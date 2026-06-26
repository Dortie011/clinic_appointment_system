<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
   public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::with('doctor')
            ->when($search, function ($query, $search) {
                return $query->where('username', 'LIKE', "%{$search}%")
                             ->orWhere('email', 'LIKE', "%{$search}%")
                             ->orWhere('role', 'LIKE', "%{$search}%");
            })
            ->orderBy('user_id', 'desc')
            ->paginate(10);

        // 1. Get a clean list of all doctor IDs that are already taken in the users table
        $takenDoctorIds = User::whereNotNull('doctor_id')->pluck('doctor_id')->toArray();

        // 2. Fetch only the doctors whose IDs are NOT in that taken list
        $doctors = Doctor::whereNotIn('doctor_id', $takenDoctorIds)
            ->orderBy('last_name', 'asc')
            ->get();

        return view('admin.users', compact('users', 'search', 'doctors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username'  => 'required|string|max:50|unique:users,username',
            'password'  => 'required|string|min:6',
            'role'      => 'required|in:Admin,Receptionist,Doctor',
            'email'     => 'nullable|email|max:100|unique:users,email',
            'doctor_id' => 'nullable|required_if:role,Doctor|exists:doctors,doctor_id',
        ]);

        User::create([
            'username'  => $request->username,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'email'     => $request->email,
            'doctor_id' => $request->role === 'Doctor' ? $request->doctor_id : null,
        ]);

        return redirect()->route('admin.users')->with('success', 'User account created successfully!');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'username'  => 'required|string|max:50|unique:users,username,' . $user->user_id . ',user_id',
            'role'      => 'required|in:Admin,Receptionist,Doctor',
            'email'     => 'nullable|email|max:100|unique:users,email,' . $user->user_id . ',user_id',
            'password'  => 'nullable|string|min:6',
            'doctor_id' => 'nullable|required_if:role,Doctor|exists:doctors,doctor_id',
        ]);

        $userData = [
            'username'  => $request->username,
            'role'      => $request->role,
            'email'     => $request->email,
        ];

        // If they are already a doctor, don't overwrite or erase their linked profile ID
        if ($user->role === 'Doctor') {
            $userData['doctor_id'] = $user->doctor_id;
        } else {
            $userData['doctor_id'] = $request->role === 'Doctor' ? $request->doctor_id : null;
        }

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return redirect()->route('admin.users')->with('success', 'User configuration updated successfully!');
    }

    public function destroy($id)
    {
        if (auth()->id() == $id) {
            return redirect()->back()->withErrors(['self_delete' => 'You cannot drop your own active administrator profile context session.']);
        }

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User profile removed successfully.');
    }
}