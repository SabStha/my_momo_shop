<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('investor_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_id')->constrained()->onDelete('cascade');
            $table->enum('report_type', ['monthly', 'quarterly', 'annual', 'performance', 'payout'])->default('monthly');
            $table->string('title');
            $table->longText('content')->nullable();
            $table->timestamp('generated_at');
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->enum('status', ['draft', 'sent', 'viewed'])->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('file_path')->nullable();
            $table->boolean('email_sent')->default(false);
            $table->string('email_subject')->nullable();
            $table->text('email_body')->nullable();
            $table->json('metrics_data')->nullable();
            $table->json('charts_data')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investor_reports');
    }
};
