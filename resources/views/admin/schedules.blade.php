@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 font-sans">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Schedules Management</h2>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4 text-sm">
                <ul class="list-disc pl-5 font-medium">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div class="flex flex-wrap gap-2 text-sm font-medium">
                @if(in_array(auth()->user()->role, ['Admin', 'Doctor']))
                    <button onclick="toggleModal('addScheduleModal')" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded shadow-sm hover:bg-gray-50 transition">
                        <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                        </svg>
                        Add Schedule
                    </button>
                    
                    <button id="editBtn" disabled onclick="openEditModal()" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded shadow-sm hover:bg-gray-50 transition opacity-50 cursor-not-allowed">
                        <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        Edit Schedule
                    </button>

                    <button id="deleteBtn" disabled onclick="openDeleteModal()" class="flex items-center gap-2 bg-white border border-gray-300 text-red-600 px-3 py-1.5 rounded shadow-sm hover:bg-red-50 transition opacity-50 cursor-not-allowed">
                        <svg class="w-4 h-4 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        Delete Schedule
                    </button>
                @endif
            </div>

            <form action="{{ route('admin.schedules') }}" method="GET" class="flex gap-2 w-full md:w-auto">
                <div class="relative w-full md:w-80">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm font-medium">Search:</span>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="YYYY-MM-DD or Status..." class="w-full border border-gray-300 rounded pl-16 pr-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-1.5 text-sm font-medium rounded shadow-sm transition">Search</button>
                @if(request('search'))
                    <a href="{{ route('admin.schedules') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1.5 text-sm font-medium rounded flex items-center">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto border border-gray-200 rounded-md shadow-sm">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-700 font-semibold border-b border-gray-200 uppercase tracking-wider text-xs">
                        <th class="p-3 border-r">Doctor Name</th>
                        <th class="p-3 border-r">Availability Date</th>
                        <th class="p-3 border-r">Start Time</th>
                        <th class="p-3 border-r">End Time</th>
                        <th class="p-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white text-gray-600">
                    @forelse($schedules as $sched)
                        <tr onclick="selectSchedule(this, {{ $sched->schedule_id }})" class="hover:bg-blue-50/40 transition cursor-pointer">
                            <td class="p-3 border-r font-medium text-gray-900">
                                {{ $sched->doctor ? 'Dr. ' . $sched->doctor->first_name . ' ' . $sched->doctor->last_name : 'Unknown Doctor' }}
                            </td>
                            <td class="p-3 border-r font-medium text-gray-700">
                                {{ \Carbon\Carbon::parse($sched->availability_date)->format('d/m/Y') }}
                            </td>
                            <td class="p-3 border-r text-green-600 font-semibold">{{ date('h:i A', strtotime($sched->start_time)) }}</td>
                            <td class="p-3 border-r text-red-600 font-semibold">{{ date('h:i A', strtotime($sched->end_time)) }}</td>
                            <td class="p-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold 
                                    {{ $sched->availability_status === 'Available' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $sched->availability_status === 'On Leave' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $sched->availability_status === 'Blocked' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $sched->availability_status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400 italic">No corresponding schedule records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $schedules->appends(['search' => request('search')])->links() }}
        </div>
    </div>
</div>

<div id="addScheduleModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 overflow-hidden border border-gray-100">
        <div class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
            <h3 class="text-lg font-bold">Add Doctor Availability Slot</h3>
            <button onclick="toggleModal('addScheduleModal')" class="text-white hover:text-gray-200 text-2xl font-semibold">&times;</button>
        </div>
        <form action="{{ route('admin.schedules.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            @if(auth()->user()->role === 'Doctor')
                <input type="hidden" name="doctor_id" value="{{ auth()->user()->doctor_id }}">
            @else
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Select Doctor</label>
                    <select name="doctor_id" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Choose Practitioner --</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->doctor_id }}">Dr. {{ $doc->first_name }} {{ $doc->last_name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Availability Date</label>
                    <input type="date" name="availability_date" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Status</label>
                    <select name="availability_status" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="Available">Available</option>
                        <option value="On Leave">On Leave</option>
                        <option value="Blocked">Blocked</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Start Time</label>
                    <input type="time" name="start_time" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">End Time</label>
                    <input type="time" name="end_time" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>
            <div class="flex justify-end space-x-2 pt-4 border-t border-gray-100">
                <button type="button" onclick="toggleModal('addScheduleModal')" class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-4 py-2 text-sm font-medium rounded">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-medium rounded shadow-sm">Save Shift Slot</button>
            </div>
        </form>
    </div>
</div>

<div id="editScheduleModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 overflow-hidden border border-gray-100">
        <div class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
            <h3 class="text-lg font-bold">Modify Schedule Record</h3>
            <button type="button" onclick="toggleModal('editScheduleModal')" class="text-white hover:text-gray-200 text-2xl font-semibold">&times;</button>
        </div>
        <form id="editScheduleForm" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            @if(auth()->user()->role === 'Doctor')
                <input type="hidden" id="edit_doctor_id" name="doctor_id" value="{{ auth()->user()->doctor_id }}">
            @else
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Assigned Doctor</label>
                    <select id="edit_doctor_id" name="doctor_id" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->doctor_id }}">Dr. {{ $doc->first_name }} {{ $doc->last_name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Availability Date</label>
                    <input type="date" id="edit_availability_date" name="availability_date" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Status</label>
                    <select id="edit_availability_status" name="availability_status" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="Available">Available</option>
                        <option value="On Leave">On Leave</option>
                        <option value="Blocked">Blocked</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Start Time</label>
                    <input type="time" id="edit_start_time" name="start_time" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">End Time</label>
                    <input type="time" id="edit_end_time" name="end_time" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>
            <div class="flex justify-end space-x-2 pt-4 border-t border-gray-100">
                <button type="button" onclick="toggleModal('editScheduleModal')" class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-4 py-2 text-sm font-medium rounded">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-medium rounded shadow-sm">Update Slot Details</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteScheduleModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 overflow-hidden border border-gray-100">
        <div class="bg-red-600 text-white px-6 py-4 flex justify-between items-center">
            <h3 class="text-lg font-bold">Confirm Shift Removal</h3>
            <button type="button" onclick="toggleModal('deleteScheduleModal')" class="text-white hover:text-gray-200 text-2xl font-semibold">&times;</button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 text-sm">Are you sure you want to permanently remove this doctor availability shift schedule? This action cannot be undone.</p>
            <form id="deleteScheduleForm" method="POST" class="mt-6 flex justify-end space-x-2">
                @csrf
                @method('DELETE')
                <button type="button" onclick="toggleModal('deleteScheduleModal')" class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-4 py-2 text-sm font-medium rounded">Cancel</button>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 text-sm font-medium rounded shadow-sm">Delete Permanently</button>
            </form>
        </div>
    </div>
</div>

<script>
    let selectedScheduleId = null;

    function selectSchedule(row, id) {
        document.querySelectorAll('tbody tr').forEach(tr => tr.classList.remove('bg-blue-100', 'font-semibold'));
        row.classList.add('bg-blue-100', 'font-semibold');
        selectedScheduleId = id;

        const editBtn = document.getElementById('editBtn');
        if (editBtn) {
            editBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            editBtn.removeAttribute('disabled');
        }

        const deleteBtn = document.getElementById('deleteBtn');
        if (deleteBtn) {
            deleteBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            deleteBtn.removeAttribute('disabled');
        }
    }

    function toggleModal(modalId) {
        document.getElementById(modalId).classList.toggle('hidden');
    }

    function openDeleteModal() {
        if (!selectedScheduleId) return;
        document.getElementById('deleteScheduleForm').action = `/admin/schedules/${selectedScheduleId}/delete`;
        toggleModal('deleteScheduleModal');
    }

    function openEditModal() {
        if (!selectedScheduleId) return;

        fetch(`/admin/schedules/${selectedScheduleId}/edit`)
            .then(response => response.json())
            .then(sched => {
                document.getElementById('edit_doctor_id').value = sched.doctor_id;
                document.getElementById('edit_availability_date').value = sched.availability_date;
                document.getElementById('edit_availability_status').value = sched.availability_status;
                document.getElementById('edit_start_time').value = sched.start_time.substring(0, 5);
                document.getElementById('edit_end_time').value = sched.end_time.substring(0, 5);

                document.getElementById('editScheduleForm').action = `/admin/schedules/${selectedScheduleId}/update`;
                toggleModal('editScheduleModal');
            })
            .catch(error => {
                console.error(error);
                alert('Error loading shift details.');
            });
    }
</script>
@endsection