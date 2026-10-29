<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BootstrapSystemAdmin extends Command
{
    protected $signature = 'toolkit:bootstrap-admin {email}';
    protected $description = 'Grant the first System Admin permission to an existing MFA-enrolled account';

    public function handle(): int
    {
        $email = mb_strtolower(trim($this->argument('email')));

        DB::transaction(function () use ($email) {
            // Serialize this one-time bootstrap against other bootstrap runs.
            $users = User::query()->orderBy('id')->lockForUpdate()->get();
            $user = $users->first(fn ($user) => mb_strtolower($user->email) === $email);

            if (! $user) {
                throw ValidationException::withMessages(['email' => 'No account matches this email.']);
            }
            if ($user->is_system_admin) {
                $this->info('This account is already a System Admin.');
                return;
            }
            if ($users->contains('is_system_admin', true)) {
                throw ValidationException::withMessages(['email' => 'A System Admin already exists. This command only creates the first one.']);
            }
            if (! filled($user->two_factor_secret) || $user->two_factor_confirmed_at === null) {
                throw ValidationException::withMessages(['email' => 'Complete MFA enrollment for this account first.']);
            }

            $user->is_system_admin = true;
            $user->save();

            DB::table('risk_audit_events')->insert([
                'actor_id' => $user->id,
                'risk_assessment_id' => null,
                'action' => 'user.system_admin_bootstrapped',
                'metadata' => json_encode([
                    'target_user_id' => $user->id,
                    'source' => 'console_bootstrap',
                    'actor_attribution' => 'target_account; initiated by local console operator',
                ], JSON_THROW_ON_ERROR),
                'created_at' => now(),
            ]);
            $this->info('System Admin permission granted to '.$user->email.'.');
        });

        return self::SUCCESS;
    }
}
