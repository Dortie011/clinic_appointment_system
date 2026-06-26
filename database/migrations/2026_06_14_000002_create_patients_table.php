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
        Schema::create('patients', function (Blueprint $table) {
            $table->id('patient_id'); // Custom primary key matching your script
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->date('birth_date');
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->string('phone_num', 15);
            $table->string('email', 100)->unique()->nullable();
            $table->string('address', 255)->nullable();
            $table->timestamp('registration_date')->useCurrent();
            $table->timestamps(); // Handles created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
