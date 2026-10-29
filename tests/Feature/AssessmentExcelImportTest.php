<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\EnsureMfaIsEnrolled;
use App\Models\User;
use App\Services\AssessmentWorkbookReader;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

final class AssessmentExcelImportTest extends TestCase
{
    private User $assessor;

    protected function setUp(): void
    {
        parent::setUp();
        // Deliberately use a NEW, named in-memory connection. Never migrate or erase the project database.
        config([
            'database.default' => 'assessment_import_test',
            'database.connections.assessment_import_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
            ],
            'session.driver' => 'array', 'cache.default' => 'array',
        ]);
        DB::purge('assessment_import_test');
        $this->withoutMiddleware(EnsureMfaIsEnrolled::class);
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('email')->unique(); $t->string('password');
            $t->rememberToken(); $t->boolean('is_system_admin')->default(false);
            $t->text('two_factor_secret')->nullable(); $t->text('two_factor_recovery_codes')->nullable();
            $t->timestamp('two_factor_confirmed_at')->nullable(); $t->timestamp('email_verified_at')->nullable(); $t->timestamps();
        });
        Schema::create('departments', function (Blueprint $t) { $t->id(); $t->string('name'); });
        Schema::create('department_memberships', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('department_id'); $t->string('role');
        });
        Schema::create('risk_assets', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('department_id'); $t->string('code'); $t->string('name');
            $t->softDeletes(); $t->timestamps(); $t->unique(['department_id', 'code']);
        });
        Schema::create('risk_assessments', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('risk_asset_id'); $t->unsignedBigInteger('assessor_id');
            $t->date('assessed_on'); $t->string('profile'); $t->string('engine_version');
            $t->text('threat'); $t->text('vulnerability'); $t->text('existing_controls')->nullable();
            $t->string('risk_owner'); $t->text('inputs'); $t->text('result'); $t->string('status');
            $t->integer('revision'); $t->timestamps();
        });
        Schema::create('risk_audit_events', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('actor_id'); $t->unsignedBigInteger('risk_assessment_id')->nullable();
            $t->string('action'); $t->text('metadata'); $t->timestamp('created_at');
        });
        DB::table('departments')->insert([['id' => 1, 'name' => 'Development'], ['id' => 2, 'name' => 'Other']]);
        $this->assessor = User::forceCreate(['name' => 'Assessor', 'email' => 'excel-assessor@example.test', 'password' => 'Unused-Local-Test-Password1!']);
        DB::table('department_memberships')->insert(['user_id' => $this->assessor->id, 'department_id' => 1, 'role' => 'assessor']);
        DB::table('risk_assets')->insert([
            ['id' => 1, 'department_id' => 1, 'code' => 'DEMO-SRV-001', 'name' => 'Server'],
            ['id' => 2, 'department_id' => 2, 'code' => 'DEMO-SRV-001', 'name' => 'Other server'],
        ]);
        $this->actingAs($this->assessor);
    }

    private function row(array $changes = []): array
    {
        return array_replace(array_combine(AssessmentWorkbookReader::HEADERS, [
            'DEMO-SRV-001', 'usim-2026', '2026-10-28', 'Threat', 'Vulnerability', 'Controls', 'Owner',
            3, 3, 3, 2, '', 3, 2, 3,
        ]), $changes);
    }

    private function upload(array $rows, bool $compact = false): UploadedFile
    {
        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet();
        $sheet->setTitle('Assessments');
        $headers = $compact ? array_values(array_diff(AssessmentWorkbookReader::HEADERS, ['asset_code', 'profile'])) : AssessmentWorkbookReader::HEADERS;
        $sheet->fromArray($headers, null, 'A1');
        foreach ($rows as $i => $row) {
            foreach ($headers as $j => $header) {
                $value = $row[$header];
                $sheet->setCellValueExplicit([$j + 1, $i + 2], (string) $value, DataType::TYPE_STRING);
            }
        }
        $path = tempnam(sys_get_temp_dir(), 'risk-import-test-');
        try {
            (new Xlsx($book))->save($path);
            return UploadedFile::fake()->createWithContent('assessments.xlsx', file_get_contents($path));
        } finally {
            unlink($path); $book->disconnectWorksheets();
        }
    }

    private function fill(array $rows, string $profile = 'usim-2026', int $assetId = 1, bool $compact = false)
    {
        return $this->postJson(route('assessments.import.fill'), [
            'risk_asset_id' => $assetId,
            'profile' => $profile,
            'workbook' => $this->upload($rows, $compact),
        ]);
    }

    public function test_all_profiles_fill_fields_without_saving_assessments(): void
    {
        $rows = [
            $this->row(['profile' => 'nacsa-3-2024', 'loss' => 3, 'operations' => '', 'financial' => '', 'reputation' => '']),
            $this->row(['profile' => 'nacsa-5-2024', 'c' => 5, 'i' => 4, 'a' => 4, 'likelihood' => 4, 'loss' => 4, 'operations' => '', 'financial' => '', 'reputation' => '']),
            $this->row(),
        ];
        foreach ($rows as $row) {
            $this->fill($rows, $row['profile'])->assertOk()
                ->assertJsonPath('profile', $row['profile'])
                ->assertJsonPath('fields.c', $row['c'])
                ->assertJsonPath('fields.risk_owner', 'Owner');
        }
        $this->assertDatabaseCount('risk_assessments', 0);
        $this->assertDatabaseCount('risk_audit_events', 0);
    }

    public function test_compact_template_uses_the_selected_profile_and_asset(): void
    {
        $this->fill([$this->row()], compact: true)->assertOk()
            ->assertJsonPath('risk_asset_id', 1)->assertJsonPath('fields.operations', 3);
        $this->assertDatabaseCount('risk_assessments', 0);
    }

    public function test_user_can_edit_filled_values_then_save_through_the_normal_form(): void
    {
        $fields = $this->fill([$this->row()], compact: true)->assertOk()->json('fields');
        $this->assertDatabaseCount('risk_assessments', 0);
        $fields['likelihood'] = 1;
        $fields['risk_owner'] = 'Edited owner';
        $this->post(route('assessments.store'), $fields + ['risk_asset_id' => 1, 'profile' => 'usim-2026'])
            ->assertSessionHasNoErrors()->assertRedirect(route('assessments.index'));
        $this->assertDatabaseCount('risk_assessments', 1);
        $saved = DB::table('risk_assessments')->first();
        $this->assertSame('Edited owner', $saved->risk_owner);
        $this->assertSame('draft', $saved->status);
        $this->assertSame(24, json_decode($saved->result, true)['score']);
    }

    public function test_invalid_scores_and_irrelevant_impacts_are_rejected(): void
    {
        $this->fill([$this->row(['c' => 4])])->assertUnprocessable()->assertJsonValidationErrors('c');
        $this->fill([$this->row(['loss' => 2])])->assertUnprocessable()->assertJsonValidationErrors('loss');
        $this->assertDatabaseCount('risk_assessments', 0);
    }

    public function test_blank_descriptions_can_be_completed_after_upload(): void
    {
        $this->fill([$this->row(['threat' => '', 'assessed_on' => '', 'risk_owner' => ''])])
            ->assertOk()->assertJsonPath('fields.threat', '')->assertJsonPath('fields.assessed_on', '');
    }

    public function test_ambiguous_or_unmatched_legacy_rows_are_rejected(): void
    {
        $this->fill([$this->row(), $this->row()])->assertUnprocessable()->assertJsonValidationErrors('workbook');
        $this->fill([$this->row(['asset_code' => 'OTHER-ASSET'])])->assertUnprocessable()->assertJsonValidationErrors('workbook');
        $this->fill([$this->row()], 'nacsa-3-2024')->assertUnprocessable()->assertJsonValidationErrors('workbook');
    }

    public function test_other_departments_and_reviewers_cannot_fill(): void
    {
        $this->fill([$this->row()], assetId: 2)->assertForbidden();
        DB::table('department_memberships')->update(['role' => 'reviewer']);
        $this->fill([$this->row()])->assertForbidden();
        $this->get(route('assessments.import.template', ['profile' => 'usim-2026']))->assertForbidden();
    }

    public function test_archived_assets_cannot_receive_filled_values(): void
    {
        DB::table('risk_assets')->where('id', 1)->update(['deleted_at' => now()]);
        $this->fill([$this->row()])->assertNotFound();
    }

    public function test_formula_cells_are_rejected_without_evaluation(): void
    {
        $file = $this->upload([$this->row()]);
        $book = (new \PhpOffice\PhpSpreadsheet\Reader\Xlsx())->load($file->getRealPath());
        try {
            $book->getActiveSheet()->setCellValue('H2', '=1+2');
            $writer = new Xlsx($book);
            $writer->setPreCalculateFormulas(false);
            $writer->save($file->getRealPath());
            $this->postJson(route('assessments.import.fill'), [
                'risk_asset_id' => 1, 'profile' => 'usim-2026', 'workbook' => $file,
            ])->assertUnprocessable()->assertJsonValidationErrors('workbook');
        } finally {
            $book->disconnectWorksheets();
        }
    }

    public function test_downloaded_templates_match_each_selected_profile(): void
    {
        foreach (['nacsa-3-2024' => 3, 'nacsa-5-2024' => 5, 'usim-2026' => 3] as $profile => $cia) {
            $response = $this->get(route('assessments.import.template', ['profile' => $profile]))->assertOk();
            $file = UploadedFile::fake()->createWithContent('example.xlsx', $response->streamedContent());
            $this->postJson(route('assessments.import.fill'), [
                'risk_asset_id' => 1, 'profile' => $profile, 'workbook' => $file,
            ])->assertOk()->assertJsonPath('fields.c', $cia);
        }
    }

    public function test_legacy_bulk_save_routes_are_no_longer_available(): void
    {
        $this->get(route('assessments.import'))->assertRedirect(route('assessments.index').'#create-assessment');
        $this->post('/assessments/import/preview')->assertNotFound();
        $this->post('/assessments/import/12345678-1234-4123-8123-123456789abc')->assertNotFound();
        $this->assertDatabaseCount('risk_assessments', 0);
    }
}

