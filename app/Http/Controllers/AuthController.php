<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Show the login form interface
    public function showLogin()
    {
        if (Auth::check()) {
            // 🎯 FIXED: Redirect already logged-in users to the dashboard instead of patients
            return redirect()->route('admin.dashboard');
        }
        return view('auth.login');
    }

    // Process the login form submission
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Attempt to log the user in
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // 🎯 FIXED: Redirect successful logins to the safe dashboard route
            return redirect()->route('admin.dashboard');
        }

        // If login fails, redirect back with an error message
        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    // Handle logging out of the system
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}