<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Specializations First
        DB::table('specialization')->insert([
            ['specialization_name' => 'General Medicine'],
            ['specialization_name' => 'Pediatrics'],
            ['specialization_name' => 'Cardiology'],
            ['specialization_name' => 'Dermatology'],
            ['specialization_name' => 'Neurology'],
        ]);

        // 2. Seed Doctors (Required before linking them to Users or Schedules)
        DB::table('doctors')->insert([
            [
                'doctor_id' => 1, 
                'first_name' => 'John', 
                'last_name' => 'Smith', 
                'phone' => '09543216789', 
                'email' => 'John@gmail.com', 
                'room_num' => 'A102'
            ],
            [
                'doctor_id' => 2, 
                'first_name' => 'Rose', 
                'last_name' => 'Sanchez', 
                'phone' => '09789123456', 
                'email' => 'Rose@gmail.com', 
                'room_num' => 'ER-1'
            ],
        ]);

        // 3. Seed Doctor Specialization Link (Pivot Table)
        DB::table('doctor_specialization')->insert([
            ['doctor_id' => 1, 'specialization_id' => 1],
            ['doctor_id' => 1, 'specialization_id' => 3],
            ['doctor_id' => 2, 'specialization_id' => 2],
        ]);

        // 4. Seed Patients
        DB::table('patients')->insert([
            [
                'patient_id' => 1, 
                'first_name' => 'Satoru', // Fixed the typo from 'Saturo'
                'last_name' => 'Gojo', 
                'birth_date' => '1997-12-25', 
                'gender' => 'Male', 
                'phone_num' => '09123456789', 
                'email' => 'Satoru@gmail.com', 
                'address' => 'San pedro'
            ],
            [
                'patient_id' => 2, 
                'first_name' => 'Suguru', 
                'last_name' => 'Geto', 
                'birth_date' => '1997-05-24', 
                'gender' => 'Male', 
                'phone_num' => '09987654321', 
                'email' => 'Suguru@gmail.com', 
                'address' => 'Sicsican'
            ],
        ]);

        // 5. Seed Doctor Schedules
        DB::table('schedules')->insert([
            [
                'schedule_id' => 1, 
                'doctor_id' => 1, 
                'availability_date' => '2026-02-25', 
                'start_time' => '09:00:00', 
                'end_time' => '12:00:00', 
                'availability_status' => 'Available'
            ],
            [
                'schedule_id' => 2, 
                'doctor_id' => 2, 
                'availability_date' => '2026-02-25', 
                'start_time' => '13:00:00', 
                'end_time' => '17:00:00', 
                'availability_status' => 'Available'
            ],
        ]);

        // 6. Seed Appointments
        DB::table('appointments')->insert([
            [
                'patient_id' => 1, 
                'doctor_id' => 1, 
                'schedule_id' => 1, 
                'status' => 'Scheduled', 
                'reason_for_visit' => 'Routine check-up', 
                'notes' => null
            ],
            [
                'patient_id' => 2, 
                'doctor_id' => 2, 
                'schedule_id' => 2, 
                'status' => 'Scheduled', 
                'reason_for_visit' => 'Consultation', 
                'notes' => null
            ],
        ]);

        // 7. Seed Users (Passwords are properly hashed so login works seamlessly)
        DB::table('users')->insert([
            [
                'username' => 'admin', 
                'password' => Hash::make('admin'), 
                'role' => 'Admin', 
                'email' => 'admin@clinic.com', 
                'doctor_id' => null
            ],
            [
                'username' => 'reception', 
                'password' => Hash::make('reception'), 
                'role' => 'Receptionist', 
                'email' => 'reception@clinic.com', 
                'doctor_id' => null
            ],
            [
                'username' => 'doctor', 
                'password' => Hash::make('doctor'), 
                'role' => 'Doctor', 
                'email' => 'doctor@clinic.com', 
                'doctor_id' => 1
            ],
            [
                'username' => 'Sebas', 
                'password' => Hash::make('password123'), // Change 'password123' to whatever you like
                'role' => 'Admin', 
                'email' => 'sebas@gmail.com', 
                'doctor_id' => null
            ],
        ]);
    }
}