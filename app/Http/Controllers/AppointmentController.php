<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Schedule;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $user = auth()->user();

        $appointments = Appointment::with(['patient', 'doctor', 'schedule'])
            ->when($user->role === 'Doctor', function ($query) use ($user) {
                return $query->where('doctor_id', $user->doctor_id);
            })
            ->when($search, function ($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->whereHas('patient', function ($pq) use ($search) {
                        $pq->where('first_name', 'LIKE', "%{$search}%")->orWhere('last_name', 'LIKE', "%{$search}%");
                    })->orWhereHas('doctor', function ($dq) use ($search) {
                        $dq->where('first_name', 'LIKE', "%{$search}%")->orWhere('last_name', 'LIKE', "%{$search}%");
                    })->orWhere('status', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('appointment_id', 'desc')
            ->paginate(10);

        $patients = collect();
        $doctors = collect();
        $availableSchedules = collect();

        if ($user->role !== 'Doctor') {
            $patients = Patient::orderBy('last_name', 'asc')->get();
            
            $today = \Carbon\Carbon::today()->toDateString();

            // 1. Fetch the unbooked schedules from today onwards
            $availableSchedules = Schedule::with('doctor')
                ->whereIn('availability_status', ['Available', 'available'])
                ->where('availability_date', '>=', $today)
                ->whereDoesntHave('appointment')
                ->orderBy('availability_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();

            // 2. Filter doctors to ONLY include those who have slots in the available schedules above
            $availableDoctorIds = $availableSchedules->pluck('doctor_id')->unique();
            $doctors = Doctor::whereIn('doctor_id', $availableDoctorIds)->orderBy('last_name', 'asc')->get();
        }

        return view('admin.appointments', compact('appointments', 'search', 'patients', 'doctors', 'availableSchedules'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role === 'Doctor') {
            return redirect()->route('admin.appointments')->withErrors(['unauthorized' => 'Doctors are not authorized to book appointments.']);
        }

        $request->validate([
            'patient_id'       => 'required|exists:patients,patient_id',
            'doctor_id'        => 'required|exists:doctors,doctor_id',
            'schedule_id'      => 'required|unique:appointments,schedule_id|exists:schedules,schedule_id',
            'reason_for_visit' => 'required|string|max:255',
            'notes'            => 'nullable|string',
        ]);

        Appointment::create($request->all());

        return redirect()->route('admin.appointments')->with('success', 'Appointment booked successfully!');
    }

    public function edit($id)
    {
        $appointment = Appointment::with('schedule')->findOrFail($id);

        if (auth()->user()->role === 'Doctor' && $appointment->doctor_id !== auth()->user()->doctor_id) {
            return response()->json(['error' => 'Unauthorized access to this appointment.'], 403);
        }

        return response()->json($appointment);
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        if (auth()->user()->role === 'Doctor' && $appointment->doctor_id !== auth()->user()->doctor_id) {
            return redirect()->route('admin.appointments')->withErrors(['unauthorized' => 'You are not authorized to update this appointment.']);
        }

        // Validate ONLY the fields that are allowed to change
        $request->validate([
            'status'           => 'required|in:Scheduled,Completed,Cancelled,No Show',
            'reason_for_visit' => 'required|string|max:255',
            'notes'            => 'nullable|string',
        ]);

        // PROTECTION LAYER: Only accept updates for management parameters
        $appointment->update($request->only(['status', 'reason_for_visit', 'notes']));

        return redirect()->route('admin.appointments')->with('success', 'Appointment record modified successfully!');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'Admin') {
            return redirect()->route('admin.appointments')->withErrors(['unauthorized' => 'Only administrators are authorized to remove appointments.']);
        }

        $appointment = Appointment::findOrFail($id);
        $appointment->delete();
        return redirect()->route('admin.appointments')->with('success', 'Appointment dropped successfully.');
    }
}