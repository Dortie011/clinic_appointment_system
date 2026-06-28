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

        // Validate fields
        $request->validate([
            'status'           => 'required|in:Scheduled,Completed,Cancelled,No Show',
            'reason_for_visit' => 'required|string|max:255',
            'notes'            => 'nullable|string',
            'doctor_id'        => 'required|exists:doctors,doctor_id',
            'schedule_id'      => 'required|exists:schedules,schedule_id',
        ]);

        $schedule = \App\Models\Schedule::findOrFail($request->schedule_id);

        // 1. Validate that the selected schedule belongs to the selected doctor
        if ($schedule->doctor_id != $request->doctor_id) {
            return redirect()->back()->withErrors(['schedule_id' => 'The selected schedule does not belong to the selected doctor.'])->withInput();
        }

        // 2. Prevent selecting inactive schedules (unless it is the current schedule of the appointment)
        if (!in_array(strtolower($schedule->availability_status), ['available']) && $appointment->schedule_id != $schedule->schedule_id) {
            return redirect()->back()->withErrors(['schedule_id' => 'The selected schedule is not active/available.'])->withInput();
        }

        // 3. Prevent double-booking (unless it's the current schedule of this appointment)
        $doubleBooked = Appointment::where('schedule_id', $schedule->schedule_id)
            ->where('appointment_id', '!=', $appointment->appointment_id)
            ->exists();
        if ($doubleBooked) {
            return redirect()->back()->withErrors(['schedule_id' => 'This schedule slot is already booked for another appointment.'])->withInput();
        }

        $updateData = [
            'reason_for_visit' => $request->reason_for_visit,
            'doctor_id'        => $request->doctor_id,
            'schedule_id'      => $request->schedule_id,
        ];

        // Only Admin and Doctor can update status
        if (in_array(auth()->user()->role, ['Admin', 'Doctor'])) {
            $updateData['status'] = $request->status;
        } else {
            $updateData['status'] = $appointment->status;
        }

        // Only Doctors can update clinical notes
        if (auth()->user()->role === 'Doctor') {
            $updateData['notes'] = $request->notes;
        }

        $appointment->update($updateData);

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

    public function getActiveDoctors(Request $request)
    {
        $today = \Carbon\Carbon::today()->toDateString();
        $includeDoctorId = $request->query('include_doctor_id');

        $doctors = \App\Models\Doctor::where(function ($query) use ($today, $includeDoctorId) {
            $query->whereHas('schedules', function ($q) use ($today) {
                $q->where('availability_date', '>=', $today)
                  ->whereIn('availability_status', ['Available', 'available'])
                  ->whereDoesntHave('appointment');
            });

            if ($includeDoctorId) {
                $query->orWhere('doctor_id', $includeDoctorId);
            }
        })
        ->orderBy('last_name', 'asc')
        ->get();

        return response()->json($doctors);
    }

    public function getDoctorSchedules($doctorId, Request $request)
    {
        $today = \Carbon\Carbon::today()->toDateString();
        $includeScheduleId = $request->query('include_schedule_id');

        $schedules = \App\Models\Schedule::where('doctor_id', $doctorId)
            ->where(function ($query) use ($today, $includeScheduleId) {
                $query->where(function ($q) use ($today) {
                    $q->where('availability_date', '>=', $today)
                      ->whereIn('availability_status', ['Available', 'available'])
                      ->whereDoesntHave('appointment');
                });
                
                if ($includeScheduleId) {
                    $query->orWhere('schedule_id', $includeScheduleId);
                }
            })
            ->orderBy('availability_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($sched) {
                $dayOfWeek = \Carbon\Carbon::parse($sched->availability_date)->format('l');
                $dateFormatted = \Carbon\Carbon::parse($sched->availability_date)->format('d/m/Y');
                $startTime = date('h:i A', strtotime($sched->start_time));
                $endTime = date('h:i A', strtotime($sched->end_time));
                
                $sched->formatted_schedule = "{$dayOfWeek} ({$dateFormatted}) | {$startTime} – {$endTime}";
                return $sched;
            });

        return response()->json($schedules);
    }
}