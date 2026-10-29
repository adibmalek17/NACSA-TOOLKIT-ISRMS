<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id(); $table->string('code')->unique(); $table->string('name'); $table->timestamps();
        });
        Schema::create('department_memberships', function (Blueprint $table) {
            $table->id(); $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->enum('role', ['admin','assessor','reviewer']);
            $table->unique(['department_id','user_id']); $table->timestamps();
        });
        Schema::create('risk_assets', function (Blueprint $table) {
            $table->id(); $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->string('code'); $table->string('name'); $table->string('serial_number')->nullable();
            $table->unsignedInteger('quantity')->default(1); $table->string('category');
            $table->text('description')->nullable(); $table->string('owner'); $table->string('location');
            $table->unique(['department_id','code']); $table->timestamps(); $table->softDeletes();
        });
        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->id(); $table->foreignId('risk_asset_id')->constrained()->restrictOnDelete();
            $table->foreignId('assessor_id')->constrained('users')->restrictOnDelete();
            $table->date('assessed_on'); $table->string('profile'); $table->string('engine_version');
            $table->text('threat'); $table->text('vulnerability'); $table->text('existing_controls')->nullable();
            $table->string('risk_owner'); $table->json('inputs'); $table->json('result');
            $table->string('status')->default('draft'); $table->unsignedInteger('revision')->default(1);
            $table->timestamps(); $table->index(['profile','assessed_on']);
        });
        Schema::create('risk_treatments', function (Blueprint $table) {
            $table->id(); $table->foreignId('risk_assessment_id')->constrained()->restrictOnDelete();
            $table->enum('option', ['accept','reduce','transfer','avoid']);
            $table->text('rationale'); $table->text('planned_controls')->nullable();
            $table->json('annex_a_references')->nullable(); $table->string('responsible_person');
            $table->date('starts_on')->nullable(); $table->date('due_on')->nullable();
            $table->text('implementation_evidence')->nullable();
            $table->json('residual_inputs')->nullable(); $table->json('residual_result')->nullable();
            $table->timestamp('implemented_at')->nullable(); $table->timestamps();
        });
        Schema::create('risk_reviews', function (Blueprint $table) {
            $table->id(); $table->foreignId('risk_assessment_id')->constrained()->restrictOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('revision'); $table->string('stage');
            $table->enum('decision', ['verified','returned']); $table->text('comment'); $table->timestamps();
        });
        Schema::create('risk_audit_events', function (Blueprint $table) {
            $table->id(); $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('risk_assessment_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('action'); $table->json('metadata'); $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void
    {
        foreach (['risk_audit_events','risk_reviews','risk_treatments','risk_assessments',
            'risk_assets','department_memberships','departments'] as $name) Schema::dropIfExists($name);
    }
};
