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
        Schema::create('gmail_batch_fetch_progress', function (Blueprint $table) {
            $table->uuid();
            $table->string('gmail_account_id');
            $table->foreign('gmail_account_id')->references('uuid')->on('gmail_accounts')->onDelete('cascade');
            $table->string('batch_id');
            $table->foreign('batch_id')->references('id')->on('job_batches')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gmail_batch_fetch_progress');
    }
};
