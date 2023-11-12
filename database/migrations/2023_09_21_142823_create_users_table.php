<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('firstname')->nullable();
            $table->string('middlename')->nullable();
            $table->string('lastname')->nullable();
            $table->unsignedInteger('age')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('contactnumber')->nullable();
            $table->string('address')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->binary('image')->nullable();
            $table->string('status')->default('Pending');
            $table->string('verification_code', 8)->nullable();
            $table->string('password_reset_token')->nullable();
            $table->string('fCMToken')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_code_request')->nullable();
            $table->timestamps();
        });
        DB::statement('ALTER TABLE users MODIFY image MEDIUMBLOB');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};