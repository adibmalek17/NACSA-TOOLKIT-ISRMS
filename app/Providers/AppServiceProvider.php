<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            if ($event->guard !== 'web') {
                return;
            }

            // RequireFreshSession rejects remembered sessions. Do not record
            // their restoration as a successful interactive sign-in.
            if (Auth::guard('web')->viaRemember()) {
                return;
            }

            $this->recordAuthenticationEvent(
                'auth.login',
                $event->user->getAuthIdentifier(),
                ['guard' => $event->guard],
            );
        });

        Event::listen(Logout::class, function (Logout $event): void {
            if ($event->guard !== 'web' || $event->user === null) {
                return;
            }

            $remembered = Auth::guard('web')->viaRemember();

            $this->recordAuthenticationEvent(
                $remembered ? 'auth.remembered_session_rejected' : 'auth.logout',
                $event->user->getAuthIdentifier(),
                ['guard' => $event->guard],
            );
        });
    }

    private function recordAuthenticationEvent(
        string $action,
        int|string $actorId,
        array $metadata,
    ): void {
        // Store only explicitly selected metadata, never submitted credentials,
        // authentication codes, recovery codes, cookies, or session tokens.
        if (! $this->app->runningInConsole()) {
            $request = $this->app->make('request');
            $metadata['ip_address'] = $request->ip();
        }

        DB::table('risk_audit_events')->insert([
            'actor_id' => $actorId,
            'risk_assessment_id' => null,
            'action' => $action,
            'metadata' => json_encode($metadata, JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);
    }
}
