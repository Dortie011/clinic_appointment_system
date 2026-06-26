<?php

use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\Appointment;

function createUser($role, $doctorId = null) {
    $uniq = uniqid();
    return User::create([
        'username' => 'test_' . strtolower($role) . '_' . $uniq,
        'email' => 'test_' . strtolower($role) . '_' . $uniq . '@example.com',
        'password' => Hash::make('password'),
        'role' => $role,
        'doctor_id' => $doctorId,
    ]);
}

test('Admin role has access to all admin panel routes', function () {
    $admin = createUser('Admin');

    $this->actingAs($admin);

    $this->get(route('admin.dashboard'))->assertOk();
    $this->get(route('admin.patients'))->assertOk();
    $this->get(route('admin.doctors'))->assertOk();
    $this->get(route('admin.schedules'))->assertOk();
    $this->get(route('admin.appointments'))->assertOk();
    $this->get(route('admin.users'))->assertOk();
});

test('Receptionist role has access to dashboard, patients, and appointments, but is forbidden from doctors, schedules, and users', function () {
    $receptionist = createUser('Receptionist');

    $this->actingAs($receptionist);

    $this->get(route('admin.dashboard'))->assertOk();
    $this->get(route('admin.patients'))->assertOk();
    $this->get(route('admin.appointments'))->assertOk();

    // Forbidden routes should yield 403 response
    $this->get(route('admin.doctors'))->assertStatus(403);
    $this->get(route('admin.schedules'))->assertStatus(403);
    $this->get(route('admin.users'))->assertStatus(403);
});

test('Doctor role has access to dashboard, schedules, and appointments, but is forbidden from patients, doctors, and users', function () {
    $doctorRecord = Doctor::create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '1234567890',
        'email' => 'doctor@example.com',
        'room_num' => 'A-101',
    ]);

    $doctorUser = createUser('Doctor', $doctorRecord->doctor_id);

    $this->actingAs($doctorUser);

    $this->get(route('admin.dashboard'))->assertOk();
    $this->get(route('admin.schedules'))->assertOk();
    $this->get(route('admin.appointments'))->assertOk();

    // Forbidden routes should yield 403 response
    $this->get(route('admin.patients'))->assertStatus(403);
    $this->get(route('admin.doctors'))->assertStatus(403);
    $this->get(route('admin.users'))->assertStatus(403);
});

test('Doctor role can only view their own assigned appointments', function () {
    $doc1 = Doctor::create([
        'first_name' => 'Doc1',
        'last_name' => 'Test',
        'phone' => '111',
        'email' => 'doc1@example.com',
        'room_num' => '1',
    ]);
    $doc2 = Doctor::create([
        'first_name' => 'Doc2',
        'last_name' => 'Test',
        'phone' => '222',
        'email' => 'doc2@example.com',
        'room_num' => '2',
    ]);

    $userDoc1 = createUser('Doctor', $doc1->doctor_id);
    $userDoc2 = createUser('Doctor', $doc2->doctor_id);

    $patient = Patient::create([
        'first_name' => 'Pat',
        'last_name' => 'Test',
        'birth_date' => '2000-01-01',
        'gender' => 'Male',
        'phone_num' => '333',
        'email' => 'pat@example.com',
        'address' => 'Addr',
    ]);

    $schedule1 = Schedule::create([
        'doctor_id' => $doc1->doctor_id,
        'availability_date' => '2026-07-01',
        'start_time' => '09:00',
        'end_time' => '10:00',
        'availability_status' => 'Available',
    ]);

    $schedule2 = Schedule::create([
        'doctor_id' => $doc2->doctor_id,
        'availability_date' => '2026-07-01',
        'start_time' => '10:00',
        'end_time' => '11:00',
        'availability_status' => 'Available',
    ]);

    $app1 = Appointment::create([
        'patient_id' => $patient->patient_id,
        'doctor_id' => $doc1->doctor_id,
        'schedule_id' => $schedule1->schedule_id,
        'status' => 'Scheduled',
        'reason_for_visit' => 'Checkup 1',
    ]);

    $app2 = Appointment::create([
        'patient_id' => $patient->patient_id,
        'doctor_id' => $doc2->doctor_id,
        'schedule_id' => $schedule2->schedule_id,
        'status' => 'Scheduled',
        'reason_for_visit' => 'Checkup 2',
    ]);

    // Acting as Doctor 1
    $this->actingAs($userDoc1);
    $response = $this->get(route('admin.appointments'));
    $response->assertSee('Checkup 1');
    $response->assertDontSee('Checkup 2');

    // Acting as Doctor 2
    $this->actingAs($userDoc2);
    $response = $this->get(route('admin.appointments'));
    $response->assertSee('Checkup 2');
    $response->assertDontSee('Checkup 1');
});
