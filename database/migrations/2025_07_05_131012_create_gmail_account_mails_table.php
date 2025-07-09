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
        Schema::create('gmail_account_mails', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('gmail_account_id');
            $table->foreign('gmail_account_id')->references('uuid')->on('gmail_accounts')->onDelete('cascade');
            $table->string('mail_id')->unique();
            $table->string('sender');
            $table->longText('subject')->nullable();
            $table->longText('description')->nullable();
            $table->dateTime('received_at');
            $table->bigInteger('sizeEstimate');
            $table->json('label_ids');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gmail_account_mails');
    }
};
