<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $todayStr = \Carbon\Carbon::today()->toDateString();
        $user = auth()->user();

        if ($user->role === 'Doctor') {
            $doctor_id = $user->doctor_id;

            // Doctors see their own patients, their own total appointments, and their own appointments today
            $totalPatients = Appointment::where('doctor_id', $doctor_id)
                ->distinct('patient_id')
                ->count('patient_id');

            $totalAppointments = Appointment::where('doctor_id', $doctor_id)->count();

            $todayAppointments = Appointment::where('doctor_id', $doctor_id)
                ->whereHas('schedule', function($q) use ($todayStr) {
                    $q->where('availability_date', $todayStr);
                })->count();

            $totalDoctors = 1;

            $totalUsers = $todayAppointments; // We will use this count to display "Today's Appointments" for Doctors

            $recentAppointments = Appointment::where('doctor_id', $doctor_id)
                ->with(['patient', 'doctor', 'schedule'])
                ->orderBy('appointment_id', 'desc')
                ->take(5)
                ->get();
        } else {
            // Admin & Receptionist counts
            $totalPatients = Patient::count();
            $totalDoctors = Doctor::count();
            $totalAppointments = Appointment::count();
            
            $todayAppointments = Appointment::whereHas('schedule', function($q) use ($todayStr) {
                $q->where('availability_date', $todayStr);
            })->count();

            if ($user->role === 'Admin') {
                $totalUsers = User::count();
            } else {
                $totalUsers = 0; // Receptionists don't see system users count
            }

            $recentAppointments = Appointment::with(['patient', 'doctor', 'schedule'])
                ->orderBy('appointment_id', 'desc')
                ->take(5)
                ->get();
        }

        return view('admin.dashboard', compact(
            'totalPatients', 
            'totalDoctors',
            'totalAppointments', 
            'todayAppointments', 
            'totalUsers', 
            'recentAppointments'
        ));
    }
}