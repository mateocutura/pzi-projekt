<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('phase_id')->constrained('project_phases')->cascadeOnDelete();
            $table->enum('status', ['not_started', 'submitted', 'approved', 'rejected'])->default('not_started');
            $table->text('submission_text')->nullable();
            $table->text('mentor_feedback')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();


            $table->unique(['project_id', 'phase_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_submissions');
    }
};
