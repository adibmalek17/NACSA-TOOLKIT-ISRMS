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

final class AssessmentLifecycleTest extends TestCase
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
            'database.default' => 'assessment_lifecycle_test',
            'database.connections.assessment_lifecycle_test' => [
                'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
            ],
            'session.driver' => 'array', 'cache.default' => 'array',
        ]);
        DB::purge('assessment_lifecycle_test');
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

    public function test_all_statuses_can_be_edited_to_a_recalculated_draft(): void
    {
        foreach (['draft', 'submitted', 'returned', 'verified', 'archived'] as $status) {
            $id = $this->assessment($status);
            $this->get(route('assessments.edit', $id))->assertOk()->assertSee('Edit assessment');
            $this->put(route('assessments.update', $id), $this->payload($status))
                ->assertSessionHasNoErrors()->assertRedirect();
            $row = DB::table('risk_assessments')->find($id);
            $this->assertSame('draft', $row->status);
            $this->assertSame(2, (int) $row->revision);
            $this->assertSame((int) $this->admin->id, (int) $row->assessor_id);
            $this->assertSame(24, json_decode($row->result, true)['score']);
            $event = DB::table('risk_audit_events')->where('risk_assessment_id', $id)->where('action', 'assessment.edited')->first();
            $snapshot = json_decode($event->metadata, true)['previous_snapshot'];
            $this->assertSame($status, $snapshot['status']);
            $this->assertSame(48, $snapshot['result']['score']);
        }
    }

    public function test_all_statuses_can_be_deleted_without_deleting_the_asset(): void
    {
        foreach (['draft', 'submitted', 'returned', 'verified', 'archived'] as $status) {
            $id = $this->assessment($status);
            $this->delete(route('assessments.destroy', $id), $this->deletePayload($status))->assertRedirect();
            $this->assertDatabaseMissing('risk_assessments', ['id' => $id]);
        }
        $this->assertDatabaseCount('risk_assets', 2);
        $this->assertSame(5, DB::table('risk_audit_events')->where('action', 'assessment.deleted')->count());
        $this->assertSame(0, DB::table('risk_audit_events')->whereNotNull('risk_assessment_id')->count());
    }

    public function test_delete_removes_linked_reviews_before_treatments_and_preserves_audit(): void
    {
        $id = $this->assessment(); $plan = $this->plan($id);
        foreach ([null, $plan] as $treatment) {
            DB::table('risk_reviews')->insert([
                'risk_assessment_id' => $id, 'risk_treatment_id' => $treatment,
                'reviewer_id' => $this->reviewer->id, 'revision' => 1,
                'stage' => $treatment ? 'residual' : 'assessment', 'decision' => 'verified',
                'comment' => 'Verified', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $this->delete(route('assessments.destroy', $id), $this->deletePayload('verified'))->assertRedirect();
        $this->assertDatabaseCount('risk_reviews', 0);
        $this->assertDatabaseCount('risk_treatments', 0);
        $this->assertDatabaseCount('risk_audit_events', 3);
        $event = DB::table('risk_audit_events')->where('action', 'assessment.created')->first();
        $this->assertNull($event->risk_assessment_id);
        $this->assertSame($id, json_decode($event->metadata, true)['deleted_assessment_id']);
    }

    public function test_permissions_apply_to_get_edit_update_and_delete_and_old_correction_route(): void
    {
        $id = $this->assessment('returned');
        $this->actingAs($this->reviewer);
        $this->get(route('assessments.edit', $id))->assertForbidden();
        $this->putJson(route('assessments.update', $id), $this->payload('returned'))->assertForbidden();
        $this->deleteJson(route('assessments.destroy', $id), $this->deletePayload('returned'))->assertForbidden();
        $this->putJson(route('assessments.correct.update', $id), $this->payload('returned'))->assertForbidden();
        $this->actingAs($this->other);
        $this->get(route('assessments.edit', $id))->assertNotFound();
        $this->deleteJson(route('assessments.destroy', $id), $this->deletePayload('returned'))->assertNotFound();
        $this->actingAs($this->assessor);
        $this->get(route('assessments.edit', $id))->assertOk();
        $this->put(route('assessments.update', $id), $this->payload('returned'))->assertRedirect();
        DB::table('risk_assessments')->where('id', $id)->update(['assessor_id' => $this->admin->id]);
        $this->get(route('assessments.edit', $id))->assertForbidden();
        $this->deleteJson(route('assessments.destroy', $id), $this->deletePayload('draft', 2))->assertForbidden();
    }

    public function test_stale_forms_and_missing_confirmation_do_not_mutate(): void
    {
        $id = $this->assessment('archived');
        $this->deleteJson(route('assessments.destroy', $id), ['revision' => 1, 'status' => 'archived'])
            ->assertUnprocessable()->assertJsonValidationErrors('confirm_delete');
        $this->putJson(route('assessments.update', $id), $this->payload('verified'))->assertConflict();
        $this->put(route('assessments.update', $id), $this->payload('archived'))->assertRedirect();
        $this->putJson(route('assessments.update', $id), $this->payload('archived'))->assertConflict();
        $this->deleteJson(route('assessments.destroy', $id), $this->deletePayload('archived'))->assertConflict();
        $this->assertDatabaseHas('risk_assessments', ['id' => $id, 'revision' => 2, 'status' => 'draft']);
    }

    public function test_invalid_scores_and_asset_or_profile_changes_are_rejected(): void
    {
        $id = $this->assessment('draft');
        foreach ([['c' => 4], ['risk_asset_id' => 2], ['profile' => 'nacsa-5-2024']] as $change) {
            $this->putJson(route('assessments.update', $id), array_replace($this->payload('draft'), $change))->assertUnprocessable();
        }
        $this->assertDatabaseHas('risk_assessments', ['id' => $id, 'revision' => 1]);
    }

    public function test_old_treatments_remain_historical_after_edit_and_reverification(): void
    {
        $id = $this->assessment(); $oldPlan = $this->plan($id);
        DB::table('risk_reviews')->insert([
            'risk_assessment_id' => $id, 'risk_treatment_id' => null,
            'reviewer_id' => $this->reviewer->id, 'revision' => 1, 'stage' => 'assessment',
            'decision' => 'verified', 'comment' => 'Earlier approval', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->actingAs($this->assessor);
        $this->put(route('assessments.update', $id), $this->payload('verified'))->assertRedirect();
        $this->post(route('assessments.submit', $id), ['revision' => 2])->assertRedirect();
        $this->actingAs($this->reviewer);
        $this->post(route('assessments.review.store', $id), ['revision' => 2, 'decision' => 'verified', 'comment' => 'Fresh approval'])->assertRedirect();
        $this->assertDatabaseCount('risk_reviews', 2);
        $this->postJson(route('assessments.treatment-review.store', ['assessment' => $id, 'treatment' => $oldPlan]), ['revision' => 2, 'comment' => 'Attempt'])->assertConflict();
        $this->actingAs($this->assessor);
        $this->postJson(route('assessments.implementation.store', ['assessment' => $id, 'treatment' => $oldPlan]), $this->payload('verified', 2) + ['implementation_evidence' => 'Attempt'])->assertConflict();
        $this->post(route('assessments.treatments.store', $id), [
            'revision' => 2, 'option' => 'reduce', 'rationale' => 'New plan', 'responsible_person' => 'Owner',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('risk_treatments', ['risk_assessment_id' => $id, 'assessment_revision' => 2]);
        $record = DB::table('risk_assessments')->find($id);
        $plan = DB::table('risk_treatments')->find($oldPlan);
        $this->assertFalse(TreatmentRevision::isCurrent($record, $plan));
        $this->assertSame(48, TreatmentRevision::baseline($record, $plan)['result']['score']);
        $this->get(route('assessments.implementation', ['assessment' => $id, 'treatment' => $oldPlan]))->assertOk()->assertSee('historical and view only');
        $this->get(route('assessments.report', $id))->assertOk()->assertViewHas('treatments', fn ($plans) => $plans->count() === 1 && (int) $plans->first()->assessment_revision === 2);
        $this->get(route('dashboard'))->assertOk()->assertViewHas('stats', fn ($stats) => $stats['awaiting_implementation'] === 1);
    }

    public function test_migration_backfills_from_audit_and_leaves_unknown_plans_read_only(): void
    {
        $migration = require database_path('migrations/2026_10_29_000001_add_assessment_revision_to_risk_treatments.php');
        $id = $this->assessment(); $known = $this->plan($id); $unknown = $this->plan($id);
        DB::table('risk_audit_events')->where('action', 'treatment.created')->delete();
        $this->event($id, 'treatment.created', ['treatment_id' => $known, 'assessment_revision' => 1]);
        DB::table('risk_assessments')->where('id', $id)->update(['revision' => 3]);
        $migration->down(); $migration->up();
        $this->assertDatabaseHas('risk_treatments', ['id' => $known, 'assessment_revision' => 1]);
        $this->assertNull(DB::table('risk_treatments')->find($unknown)->assessment_revision);
        $this->assertFalse(TreatmentRevision::isCurrent(DB::table('risk_assessments')->find($id), DB::table('risk_treatments')->find($unknown)));
    }

    public function test_list_controls_and_archived_asset_restriction(): void
    {
        $id = $this->assessment('archived');
        $this->get(route('assessments.index'))->assertOk()->assertSee('Edit')->assertSee('confirm_delete', false)->assertDontSee('>Archive</button>', false);
        $this->postJson('/assessments/'.$id.'/archive', ['revision' => 1])->assertNotFound();
        $this->actingAs($this->reviewer);
        $this->get(route('assessments.index'))->assertOk()->assertDontSee('confirm_delete', false);
        $this->actingAs($this->admin);
        DB::table('risk_assets')->where('id', 1)->update(['deleted_at' => now()]);
        $this->get(route('assessments.edit', $id))->assertConflict();
        $this->delete(route('assessments.destroy', $id), $this->deletePayload('archived'))->assertRedirect();
    }
}
