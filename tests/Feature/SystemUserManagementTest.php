<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SystemUserManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // A separate in-memory database: never migrate or erase the project DB.
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
            'session.driver' => 'array',
            'cache.default' => 'array',
        ]);
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('email')->unique();
            $table->string('password'); $table->rememberToken();
            $table->boolean('is_system_admin')->default(false);
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable(); $table->timestamps();
        });
        Schema::create('departments', function (Blueprint $table) {
            $table->id(); $table->string('name');
        });
        Schema::create('department_memberships', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('department_id'); $table->string('role');
            $table->unique(['department_id', 'user_id']); $table->timestamps();
        });
        Schema::create('risk_audit_events', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('actor_id');
            $table->unsignedBigInteger('risk_assessment_id')->nullable();
            $table->string('action'); $table->text('metadata'); $table->timestamp('created_at');
        });
        DB::table('departments')->insert(['id' => 1, 'name' => 'Test department']);
    }

    private function account(string $email, bool $systemAdmin = false): User
    {
        $user = new User;
        $user->forceFill([
            'name' => 'Test user', 'email' => $email,
            'password' => 'FixtureOnly!2026', 'is_system_admin' => $systemAdmin,
            'two_factor_secret' => 'fixture-enrolled-marker',
            'two_factor_confirmed_at' => now(),
        ])->save();
        return $user;
    }

    private function payload(): array
    {
        return [
            'name' => 'New assessor', 'email' => 'new@example.test',
            'password' => 'FixtureOnly!2026', 'password_confirmation' => 'FixtureOnly!2026',
            'department_id' => 1, 'role' => 'assessor',
        ];
    }

    public function test_department_admin_cannot_access_system_user_management(): void
    {
        $user = $this->account('department@example.test');
        DB::table('department_memberships')->insert([
            'user_id' => $user->id, 'department_id' => 1, 'role' => 'admin',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);
        $this->get('/admin/users')->assertForbidden();
        $this->post('/admin/users', $this->payload())->assertForbidden();
        $this->put('/admin/users/'.$user->id.'/membership', ['department_id' => 1, 'role' => 'reviewer'])->assertForbidden();
        $this->delete('/admin/users/'.$user->id.'/membership/1')->assertForbidden();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_system_admin_can_create_account_with_hashed_password_and_audit(): void
    {
        $admin = $this->account('system@example.test', true);
        $this->actingAs($admin)->withSession(['auth.password_confirmed_at' => time()]);
        $this->post('/admin/users', $this->payload())->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.users.index'));
        $user = User::where('email', 'new@example.test')->firstOrFail();
        $this->assertFalse($user->is_system_admin);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertTrue(Hash::check('FixtureOnly!2026', $user->password));
        $this->assertDatabaseHas('department_memberships', [
            'user_id' => $user->id, 'department_id' => 1, 'role' => 'assessor',
        ]);
        $this->assertDatabaseHas('risk_audit_events', ['actor_id' => $admin->id, 'action' => 'user.created']);
        $metadata = DB::table('risk_audit_events')->where('action', 'user.created')->value('metadata');
        $this->assertStringNotContainsString('FixtureOnly!', $metadata);
    }

    public function test_system_permission_cannot_be_assigned_through_account_form(): void
    {
        $this->actingAs($this->account('system@example.test', true))
            ->withSession(['auth.password_confirmed_at' => time()]);
        $this->post('/admin/users', $this->payload() + ['is_system_admin' => true])
            ->assertSessionHasErrors('is_system_admin');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_role_changes_and_removal_preserve_account_and_audit_history(): void
    {
        $admin = $this->account('system@example.test', true);
        $target = $this->account('target@example.test');
        $this->actingAs($admin)->withSession(['auth.password_confirmed_at' => time()]);
        foreach (['assessor', 'reviewer'] as $role) {
            $this->put('/admin/users/'.$target->id.'/membership', ['department_id' => 1, 'role' => $role])
                ->assertSessionHasNoErrors()->assertRedirect();
        }
        $this->assertDatabaseCount('department_memberships', 1);
        $this->assertDatabaseHas('department_memberships', ['user_id' => $target->id, 'role' => 'reviewer']);
        $this->delete('/admin/users/'.$target->id.'/membership/1')->assertRedirect();
        $this->assertDatabaseCount('department_memberships', 0);
        $this->assertDatabaseHas('users', ['id' => $target->id]);
        $this->assertDatabaseHas('risk_audit_events', ['action' => 'user.membership_removed']);
    }
}
