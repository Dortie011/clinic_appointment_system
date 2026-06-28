<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Doctor;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $user = auth()->user();

        $schedules = Schedule::with('doctor')
            ->when($user->role === 'Doctor', function ($query) use ($user) {
                return $query->where('doctor_id', $user->doctor_id);
            })
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->whereHas('doctor', function ($dq) use ($search) {
                        $dq->where('first_name', 'LIKE', "%{$search}%")
                          ->orWhere('last_name', 'LIKE', "%{$search}%");
                    })
                    ->orWhere('availability_date', 'LIKE', "%{$search}%")
                    ->orWhere('availability_status', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('schedule_id', 'desc')
            ->paginate(10);

        if ($user->role === 'Doctor') {
            $doctors = Doctor::where('doctor_id', $user->doctor_id)->get();
        } else {
            $doctors = Doctor::orderBy('last_name', 'asc')->get();
        }

        return view('admin.schedules', compact('schedules', 'search', 'doctors'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $rules = [
            'availability_date'   => 'required|date|after_or_equal:today',
            'start_time'          => 'required',
            'end_time'            => 'required',
            'availability_status' => 'required|in:Available,On Leave,Blocked',
        ];

        if ($user->role !== 'Doctor') {
            $rules['doctor_id'] = 'required|exists:doctors,doctor_id';
        }

        $request->validate($rules);

        $data = $request->all();
        if ($user->role === 'Doctor') {
            $data['doctor_id'] = $user->doctor_id;
        }

        try {
            Schedule::create($data);
            return redirect()->route('admin.schedules')->with('success', 'Doctor shift scheduled perfectly!');
        } catch (\Illuminate\Database\QueryException $e) {
            // Catches any double-bookings matching composite unique constraint
            if ($e->errorInfo[1] == 1062) {
                return redirect()->back()->withErrors(['duplicate' => 'This doctor already has a slot scheduled for this exact date and time window.'])->withInput();
            }
            throw $e;
        }
    }

    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);
        $user = auth()->user();

        if ($user->role === 'Doctor' && $schedule->doctor_id !== $user->doctor_id) {
            return response()->json(['error' => 'Unauthorized access to this schedule.'], 403);
        }

        return response()->json($schedule);
    }

    public function update(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        $user = auth()->user();

        if ($user->role === 'Doctor' && $schedule->doctor_id !== $user->doctor_id) {
            return redirect()->route('admin.schedules')->withErrors(['unauthorized' => 'You are not authorized to update this schedule.']);
        }

        $rules = [
            'availability_date'   => 'required|date',
            'start_time'          => 'required',
            'end_time'            => 'required',
            'availability_status' => 'required|in:Available,On Leave,Blocked',
        ];

        if ($user->role !== 'Doctor') {
            $rules['doctor_id'] = 'required|exists:doctors,doctor_id';
        }

        $request->validate($rules);

        $data = $request->all();
        if ($user->role === 'Doctor') {
            $data['doctor_id'] = $user->doctor_id;
        }

        try {
            $schedule->update($data);
            return redirect()->route('admin.schedules')->with('success', 'Schedule record adjusted successfully!');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return redirect()->back()->withErrors(['duplicate' => 'Another slot overlaps this exact time window for this practitioner.'])->withInput();
            }
            throw $e;
        }
    }

    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $user = auth()->user();

        if ($user->role !== 'Admin' && ($user->role !== 'Doctor' || $schedule->doctor_id !== $user->doctor_id)) {
            return redirect()->route('admin.schedules')->withErrors(['unauthorized' => 'You are not authorized to delete this schedule.']);
        }

        $schedule->delete();
        return redirect()->route('admin.schedules')->with('success', 'Schedule shift deleted successfully!');
    }
}