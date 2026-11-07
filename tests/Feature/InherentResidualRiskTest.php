<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Risk\RiskCalculator;
use App\Http\Middleware\EnsureMfaIsEnrolled;
use App\Models\User;
use App\Services\TreatmentRevision;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class InherentResidualRiskTest extends TestCase
{
    private User $admin;
    private User $assessor;
    private User $reviewer;
    private User $other;

    protected function setUp(): void
    {
        parent::setUp();
        // Isolated connection: no RefreshDatabase, no project database migration/cleanup.
        config([
            'database.default' => 'inherent_residual_test',
            'database.connections.inherent_residual_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
            ],
            'session.driver' => 'array', 'cache.default' => 'array',
        ]);
        DB::purge('inherent_residual_test');
        $this->withoutMiddleware(EnsureMfaIsEnrolled::class);
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('email')->unique(); $t->string('password');
            $t->rememberToken(); $t->boolean('is_system_admin')->default(false);
            $t->text('two_factor_secret')->nullable(); $t->text('two_factor_recovery_codes')->nullable();
            $t->timestamp('two_factor_confirmed_at')->nullable(); $t->timestamp('email_verified_at')->nullable(); $t->timestamps();
        });
        (require database_path('migrations/2026_10_10_000001_create_risk_tables.php'))->up();
        (require database_path('migrations/2026_10_19_093639_add_risk_treatment_id_to_risk_reviews_table.php'))->up();
        (require database_path('migrations/2026_10_29_000001_add_assessment_revision_to_risk_treatments.php'))->up();
        (require database_path('migrations/2026_10_31_000001_add_risk_basis_to_assessments.php'))->up();
        (require database_path('migrations/2026_11_07_000001_add_expected_risk_to_assessments.php'))->up();
        DB::table('departments')->insert([
            ['id' => 1, 'code' => 'DEV', 'name' => 'Development'],
            ['id' => 2, 'code' => 'OTHER', 'name' => 'Other'],
        ]);
        foreach (['admin', 'assessor', 'reviewer', 'other'] as $role) {
            $this->{$role} = User::forceCreate([
                'name' => $role, 'email' => $role.'-lifecycle@example.test',
                'password' => 'Unused-Local-Test-Password1!',
            ]);
            DB::table('department_memberships')->insert([
                'user_id' => $this->{$role}->id, 'department_id' => $role === 'other' ? 2 : 1,
                'role' => $role === 'other' ? 'admin' : $role,
            ]);
        }
        foreach ([1, 2] as $id) {
            DB::table('risk_assets')->insert([
                'id' => $id, 'department_id' => $id, 'code' => 'TEST-'.$id, 'name' => 'Test server',
                'quantity' => 1, 'category' => 'Perkakasan', 'owner' => 'Owner', 'location' => 'Lab',
            ]);
        }
        $this->actingAs($this->admin);
    }

    private function inputs(): array
    {
        return ['profile' => 'usim-2026', 'c' => 3, 'i' => 3, 'a' => 3,
            'likelihood' => 2, 'operations' => 3, 'financial' => 2, 'reputation' => 3];
    }

    private function assessment(string $status = 'verified', int $asset = 1): int
    {
        $id = DB::table('risk_assessments')->insertGetId([
            'risk_asset_id' => $asset, 'assessor_id' => $this->assessor->id,
            'assessed_on' => '2026-10-29', 'profile' => 'usim-2026', 'engine_version' => '0.1',
            'threat' => 'Threat', 'vulnerability' => 'Vulnerability', 'risk_owner' => 'Owner',
            'inputs' => json_encode($this->inputs()),
            'result' => json_encode(app(RiskCalculator::class)->calculate($this->inputs())),
            'status' => $status, 'revision' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->event($id, 'assessment.created', []);
        return $id;
    }

    private function event(int $id, string $action, array $metadata): void
    {
        DB::table('risk_audit_events')->insert([
            'actor_id' => $this->admin->id, 'risk_assessment_id' => $id,
            'action' => $action, 'metadata' => json_encode($metadata), 'created_at' => now(),
        ]);
    }

    private function plan(int $assessment, int $revision = 1): int
    {
        $id = DB::table('risk_treatments')->insertGetId([
            'risk_assessment_id' => $assessment, 'assessment_revision' => $revision,
            'option' => 'reduce', 'rationale' => 'Reduce risk', 'responsible_person' => 'Owner',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->event($assessment, 'treatment.created', ['treatment_id' => $id, 'assessment_revision' => $revision]);
        return $id;
    }

    private function payload(string $status, int $revision = 1): array
    {
        $scores = $this->inputs(); unset($scores['profile']);
        return array_replace($scores, [
            'status' => $status, 'revision' => $revision, 'assessed_on' => '2026-10-29',
            'threat' => 'Edited threat', 'vulnerability' => 'Edited vulnerability',
            'risk_owner' => 'Edited owner', 'existing_controls' => 'Controls', 'likelihood' => 1,
        ]);
    }

    private function deletePayload(string $status, int $revision = 1): array
    {
        return ['status' => $status, 'revision' => $revision, 'confirm_delete' => '1'];
    }

    public function test_new_assessments_save_without_confirmation_and_calculate_inherent_risk_for_every_profile(): void
    {
        foreach (['nacsa-3-2024', 'nacsa-5-2024', 'usim-2026'] as $profile) {
            $max = $profile === 'nacsa-5-2024' ? 5 : 3;
            $inputs = ['profile' => $profile, 'c' => $max, 'i' => $max, 'a' => $max, 'likelihood' => 2];
            $inputs += $profile === 'usim-2026'
                ? ['operations' => 3, 'financial' => 2, 'reputation' => 3] : ['loss' => $max];
            $payload = $inputs + ['risk_asset_id' => 1, 'assessed_on' => '2026-10-31',
                'threat' => 'Unauthorised access', 'vulnerability' => 'Weak access restrictions',
                'existing_controls' => 'MFA is already in place', 'risk_owner' => 'Owner'];
            $this->post(route('assessments.store'), $payload + ['result' => ['score' => 0]])
                ->assertSessionHasNoErrors()->assertRedirect();
            $row = DB::table('risk_assessments')->orderByDesc('id')->first();
            $this->assertSame('inherent', $row->risk_basis);
            $this->assertSame(app(RiskCalculator::class)->calculate($inputs), json_decode($row->result, true));
            $this->get(route('assessments.show', $row->id))->assertOk()->assertSee('Not assessed');
        }
        $this->assertDatabaseCount('risk_assessments', 3);
    }

    public function test_migration_preserves_legacy_scores_and_edit_retains_the_old_basis_in_history(): void
    {
        $migration = require database_path('migrations/2026_10_31_000001_add_risk_basis_to_assessments.php');
        $migration->down();
        $id = $this->assessment();
        $before = DB::table('risk_assessments')->find($id)->result;
        $migration->up();
        $oldPlan = $this->plan($id);
        $row = DB::table('risk_assessments')->find($id);
        $this->assertNull($row->risk_basis);
        $this->assertSame($before, $row->result);
        $this->get(route('assessments.show', $id))->assertOk()->assertSee('Inherent Risk')->assertDontSee('This assessment predates');
        $payload = $this->payload('verified');
        $this->put(route('assessments.update', $id), $payload)
            ->assertSessionHasNoErrors()->assertRedirect();
        $current = DB::table('risk_assessments')->find($id);
        $this->assertSame('inherent', $current->risk_basis);
        $this->assertSame(2, (int) $current->revision);
        $baseline = TreatmentRevision::baseline($current, DB::table('risk_treatments')->find($oldPlan));
        $this->assertNull($baseline['risk_basis']);
        $this->assertSame(json_decode($before, true), $baseline['result']);
        $this->assertSame([], \App\Services\RiskComparison::forAssessments(collect([$current]))->get($id));
    }

    public function test_residual_risk_is_separate_server_calculated_and_independently_verified(): void
    {
        $id = $this->assessment(); $plan = $this->plan($id);
        DB::table('risk_assessments')->where('id', $id)->update(['risk_basis' => 'inherent']);
        $original = DB::table('risk_assessments')->find($id)->result;
        $payload = $this->inputs(); unset($payload['profile']);
        $payload += ['revision' => 1, 'implementation_evidence' => 'MFA enforced; access checks passed'];
        $payload['likelihood'] = 1;
        $route = route('assessments.implementation.store', ['assessment' => $id, 'treatment' => $plan]);
        $this->actingAs($this->other);
        $this->postJson($route, $payload)->assertNotFound();
        $this->actingAs($this->reviewer);
        $this->postJson($route, $payload)->assertForbidden();
        $this->actingAs($this->admin);
        $this->post($route, $payload + ['residual_result' => ['score' => 0]])
            ->assertSessionHasNoErrors()->assertRedirect();
        $record = DB::table('risk_assessments')->find($id);
        $this->assertSame($original, $record->result);
        $this->postJson($route, $payload)->assertConflict();
        $comparison = \App\Services\RiskComparison::forAssessments(collect([$record]))->get($id)[0];
        $this->assertSame(24, $comparison['result']['score']);
        $this->assertSame(24, $comparison['reduction']);
        $this->assertSame('Awaiting verification', $comparison['status']);
        $panels = \App\Services\ResidualReviewPresentation::currentPlans($record, (int) $this->admin->id);
        $this->assertFalse($panels[0]['canReview']);
        $panels = \App\Services\ResidualReviewPresentation::currentPlans($record, (int) $this->reviewer->id);
        $this->assertTrue($panels[0]['canReview']);
        $this->actingAs($this->reviewer);
        $this->get(route('assessments.review', $id))->assertOk()->assertSee('Residual Risk Review')->assertSee('MFA enforced; access checks passed');
        $this->get(route('assessments.treatments', $id))->assertOk()->assertSee('Awaiting Verification');
        $this->post(route('assessments.treatment-review.store', ['assessment' => $id, 'treatment' => $plan]),
            ['revision' => 1, 'comment' => 'Evidence checked'])->assertSessionHasNoErrors()->assertRedirect();
        $comparison = \App\Services\RiskComparison::forAssessments(collect([$record]))->get($id)[0];
        $this->assertSame('Verified', $comparison['status']);
        $this->get(route('assessments.review', $id))->assertOk()->assertSee('Evidence checked');
        $this->get(route('assessments.treatments', $id))->assertOk()->assertSee('View Review Decision');
        $this->get(route('assessments.report', $id))->assertOk()
            ->assertSee('Inherent, Expected and Residual Risk')->assertSee('Treatment Plan '.$plan);
    }

    public function test_comparison_keeps_each_current_plan_and_never_uses_historical_or_unknown_revisions(): void
    {
        $id = $this->assessment();
        DB::table('risk_assessments')->where('id', $id)->update(['risk_basis' => 'inherent', 'revision' => 2]);
        $historical = $this->plan($id, 1);
        $current = $this->plan($id, 2);
        $pending = $this->plan($id, 2);
        $unknown = $this->plan($id, 2);
        DB::table('risk_treatments')->where('id', $unknown)->update(['assessment_revision' => null]);
        $inputs = $this->inputs(); $inputs['likelihood'] = 3;
        $result = app(RiskCalculator::class)->calculate($inputs);
        DB::table('risk_treatments')->whereIn('id', [$historical, $current, $unknown])->update([
            'implemented_at' => now(), 'residual_inputs' => json_encode($inputs), 'residual_result' => json_encode($result),
        ]);
        $record = DB::table('risk_assessments')->find($id);
        $rows = \App\Services\RiskComparison::forAssessments(collect([$record]))->get($id);
        $this->assertSame([$current, $pending], array_column($rows, 'plan_id'));
        $this->assertSame(-24, $rows[0]['reduction']);
        $this->assertSame('Not assessed', $rows[1]['status']);
        $this->assertNull($rows[1]['result']);
        // An unknown basis must not be presented as a measured reduction.
        $record->risk_basis = null;
        $this->assertNull(\App\Services\RiskComparison::forAssessments(collect([$record]))->get($id)[0]['reduction']);
        // A mismatched engine cannot be compared numerically either.
        $record->risk_basis = 'inherent'; $record->engine_version = 'different';
        $this->assertNull(\App\Services\RiskComparison::forAssessments(collect([$record]))->get($id)[0]['reduction']);
    }

    public function test_comparison_and_exports_remain_department_scoped(): void
    {
        $mine = $this->assessment('draft', 1); $other = $this->assessment('draft', 2);
        DB::table('risk_assessments')->where('id', $mine)->update(['risk_basis' => 'inherent']);
        $this->get(route('assessments.show', $other))->assertNotFound();
        $this->get(route('assessments.report', $other))->assertNotFound();
        $this->get(route('assessments.index'))->assertOk()
            ->assertViewHas('riskComparisons', fn ($rows) => $rows->has($mine) && !$rows->has($other));
        $response = $this->get(route('assessments.export'))->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString('Inherent score', $csv);
        $this->assertStringContainsString('Not assessed', $csv);
        $this->assertStringContainsString('TEST-1', $csv);
        $this->assertStringNotContainsString('TEST-2', $csv);
    }

    public function test_expected_scores_are_calculated_saved_and_retained_in_revision_history(): void
    {
        $scores = ['c'=>2, 'i'=>2, 'a'=>2, 'likelihood'=>1, 'operations'=>2, 'financial'=>2, 'reputation'=>2];
        $payload = $this->payload('draft') + ['risk_asset_id'=>1, 'profile'=>'usim-2026', 'expected'=>$scores];
        $this->post(route('assessments.store'), $payload + ['expected_result'=>['score'=>999]])
            ->assertSessionHasNoErrors()->assertRedirect();
        $row = DB::table('risk_assessments')->orderByDesc('id')->first();
        $expected = app(RiskCalculator::class)->calculate(['profile'=>'usim-2026'] + $scores);
        $this->assertSame($expected, json_decode($row->expected_result, true));
        $this->get(route('assessments.report', $row->id))->assertOk()->assertSee('Expected Risk');
        $this->put(route('assessments.update', $row->id), $this->payload('draft') + ['expected'=>array_replace($scores, ['c'=>1])])
            ->assertSessionHasNoErrors()->assertRedirect();
        $event = DB::table('risk_audit_events')->where('risk_assessment_id',$row->id)->where('action','assessment.edited')->first();
        $snapshot = json_decode($event->metadata,true)['previous_snapshot'];
        $this->assertSame($expected, $snapshot['expected_result']);
        $current = DB::table('risk_assessments')->find($row->id);
        $this->assertSame(2, (int) $current->revision);
        $baseline = TreatmentRevision::baseline($current, (object)['assessment_revision'=>1]);
        $this->assertSame($expected, $baseline['expected_result']);
    }

    public function test_invalid_expected_scores_and_unauthorized_writes_are_rejected(): void
    {
        $payload = $this->payload('draft') + ['risk_asset_id'=>1, 'profile'=>'usim-2026'];
        $this->post(route('assessments.store'), $payload + ['expected'=>['c'=>4]])
            ->assertSessionHasErrors(['expected.c','expected.i']);
        $this->assertDatabaseCount('risk_assessments',0);
        $this->actingAs($this->reviewer)->post(route('assessments.store'), $payload)->assertForbidden();
        $this->actingAs($this->other)->post(route('assessments.store'), $payload)->assertForbidden();
        $this->actingAs($this->assessor)->post(route('assessments.store'), $payload)
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNull(DB::table('risk_assessments')->first()->expected_result);
    }
}
