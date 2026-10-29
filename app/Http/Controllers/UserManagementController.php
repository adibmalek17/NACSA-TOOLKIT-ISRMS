<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    private const ROLES = ['admin', 'assessor', 'reviewer'];

    public function index(Request $request)
    {
        $users = User::query()->orderBy('name')->orderBy('id')->paginate(15);
        $memberships = DB::table('department_memberships')
            ->join('departments', 'departments.id', '=', 'department_memberships.department_id')
            ->whereIn('department_memberships.user_id', $users->pluck('id'))
            ->select('department_memberships.*', 'departments.name as department_name')
            ->orderBy('departments.name')->get()->groupBy('user_id');

        return view('admin.users.index', [
            'users' => $users,
            'memberships' => $memberships,
            'departments' => DB::table('departments')->orderBy('name')->get(),
            'roles' => self::ROLES,
        ]);
    }

    public function store(Request $request)
    {
        // Accept only explicit fields; System Admin permission is never writable here.
        if (is_string($request->input('email'))) {
            $request->merge(['email' => mb_strtolower(trim($request->input('email')))]);
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'max:72', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'role' => ['required', Rule::in(self::ROLES)],
            'is_system_admin' => ['prohibited'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);
            DB::table('department_memberships')->insert([
                'user_id' => $user->id,
                'department_id' => $data['department_id'],
                'role' => $data['role'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->audit($request, 'user.created', [
                'target_user_id' => $user->id,
                'department_id' => (int) $data['department_id'],
                'role' => $data['role'],
            ]);
        });

        return redirect()->route('admin.users.index')->with('success', 'Account created. The user must enroll in MFA when signing in.');
    }

    public function assign(Request $request, User $user)
    {
        $data = $request->validate([
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'role' => ['required', Rule::in(self::ROLES)],
            'is_system_admin' => ['prohibited'],
        ]);

        DB::transaction(function () use ($request, $user, $data) {
            // Serialize membership writes for this user.
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $query = DB::table('department_memberships')->where('user_id', $user->id)
                ->where('department_id', $data['department_id']);
            $previous = (clone $query)->first();
            if ($previous && $previous->role === $data['role']) {
                return;
            }
            if ($previous) {
                $query->update(['role' => $data['role'], 'updated_at' => now()]);
            } else {
                DB::table('department_memberships')->insert([
                    'user_id' => $user->id,
                    'department_id' => $data['department_id'],
                    'role' => $data['role'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->audit($request, 'user.membership_saved', [
                'target_user_id' => $user->id,
                'department_id' => (int) $data['department_id'],
                'previous_role' => $previous?->role,
                'role' => $data['role'],
            ]);
        });

        return redirect()->route('admin.users.index')->with('success', 'Department role saved.');
    }

    public function remove(Request $request, User $user, int $department)
    {
        DB::transaction(function () use ($request, $user, $department) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $membership = DB::table('department_memberships')->where('user_id', $user->id)
                ->where('department_id', $department)->first();
            abort_unless($membership, 404);

            DB::table('department_memberships')->where('id', $membership->id)->delete();
            $this->audit($request, 'user.membership_removed', [
                'target_user_id' => $user->id,
                'department_id' => $department,
                'previous_role' => $membership->role,
            ]);
        });

        return redirect()->route('admin.users.index')->with('success', 'Department access removed. Historical records are retained.');
    }

    private function audit(Request $request, string $action, array $metadata): void
    {
        DB::table('risk_audit_events')->insert([
            'actor_id' => $request->user()->id,
            'risk_assessment_id' => null,
            'action' => $action,
            'metadata' => json_encode($metadata, JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);
    }
}
