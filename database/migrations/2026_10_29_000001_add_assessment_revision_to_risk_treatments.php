<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('risk_treatments', function (Blueprint $table) {
            $table->unsignedInteger('assessment_revision')->nullable();
        });
        DB::table('risk_treatments')->orderBy('id')->chunkById(200, function ($plans) {
            foreach ($plans as $plan) {
                // Creation events are authoritative for existing treatment revisions.
                $revision = null;
                $events = DB::table('risk_audit_events')
                    ->where('risk_assessment_id', $plan->risk_assessment_id)
                    ->where('action', 'treatment.created')->orderBy('id')->get();
                foreach ($events as $event) {
                    $metadata = json_decode($event->metadata, true, 512, JSON_THROW_ON_ERROR);
                    if ((int) ($metadata['treatment_id'] ?? 0) === (int) $plan->id) {
                        $candidate = $metadata['assessment_revision'] ?? null;
                        if (filter_var($candidate, FILTER_VALIDATE_INT) !== false && (int) $candidate > 0) {
                            $revision = (int) $candidate;
                        }
                        break;
                    }
                }
                // Unknown provenance stays null and is read-only, not silently promoted.
                DB::table('risk_treatments')->where('id', $plan->id)
                    ->update(['assessment_revision' => $revision]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('risk_treatments', function (Blueprint $table) {
            $table->dropColumn('assessment_revision');
        });
    }
};
