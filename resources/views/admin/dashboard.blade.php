@extends('layouts.app')

@section('content')
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 font-sans">
        
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            @if(auth()->user()->role === 'Doctor')
                <div class="bg-white p-6 border border-gray-200 rounded-lg shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">My Patients</p>
                    <h3 class="text-3xl font-extrabold text-gray-800 mt-2">{{ $totalPatients }}</h3>
                </div>

                <div class="bg-white p-6 border border-gray-200 rounded-lg shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">My Appointments</p>
                    <h3 class="text-3xl font-extrabold text-gray-800 mt-2">{{ $totalAppointments }}</h3>
                </div>

                <div class="bg-white p-6 border border-gray-200 rounded-lg shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Today's Appointments</p>
                    <h3 class="text-3xl font-extrabold text-gray-800 mt-2">{{ $totalUsers }}</h3>
                </div>
            @else
                <div class="bg-white p-6 border border-gray-200 rounded-lg shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Patients</p>
                    <h3 class="text-3xl font-extrabold text-gray-800 mt-2">{{ $totalPatients }}</h3>
                </div>

                <div class="bg-white p-6 border border-gray-200 rounded-lg shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active Doctors</p>
                    <h3 class="text-3xl font-extrabold text-gray-800 mt-2">{{ $totalDoctors }}</h3>
                </div>

                @if(auth()->user()->role === 'Admin')
                    <div class="bg-white p-6 border border-gray-200 rounded-lg shadow-sm">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">System Users</p>
                        <h3 class="text-3xl font-extrabold text-gray-800 mt-2">{{ $totalUsers }}</h3>
                    </div>
                @endif
            @endif
        </div>

        <div class="bg-gray-50/50 p-6 border border-gray-200 rounded-lg">
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Quick Navigation Shortcut Access</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                @if(in_array(auth()->user()->role, ['Admin', 'Receptionist']))
                    <a href="{{ route('admin.patients') }}" class="p-3 bg-white border border-gray-300 hover:border-blue-500 rounded text-center font-medium text-sm text-gray-700 hover:text-blue-600 shadow-sm transition block">
                        Open Patients Grid
                    </a>
                @endif
                
                @if(auth()->user()->role === 'Admin')
                    <a href="{{ route('admin.doctors') }}" class="p-3 bg-white border border-gray-300 hover:border-blue-500 rounded text-center font-semibold text-sm text-gray-700 hover:text-blue-600 shadow-sm transition block">
                        Open Doctors Grid
                    </a>
                @endif
                
                @if(in_array(auth()->user()->role, ['Admin', 'Doctor']))
                    <a href="{{ route('admin.schedules') }}" class="p-3 bg-white border border-gray-300 hover:border-blue-500 rounded text-center font-semibold text-sm text-gray-700 hover:text-blue-600 shadow-sm transition block">
                        View Schedules
                    </a>
                @endif
                
                @if(in_array(auth()->user()->role, ['Admin', 'Receptionist', 'Doctor']))
                    <a href="{{ route('admin.appointments') }}" class="p-3 bg-white border border-gray-300 hover:border-blue-500 rounded text-center font-semibold text-sm text-gray-700 hover:text-blue-600 shadow-sm transition block">
                        Appointments Records
                    </a>
                @endif
                
            </div>
        </div>
        
    </div>
@endsection