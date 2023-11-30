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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('barangay_id');
            $table->enum('emergency_type', ['General', 'Medical', 'Fire', 'Crime']);
            $table->enum('for_whom', ['Myself', 'Another_Person']);
            $table->mediumText('description');
            $table->boolean('casualties')->default(false);
            $table->point('location');
            $table->enum('visibility', ['Private', 'Public']);
            $table->binary('image')->nullable();
            $table->enum('status', ['Submitted', 'Processing', 'Resolved']);
            $table->timestamps();
        });

        // Alter the image column to use MEDIUMBLOB
        if (config('database.default') == 'mysql') {
            DB::statement('ALTER TABLE reports MODIFY image MEDIUMBLOB');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};