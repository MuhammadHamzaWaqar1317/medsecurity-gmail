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
        Schema::create('gmail_accounts', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('gmail_account')->unique();
            $table->longText('access_token');
            $table->string('refresh_token');
            $table->integer('expires_in');
            $table->longText('scope');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gmail_accounts');
    }
};
