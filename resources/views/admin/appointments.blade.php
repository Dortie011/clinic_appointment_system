@extends('layouts.app')

@section('content')
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 font-sans">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Appointments Management</h2>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4 text-sm font-medium animate-pulse">
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

        <div class="flex flex-col md:flex-row justify-start items-start md:items-center gap-4 mb-6">
            <div class="flex flex-wrap gap-2 text-sm font-medium">
                @if(in_array(auth()->user()->role, ['Admin', 'Receptionist']))
                    <button onclick="toggleModal('addAppointmentModal')" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded shadow-sm hover:bg-gray-50 transition">
                        <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Book Appointment
                    </button>
                @endif
                <button id="editBtn" disabled onclick="openEditModal()" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded shadow-sm hover:bg-gray-50 transition opacity-50 cursor-not-allowed">
                    <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                    Edit Record
                </button>
                @if(auth()->user()->role === 'Admin')
                    <button id="deleteBtn" disabled onclick="openDeleteModal()" class="flex items-center gap-2 bg-white border border-gray-300 text-red-600 px-3 py-1.5 rounded shadow-sm hover:bg-red-50 transition opacity-50 cursor-not-allowed">
                        <svg class="w-4 h-4 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        Remove
                    </button>
                @endif
            </div>

            <form action="{{ route('admin.appointments') }}" method="GET" class="flex gap-2 w-full md:w-auto">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search patient, doctor, status..." class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full md:w-64">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 text-sm font-medium rounded shadow-sm transition">Search</button>
            </form>
        </div>

        <div class="overflow-x-auto border border-gray-200 rounded-md shadow-sm">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-700 font-semibold border-b border-gray-200 uppercase text-xs">
                        <th class="p-3 border-r">Patient</th>
                        <th class="p-3 border-r">Doctor</th>
                        <th class="p-3 border-r">Date & Time</th>
                        <th class="p-3 border-r">Reason</th>
                        <th class="p-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white text-gray-600">
                    @forelse($appointments as $app)
                        <tr onclick="selectAppointment(this, {{ $app->appointment_id }})" class="hover:bg-blue-50/40 transition cursor-pointer">
                            <td class="p-3 border-r font-medium text-gray-900">{{ $app->patient->first_name }} {{ $app->patient->last_name }}</td>
                            <td class="p-3 border-r">Dr. {{ $app->doctor->first_name }} {{ $app->doctor->last_name }}</td>
                            <td class="p-3 border-r font-medium text-gray-700">
                                @if($app->schedule)
                                    {{ \Carbon\Carbon::parse($app->schedule->availability_date)->format('d/m/Y') }} | 
                                    {{ date('h:i A', strtotime($app->schedule->start_time)) }}
                                @else
                                    <span class="text-red-500 italic">Slot Removed</span>
                                @endif
                            </td>
                            <td class="p-3 border-r max-w-xs truncate">{{ $app->reason_for_visit }}</td>
                            <td class="p-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                    {{ $app->status === 'Scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $app->status === 'Completed' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $app->status === 'Cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $app->status === 'No Show' ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ $app->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400 italic">No appointments booked yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $appointments->links() }}
        </div>
    </div>

    <div id="addAppointmentModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 border border-gray-100 overflow-hidden">
            <div class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold">Book New Appointment</h3>
                <button onclick="toggleModal('addAppointmentModal')" class="text-white text-2xl font-semibold">&times;</button>
            </div>
            <form action="{{ route('admin.appointments.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Patient</label>
                    <select name="patient_id" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Select Patient --</option>
                        @foreach($patients as $pat)
                            <option value="{{ $pat->patient_id }}">{{ $pat->first_name }} {{ $pat->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Doctor (Available Today / Tomorrow)</label>
                    <select id="add_doctor_select" name="doctor_id" onchange="filterSlotsByDoctor()" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Select Doctor --</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->doctor_id }}">Dr. {{ $doc->first_name }} {{ $doc->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Available Schedule Slot</label>
                    <select id="add_schedule_select" name="schedule_id" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Choose Slot --</option>
                        @foreach($availableSchedules as $sched)
                            <option value="{{ $sched->schedule_id }}" data-doctor-id="{{ $sched->doctor_id }}">
                                {{ \Carbon\Carbon::parse($sched->availability_date)->format('d/m/Y') }} | {{ date('h:i A', strtotime($sched->start_time)) }} - {{ date('h:i A', strtotime($sched->end_time)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Reason for Visit</label>
                    <input type="text" name="reason_for_visit" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Clinical Notes (Optional)</label>
                    <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-100">
                    <button type="button" onclick="toggleModal('addAppointmentModal')" class="bg-gray-100 text-gray-700 px-4 py-2 text-sm font-medium rounded">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-medium rounded shadow-sm">Save Booking</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editAppointmentModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 border border-gray-100 overflow-hidden">
            <div class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold">Modify Appointment Details</h3>
                <button type="button" onclick="toggleModal('editAppointmentModal')" class="text-white text-2xl font-semibold">&times;</button>
            </div>
            <form id="editAppointmentForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Patient (Locked)</label>
                        <select id="edit_patient_id" disabled class="w-full border border-gray-200 rounded px-3 py-2 text-sm bg-gray-100 text-gray-500 cursor-not-allowed appearance-none">
                            @foreach($patients as $pat)
                                <option value="{{ $pat->patient_id }}">{{ $pat->first_name }} {{ $pat->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Doctor (Locked)</label>
                        <select id="edit_doctor_id" disabled class="w-full border border-gray-200 rounded px-3 py-2 text-sm bg-gray-100 text-gray-500 cursor-not-allowed appearance-none">
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->doctor_id }}">Dr. {{ $doc->first_name }} {{ $doc->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Schedule Block ID</label>
                        <input type="text" id="edit_schedule_id" class="w-full border border-gray-200 rounded px-3 py-2 text-sm bg-gray-100 text-gray-500 cursor-not-allowed" readonly>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Status</label>
                        <select id="edit_status" name="status" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="Scheduled">Scheduled</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="No Show">No Show</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Reason for Visit</label>
                    <input type="text" id="edit_reason_for_visit" name="reason_for_visit" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Clinical Notes</label>
                    <textarea id="edit_notes" name="notes" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                </div>
                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-100">
                    <button type="button" onclick="toggleModal('editAppointmentModal')" class="bg-gray-100 text-gray-700 px-4 py-2 text-sm font-medium rounded">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-medium rounded shadow-sm">Update Appointment</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteAppointmentModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 border border-gray-100 overflow-hidden">
            <div class="bg-red-600 text-white px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold">Remove Appointment</h3>
                <button type="button" onclick="toggleModal('deleteAppointmentModal')" class="text-white text-2xl font-semibold">&times;</button>
            </div>
            <div class="p-6 text-sm text-gray-600">
                Are you sure you want to drop this appointment booking?
                <form id="deleteAppointmentForm" method="POST" class="mt-6 flex justify-end space-x-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="toggleModal('deleteAppointmentModal')" class="bg-gray-100 text-gray-700 px-4 py-2 text-sm font-medium rounded">Cancel</button>
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 text-sm font-medium rounded shadow-sm hover:bg-red-700">Delete Permanently</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let selectedAppointmentId = null;

        function selectAppointment(row, id) {
            document.querySelectorAll('tbody tr').forEach(tr => tr.classList.remove('bg-blue-100', 'font-semibold'));
            row.classList.add('bg-blue-100', 'font-semibold');
            selectedAppointmentId = id;

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

        function filterSlotsByDoctor() {
            const selectedDoctorId = document.getElementById('add_doctor_select').value;
            const scheduleSelect = document.getElementById('add_schedule_select');
            const options = scheduleSelect.options;

            scheduleSelect.value = "";

            for (let i = 0; i < options.length; i++) {
                const option = options[i];
                const doctorIdAttr = option.getAttribute('data-doctor-id');

                if (!selectedDoctorId) {
                    option.style.display = "block";
                } else if (doctorIdAttr === selectedDoctorId || !doctorIdAttr) {
                    option.style.display = "block";
                } else {
                    option.style.display = "none";
                }
            }
        }

        function openDeleteModal() {
            if (!selectedAppointmentId) return;
            document.getElementById('deleteAppointmentForm').action = `/admin/appointments/${selectedAppointmentId}/delete`;
            toggleModal('deleteAppointmentModal');
        }

        function openEditModal() {
            if (!selectedAppointmentId) return;

            fetch(`/admin/appointments/${selectedAppointmentId}/edit`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_patient_id').value = data.patient_id;
                    document.getElementById('edit_doctor_id').value = data.doctor_id;
                    document.getElementById('edit_schedule_id').value = data.schedule_id;
                    document.getElementById('edit_status').value = data.status;
                    document.getElementById('edit_reason_for_visit').value = data.reason_for_visit;
                    document.getElementById('edit_notes').value = data.notes ?? '';

                    document.getElementById('editAppointmentForm').action = `/admin/appointments/${selectedAppointmentId}/update`;
                    toggleModal('editAppointmentModal');
                });
        }
    </script>
@endsection