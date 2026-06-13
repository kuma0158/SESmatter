<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('case_name');
            $table->text('work_content')->nullable();
            $table->string('location')->nullable();
            $table->string('period')->nullable();
            $table->string('unit_price', 100)->nullable();
            $table->string('settlement', 100)->nullable();
            $table->unsignedTinyInteger('interview_count')->nullable();
            $table->string('flow_limit', 100)->nullable();
            $table->string('contract_type', 50)->nullable();
            $table->string('age_limit', 50)->nullable();
            $table->string('foreigner_ok', 20)->nullable();
            $table->string('freelance_ok', 20)->nullable();
            $table->text('memo')->nullable();
            $table->string('status', 20)->default('未対応');
            $table->text('raw_text')->nullable();
            $table->string('raw_text_hash', 64)->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status']);
            $table->index(['case_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
