<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('risk_reviews', function (Blueprint $table) {
            $table->foreignId('risk_treatment_id')
                ->nullable()
                ->constrained('risk_treatments')
                ->restrictOnDelete();

            $table->unique(
                ['risk_treatment_id', 'revision', 'stage'],
                'risk_reviews_treatment_revision_stage_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('risk_reviews', function (Blueprint $table) {
            $table->dropUnique(
                'risk_reviews_treatment_revision_stage_unique'
            );

            $table->dropConstrainedForeignId('risk_treatment_id');
        });
    }
};