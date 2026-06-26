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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id('schedule_id');
            $table->foreignId('doctor_id')->constrained('doctors', 'doctor_id')->onDelete('cascade')->onUpdate('cascade');
            $table->date('availability_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('availability_status', ['Available', 'On Leave', 'Blocked'])->default('Available');
            $table->timestamps();

            // Your ALTER TABLE UNIQUE constraint translated to Laravel:
            $table->unique(['doctor_id', 'availability_date', 'start_time', 'end_time'], 'unique_schedule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
