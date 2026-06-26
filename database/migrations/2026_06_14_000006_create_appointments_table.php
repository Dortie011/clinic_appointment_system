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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id('appointment_id');
            $table->foreignId('patient_id')->constrained('patients', 'patient_id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('doctor_id')->constrained('doctors', 'doctor_id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('schedule_id')->nullable()->constrained('schedules', 'schedule_id')->onDelete('set null')->onUpdate('cascade');
            $table->enum('status', ['Scheduled', 'Completed', 'Cancelled', 'No Show'])->default('Scheduled');
            $table->string('reason_for_visit', 255);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('schedule_id'); // Keeps it one appointment per schedule block
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
