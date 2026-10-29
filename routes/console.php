<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

\Illuminate\Support\Facades\Artisan::command('nacsa:create-test-user', function () {
    if (! app()->environment('local')) {
        $this->error('This command is only available locally.');
        return;
    }

    $email = strtolower(trim($this->ask('Email address')));

    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Please enter a valid email address.');
        return;
    }

    if (\App\Models\User::where('email', $email)->exists()) {
        $this->error('This email already has an account.');
        return;
    }

    $password = $this->secret('Choose a password with at least 12 characters');

    if (strlen($password ?? '') < 12) {
        $this->error('Password must contain at least 12 characters.');
        return;
    }

    if ($password !== $this->secret('Confirm password')) {
        $this->error('Passwords do not match.');
        return;
    }

    $user = new \App\Models\User();
    $user->name = 'Local Developer';
    $user->email = $email;
    $user->password = \Illuminate\Support\Facades\Hash::make($password);
    $user->save();

    $this->info('Test account created successfully.');
});

Artisan::command('nacsa:setup-demo-admin', function () {
    if (! app()->environment('local')) {
        $this->error('This command is only available locally.');
        return;
    }

    $email = strtolower(trim($this->ask('Your existing login email')));

    $user = \App\Models\User::where('email', $email)->first();

    if (! $user) {
        $this->error('Account not found. Enter the email you use to log in.');
        return;
    }

    \Illuminate\Support\Facades\DB::transaction(function () use ($user) {
        $now = now();

        \Illuminate\Support\Facades\DB::table('departments')->upsert(
            [[
                'code' => 'DEV',
                'name' => 'Development Department',
                'created_at' => $now,
                'updated_at' => $now,
            ]],
            ['code'],
            ['name', 'updated_at']
        );

        $departmentId = \Illuminate\Support\Facades\DB::table('departments')
            ->where('code', 'DEV')
            ->value('id');

        \Illuminate\Support\Facades\DB::table('department_memberships')->upsert(
            [[
                'department_id' => $departmentId,
                'user_id' => $user->id,
                'role' => 'admin',
                'created_at' => $now,
                'updated_at' => $now,
            ]],
            ['department_id', 'user_id'],
            ['role', 'updated_at']
        );
    });

    $this->info('Admin assigned to Development Department.');
});

Artisan::command('nacsa:setup-demo-reviewer', function () {
    if (! app()->environment('local')) {
        $this->error('This command is only available locally.');
        return;
    }

    $user = \App\Models\User::where(
        'email',
        'reviewer@example.test'
    )->first();

    if (! $user) {
        $this->error('Create reviewer@example.test first.');
        return;
    }

    $departmentId = \Illuminate\Support\Facades\DB::table('departments')
        ->where('code', 'DEV')
        ->value('id');

    if (! $departmentId) {
        $this->error('Development Department was not found.');
        return;
    }

    \Illuminate\Support\Facades\DB::table('department_memberships')->upsert(
        [[
            'department_id' => $departmentId,
            'user_id' => $user->id,
            'role' => 'reviewer',
            'created_at' => now(),
            'updated_at' => now(),
        ]],
        ['department_id', 'user_id'],
        ['role', 'updated_at']
    );

    $this->info('Reviewer assigned to Development Department.');
});

Artisan::command('nacsa:setup-isolation-test', function () {
    if (! app()->environment('local')) {
        $this->error('This command is only available locally.');
        return;
    }

    $departmentId = \Illuminate\Support\Facades\DB::transaction(function () {
        $now = now();

        \Illuminate\Support\Facades\DB::table('departments')->upsert(
            [[
                'code' => 'ISO-TEST',
                'name' => 'Isolation Test Department',
                'created_at' => $now,
                'updated_at' => $now,
            ]],
            ['code'],
            ['name', 'updated_at']
        );

        $departmentId = \Illuminate\Support\Facades\DB::table('departments')
            ->where('code', 'ISO-TEST')
            ->value('id');

        $exists = \Illuminate\Support\Facades\DB::table('risk_assets')
            ->where('department_id', $departmentId)
            ->where('code', 'ISO-001')
            ->exists();

        if (! $exists) {
            \App\Models\RiskAsset::create([
                'department_id' => $departmentId,
                'code' => 'ISO-001',
                'name' => 'Isolation Test Server',
                'quantity' => 1,
                'category' => 'Perkakasan',
                'owner' => 'Isolation Test Owner',
                'location' => 'Isolation Test Lab',
            ]);
        }

        return $departmentId;
    });

    $this->info('Isolation test data is ready.');
    $this->info('Department ID: '.$departmentId);
    $this->info('No department memberships were added.');
});