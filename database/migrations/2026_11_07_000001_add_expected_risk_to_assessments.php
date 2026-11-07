<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('risk_assessments', function (Blueprint $table) {
            $table->json('expected_inputs')->nullable();
            $table->json('expected_result')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('risk_assessments', function (Blueprint $table) {
            $table->dropColumn(['expected_inputs', 'expected_result']);
        });
    }
};
