<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('virtual_assistant_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('user_role', 50)->nullable()->index();
            $table->text('question');
            $table->text('normalized_question')->nullable();
            $table->string('intent', 100)->nullable()->index();
            $table->string('confidence', 30)->nullable()->index();
            $table->json('detected_params')->nullable();
            $table->string('internal_service', 120)->nullable()->index();
            $table->unsignedInteger('result_count')->default(0);
            $table->unsignedInteger('result_total')->nullable();
            $table->text('answer')->nullable();
            $table->string('result_status', 40)->default('answered')->index();
            $table->string('failure_category', 80)->nullable()->index();
            $table->text('failure_reason')->nullable();
            $table->string('ai_provider', 60)->nullable();
            $table->string('ai_model', 120)->nullable();
            $table->boolean('ai_called')->default(false)->index();
            $table->string('ai_skipped_reason', 160)->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->string('feedback', 30)->nullable()->index();
            $table->text('feedback_reason')->nullable();
            $table->timestamp('feedback_at')->nullable();
            $table->timestamp('fixed_at')->nullable();
            $table->foreignId('fixed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('fix_note')->nullable();
            $table->timestamps();

            $table->index(['result_status', 'created_at'], 'vai_status_created_idx');
            $table->index(['intent', 'result_status'], 'vai_intent_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_assistant_interactions');
    }
};
