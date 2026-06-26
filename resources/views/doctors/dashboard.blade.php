@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800">Doctor Clinical Workstation</h2>
        <p class="text-sm text-gray-500">Welcome back! Review your assigned patient schedules and medical tasks below.</p>
    </div>

    <div class="border-t border-gray-100 pt-4">
        <h3 class="text-md font-semibold text-gray-700 mb-3">Your Assigned Appointments Today</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 font-medium border-b border-gray-200">
                        <th class="p-3">Patient Name</th>
                        <th class="p-3">Schedule Date/Time</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse($assignedAppointments ?? [] as $appointment)
                        <tr>
                            <td class="p-3 font-medium text-gray-900">{{ $appointment->patient->name }}</td>
                            <td class="p-3">{{ $appointment->schedule->availability_date }}</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ $appointment->status }}
                                </span>
                            </td>
                            <td class="p-3">
                                <a href="#" class="text-blue-600 hover:underline font-medium text-xs mr-3">Add Notes</a>
                                <a href="#" class="text-green-600 hover:underline font-medium text-xs">Prescription</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-gray-400">No appointments assigned to your queue today.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection