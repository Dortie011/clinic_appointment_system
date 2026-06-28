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

test('Receptionist role has access to dashboard, patients, appointments, and schedules, but is forbidden from doctors, users, and modifying schedules', function () {
    $receptionist = createUser('Receptionist');

    $this->actingAs($receptionist);

    $this->get(route('admin.dashboard'))->assertOk();
    $this->get(route('admin.patients'))->assertOk();
    $this->get(route('admin.appointments'))->assertOk();
    $this->get(route('admin.schedules'))->assertOk();

    // Forbidden routes should yield 403 response
    $this->get(route('admin.doctors'))->assertStatus(403);
    $this->get(route('admin.users'))->assertStatus(403);

    // Assert receptionist cannot modify schedules
    $this->post(route('admin.schedules.store'), [])->assertStatus(403);
    $this->get(route('admin.schedules.edit', 1))->assertStatus(403);
    $this->put(route('admin.schedules.update', 1), [])->assertStatus(403);
    $this->delete(route('admin.schedules.delete', 1))->assertStatus(403);
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

test('Receptionist cannot update appointment status and cannot delete appointments', function () {
    $receptionist = createUser('Receptionist');
    
    $doctor = Doctor::create([
        'first_name' => 'Doc',
        'last_name' => 'Test',
        'phone' => '123',
        'email' => 'doc@example.com',
        'room_num' => '1',
    ]);
    
    $patient = Patient::create([
        'first_name' => 'Pat',
        'last_name' => 'Test',
        'birth_date' => '2000-01-01',
        'gender' => 'Male',
        'phone_num' => '123',
        'email' => 'pat@example.com',
        'address' => 'Addr',
    ]);
    
    $schedule = Schedule::create([
        'doctor_id' => $doctor->doctor_id,
        'availability_date' => '2026-07-01',
        'start_time' => '09:00',
        'end_time' => '10:00',
        'availability_status' => 'Available',
    ]);
    
    $appointment = Appointment::create([
        'patient_id' => $patient->patient_id,
        'doctor_id' => $doctor->doctor_id,
        'schedule_id' => $schedule->schedule_id,
        'status' => 'Scheduled',
        'reason_for_visit' => 'Checkup',
    ]);

    $this->actingAs($receptionist);

    // Try to update appointment status to Completed
    $response = $this->put(route('admin.appointments.update', $appointment->appointment_id), [
        'doctor_id' => $doctor->doctor_id,
        'schedule_id' => $schedule->schedule_id,
        'reason_for_visit' => 'Updated Checkup',
        'status' => 'Completed', // Attempt status change
    ]);

    $response->assertRedirect(route('admin.appointments'));
    
    // Verify that reason_for_visit changed, but status remained 'Scheduled'
    $appointment->refresh();
    expect($appointment->reason_for_visit)->toBe('Updated Checkup');
    expect($appointment->status)->toBe('Scheduled');

    // Verify receptionist cannot delete appointment
    $this->delete(route('admin.appointments.delete', $appointment->appointment_id))
        ->assertStatus(403);
});
