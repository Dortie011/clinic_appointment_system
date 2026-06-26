@extends('layouts.app')

@section('content')
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 font-sans">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Patients Management</h2>

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

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div class="flex flex-wrap gap-2 text-sm font-medium">
                <button onclick="toggleModal('addPatientModal')" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded shadow-sm hover:bg-gray-50 transition">
                    <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                    </svg>
                    Add Patient
                </button>
                
                <button id="editBtn" disabled onclick="openEditModal()" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded shadow-sm hover:bg-gray-50 transition opacity-50 cursor-not-allowed">
                    <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                    Edit Patient
                </button>
                
                @if(auth()->user()->role === 'Admin')
                    <button id="deleteBtn" disabled onclick="openDeleteModal()" class="flex items-center gap-2 bg-white border border-gray-300 text-red-600 px-3 py-1.5 rounded shadow-sm hover:bg-red-50 transition opacity-50 cursor-not-allowed">
                        <svg class="w-4 h-4 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        Delete Patient
                    </button>
                @endif
            </div>

            <form action="{{ route('admin.patients') }}" method="GET" class="flex gap-2 w-full md:w-auto">
                <div class="relative w-full md:w-80">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm font-medium">Search:</span>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Enter first or last name..." class="w-full border border-gray-300 rounded pl-16 pr-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-1.5 text-sm font-medium rounded shadow-sm transition">Search</button>
                @if(request('search'))
                    <a href="{{ route('admin.patients') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1.5 text-sm font-medium rounded flex items-center">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto border border-gray-200 rounded-md shadow-sm">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-700 font-semibold border-b border-gray-200 uppercase tracking-wider text-xs">
                        <th class="p-3 border-r">First Name</th>
                        <th class="p-3 border-r">Last Name</th>
                        <th class="p-3 border-r">Birth Date</th>
                        <th class="p-3 border-r">Gender</th>
                        <th class="p-3 border-r">Phone Num</th>
                        <th class="p-3 border-r">Email Address</th>
                        <th class="p-3">Home Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white text-gray-600">
                    @forelse($patients as $patient)
                        <tr onclick="selectPatient(this, {{ $patient->patient_id }})" class="hover:bg-blue-50/40 transition cursor-pointer">
                            <td class="p-3 border-r font-medium text-gray-900">{{ $patient->first_name }}</td>
                            <td class="p-3 border-r">{{ $patient->last_name }}</td>
                            <td class="p-3 border-r">{{ \Carbon\Carbon::parse($patient->birth_date)->format('d/m/Y') }}</td>
                            <td class="p-3 border-r">{{ $patient->gender }}</td>
                            <td class="p-3 border-r">{{ $patient->phone_num }}</td>
                            <td class="p-3 border-r text-blue-600 font-medium">{{ $patient->email }}</td>
                            <td class="p-3">{{ $patient->address }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-400 italic">No corresponding patient data records logged.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $patients->appends(['search' => request('search')])->links() }}
        </div>
    </div>

    <div id="addPatientModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 overflow-hidden border border-gray-100">
            <div class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold">Add New Patient Record</h3>
                <button onclick="toggleModal('addPatientModal')" class="text-white hover:text-gray-200 text-2xl font-semibold">&times;</button>
            </div>
            <form action="{{ route('admin.patients.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">First Name</label>
                        <input type="text" name="first_name" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Last Name</label>
                        <input type="text" name="last_name" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Birth Date</label>
                        <input type="date" name="birth_date" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Gender</label>
                        <select name="gender" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Phone Number</label>
                        <input type="text" name="phone_num" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Home Address</label>
                    <input type="text" name="address" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-100">
                    <button type="button" onclick="toggleModal('addPatientModal')" class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-4 py-2 text-sm font-medium rounded">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-medium rounded shadow-sm">Save Patient</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editPatientModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 overflow-hidden border border-gray-100">
            <div class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold">Edit Patient Details</h3>
                <button type="button" onclick="toggleModal('editPatientModal')" class="text-white hover:text-gray-200 text-2xl font-semibold">&times;</button>
            </div>
            <form id="editPatientForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT') 
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">First Name</label>
                        <input type="text" id="edit_first_name" name="first_name" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Last Name</label>
                        <input type="text" id="edit_last_name" name="last_name" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Birth Date</label>
                        <input type="date" id="edit_birth_date" name="birth_date" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Gender</label>
                        <select id="edit_gender" name="gender" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Phone Number</label>
                        <input type="text" id="edit_phone_num" name="phone_num" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Email Address</label>
                        <input type="email" id="edit_email" name="email" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Home Address</label>
                    <input type="text" id="edit_address" name="address" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-100">
                    <button type="button" onclick="toggleModal('editPatientModal')" class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-4 py-2 text-sm font-medium rounded">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-medium rounded shadow-sm">Update Record</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deletePatientModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 overflow-hidden border border-gray-100">
            <div class="bg-red-600 text-white px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold">Confirm Deletion</h3>
                <button type="button" onclick="toggleModal('deletePatientModal')" class="text-white hover:text-gray-200 text-2xl font-semibold">&times;</button>
            </div>
            <div class="p-6">
                <p class="text-gray-600 text-sm">Are you sure you want to permanently delete this patient record? This action cannot be undone.</p>
                <form id="deletePatientForm" method="POST" class="mt-6 flex justify-end space-x-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="toggleModal('deletePatientModal')" class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-4 py-2 text-sm font-medium rounded">Cancel</button>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 text-sm font-medium rounded shadow-sm">Delete Permanently</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let selectedPatientId = null;

        function selectPatient(row, id) {
            document.querySelectorAll('tbody tr').forEach(tr => tr.classList.remove('bg-blue-100', 'font-semibold'));
            row.classList.add('bg-blue-100', 'font-semibold');
            selectedPatientId = id;

            document.getElementById('editBtn').classList.remove('opacity-50', 'cursor-not-allowed');
            document.getElementById('editBtn').removeAttribute('disabled');
            document.getElementById('deleteBtn').classList.remove('opacity-50', 'cursor-not-allowed');
            document.getElementById('deleteBtn').removeAttribute('disabled');
        }

        function toggleModal(modalId) {
            document.getElementById(modalId).classList.toggle('hidden');
        }

        function openDeleteModal() {
            if (!selectedPatientId) return;
            document.getElementById('deletePatientForm').action = `/admin/patients/${selectedPatientId}/delete`;
            toggleModal('deletePatientModal');
        }

        function openEditModal() {
            if (!selectedPatientId) return;

            fetch(`/admin/patients/${selectedPatientId}/edit`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP Status Error: ' + response.status);
                    }
                    return response.json();
                })
                .then(patient => {
                    document.getElementById('edit_first_name').value = patient.first_name;
                    document.getElementById('edit_last_name').value = patient.last_name;
                    document.getElementById('edit_birth_date').value = patient.birth_date;
                    document.getElementById('edit_gender').value = patient.gender;
                    document.getElementById('edit_phone_num').value = patient.phone_num;
                    document.getElementById('edit_email').value = patient.email;
                    document.getElementById('edit_address').value = patient.address;

                    document.getElementById('editPatientForm').action = `/admin/patients/${selectedPatientId}/update`;
                    toggleModal('editPatientModal');
                })
                .catch(error => {
                    console.error(error);
                    alert('System Data Fetch Error.');
                });
        }
    </script>
@endsection