<?php

namespace App\Http\Controllers;

// MAKE SURE THESE THREE LINES ARE EXACTLY LIKE THIS:
use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $patients = Patient::query()
            ->when($search, function ($query, $search) {
                return $query->where('first_name', 'LIKE', "%{$search}%")
                             ->orWhere('last_name', 'LIKE', "%{$search}%");
            })
            ->orderBy('patient_id', 'desc')
            ->paginate(10);

        return view('admin.patients', compact('patients', 'search'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name'  => 'required|string|max:50',
            'birth_date' => 'required|date',
            'gender'     => 'required|string',
            'phone_num'  => 'required|string|max:15',
            'email'      => 'required|email|unique:patients,email',
            'address'    => 'required|string|max:255',
        ]);

        Patient::create($validatedData);
        return redirect()->route('admin.patients')->with('success', 'Patient record added successfully!');
    }

    public function edit($id)
    {
        $patient = Patient::findOrFail($id);
        return response()->json($patient);
    }

    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        $validatedData = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name'  => 'required|string|max:50',
            'birth_date' => 'required|date',
            'gender'     => 'required|string',
            'phone_num'  => 'required|string|max:15',
            'email'      => 'required|email|unique:patients,email,' . $patient->patient_id . ',patient_id',
            'address'    => 'required|string|max:255',
        ]);

        $patient->update($validatedData);
        return redirect()->route('admin.patients')->with('success', 'Patient record updated successfully!');
    }

    public function destroy($id)
    {
        $patient = Patient::findOrFail($id);
        $patient->delete();
        return redirect()->route('admin.patients')->with('success', 'Patient record deleted successfully!');
    }

    public function dashboard()
    {
        // Gather database counts for the summary cards
        $totalPatients = Patient::count();
        $totalDoctors = \Schema::hasTable('doctors') ? \DB::table('doctors')->count() : 0;
        $totalUsers = User::count();

        // Pass the statistics straight to your dashboard template
        return view('admin.dashboard', compact('totalPatients', 'totalDoctors', 'totalUsers'));
    }
}