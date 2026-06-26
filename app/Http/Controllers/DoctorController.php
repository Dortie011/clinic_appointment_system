<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Specialization;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Eagerly load relationship records to display in the table grid
        $doctors = Doctor::with('specializations')
            ->when($search, function ($query, $search) {
                return $query->where('first_name', 'LIKE', "%{$search}%")
                             ->orWhere('last_name', 'LIKE', "%{$search}%");
            })
            ->orderBy('doctor_id', 'desc')
            ->paginate(10);

        // Fixed: Order by primary key to prevent 'Unknown column name' errors
        $allSpecializations = Specialization::orderBy('specialization_id', 'asc')->get();

        return view('admin.doctors', compact('doctors', 'search', 'allSpecializations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name'        => 'required|string|max:50',
            'last_name'         => 'required|string|max:50',
            'phone'         => 'required|string|max:15',
            'email'             => 'required|email|unique:doctors,email',
            'room_num'          => 'required|string|max:50',
            'specialization_id' => 'required|exists:specialization,specialization_id',
        ]);

        $doctor = Doctor::create($request->only(['first_name', 'last_name', 'phone','room_num', 'email']));
        
        // Sync record into pivot table rows
        $doctor->specializations()->sync([$request->specialization_id]);

        return redirect()->route('admin.doctors')->with('success', 'Doctor profile created successfully!');
    }

    public function edit($id)
    {
        $doctor = Doctor::with('specializations')->findOrFail($id);
        return response()->json($doctor);
    }

    public function update(Request $request, $id)
    {
        $doctor = Doctor::findOrFail($id);

        $request->validate([
            'first_name'        => 'required|string|max:50',
            'last_name'         => 'required|string|max:50',
            'phone'         => 'required|string|max:15',
            'email'             => 'required|email|unique:doctors,email,' . $doctor->doctor_id . ',doctor_id',
            'room_num'          => 'required|string|max:50',
            'specialization_id' => 'required|exists:specialization,specialization_id',
        ]);

        $doctor->update($request->only(['first_name', 'last_name', 'phone','room_num', 'email']));
        
        $doctor->specializations()->sync([$request->specialization_id]);

        return redirect()->route('admin.doctors')->with('success', 'Doctor profile updated successfully!');
    }

    public function destroy($id)
    {
        $doctor = Doctor::findOrFail($id);
        $doctor->specializations()->detach(); 
        $doctor->delete();
        return redirect()->route('admin.doctors')->with('success', 'Doctor profile removed successfully!');
    }
}