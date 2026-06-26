@extends('layouts.app')

@section('content')
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 font-sans">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Users Management</h2>

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
                <button onclick="toggleModal('addUserModal')" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded shadow-sm hover:bg-gray-50 transition">
                    <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                    </svg>
                    Add User
                </button>
                <button id="editBtn" disabled onclick="openEditModal()" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded shadow-sm hover:bg-gray-50 transition opacity-50 cursor-not-allowed">
                    <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                    Edit User
                </button>
                <button id="deleteBtn" disabled onclick="openDeleteModal()" class="flex items-center gap-2 bg-white border border-gray-300 text-red-600 px-3 py-1.5 rounded shadow-sm hover:bg-red-50 transition opacity-50 cursor-not-allowed">
                    <svg class="w-4 h-4 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                    Delete User
                </button>
            </div>

            <form action="{{ route('admin.users') }}" method="GET" class="flex gap-2 w-full md:w-auto">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search username, role, email..." class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full md:w-64">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 text-sm font-medium rounded shadow-sm transition">Search</button>
            </form>
        </div>

        <div class="overflow-x-auto border border-gray-200 rounded-md shadow-sm">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-700 font-semibold border-b border-gray-200 uppercase text-xs">
                        <th class="p-3 border-r">Username</th>
                        <th class="p-3 border-r">System Role</th>
                        <th class="p-3 border-r">Email Address</th>
                        <th class="p-3">Connected Practitioner</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white text-gray-600">
                    @forelse($users as $user)
                        <tr onclick="selectUser(this, {{ $user->user_id }})" class="hover:bg-blue-50/40 transition cursor-pointer">
                            <td class="p-3 border-r font-semibold text-gray-900">{{ $user->username }}</td>
                            <td class="p-3 border-r">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                    {{ $user->role === 'Admin' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $user->role === 'Receptionist' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $user->role === 'Doctor' ? 'bg-green-100 text-green-800' : '' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="p-3 border-r font-medium text-gray-600">{{ $user->email ?? 'N/A' }}</td>
                            <td class="p-3">
                                @if($user->role === 'Doctor' && $user->doctor)
                                    Dr. {{ $user->doctor->first_name }} {{ $user->doctor->last_name }}
                                @else
                                    <span class="text-gray-400 italic">None</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-gray-400 italic">No corresponding system accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->appends(['search' => request('search')])->links() }}
        </div>
    </div>

    <div id="addUserModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 border border-gray-100 overflow-hidden">
            <div class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold">Create User Account</h3>
                <button onclick="toggleModal('addUserModal')" class="text-white text-2xl font-semibold">&times;</button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Username</label>
                        <input type="text" name="username" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Role</label>
                        <select id="add_role" name="role" onchange="toggleDoctorDropdown('add')" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:outline-none">
                            <option value="Admin">Admin</option>
                            <option value="Receptionist">Receptionist</option>
                            <option value="Doctor">Doctor</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Email (Optional)</label>
                        <input type="email" name="email" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none">
                    </div>
                </div>
                <div id="add_doctor_wrapper" class="hidden">
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Link to Doctor Profile</label>
                    <select name="doctor_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:outline-none">
                        <option value="">-- Choose Profile Reference --</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->doctor_id }}">Dr. {{ $doc->first_name }} {{ $doc->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-100">
                    <button type="button" onclick="toggleModal('addUserModal')" class="bg-gray-100 text-gray-700 px-4 py-2 text-sm font-medium rounded">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-medium rounded shadow-sm">Save User</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editUserModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 border border-gray-100 overflow-hidden">
            <div class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold">Modify Account Parameters</h3>
                <button type="button" onclick="toggleModal('editUserModal')" class="text-white text-2xl font-semibold">&times;</button>
            </div>
            <form id="editUserForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Username</label>
                        <input type="text" id="edit_username" name="username" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">New Password (Optional)</label>
                        <input type="password" name="password" placeholder="Leave blank to keep..." class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Role</label>
                        <select id="edit_role" name="role" onchange="toggleDoctorDropdown('edit')" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:outline-none">
                            <option value="Admin">Admin</option>
                            <option value="Receptionist">Receptionist</option>
                            <option value="Doctor">Doctor</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Email Address</label>
                        <input type="email" id="edit_email" name="email" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none">
                    </div>
                </div>
                <div id="edit_doctor_wrapper" class="hidden">
                    <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Link to Doctor Profile</label>
                    <select id="edit_doctor_id" name="doctor_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:outline-none">
                        <option value="">-- Choose Profile Reference --</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->doctor_id }}">Dr. {{ $doc->first_name }} {{ $doc->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-100">
                    <button type="button" onclick="toggleModal('editUserModal')" class="bg-gray-100 text-gray-700 px-4 py-2 text-sm font-medium rounded">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-medium rounded shadow-sm">Update User</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteUserModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 border border-gray-100 overflow-hidden">
            <div class="bg-red-600 text-white px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold">Confirm Account Removal</h3>
                <button type="button" onclick="toggleModal('deleteUserModal')" class="text-white text-2xl font-semibold">&times;</button>
            </div>
            <div class="p-6 text-sm text-gray-600">
                Are you sure you want to permanently delete this user? This action cannot be undone.
                <form id="deleteUserForm" method="POST" class="mt-6 flex justify-end space-x-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="toggleModal('deleteUserModal')" class="bg-gray-100 text-gray-700 px-4 py-2 text-sm font-medium rounded">Cancel</button>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 text-sm font-medium rounded shadow-sm">Delete Permanently</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let selectedUserId = null;

        function selectUser(row, id) {
            document.querySelectorAll('tbody tr').forEach(tr => tr.classList.remove('bg-blue-100', 'font-semibold'));
            row.classList.add('bg-blue-100', 'font-semibold');
            selectedUserId = id;

            document.getElementById('editBtn').classList.remove('opacity-50', 'cursor-not-allowed');
            document.getElementById('editBtn').removeAttribute('disabled');
            document.getElementById('deleteBtn').classList.remove('opacity-50', 'cursor-not-allowed');
            document.getElementById('deleteBtn').removeAttribute('disabled');
        }

        function toggleModal(modalId) {
            document.getElementById(modalId).classList.toggle('hidden');
        }

        function toggleDoctorDropdown(mode) {
            const roleSelect = document.getElementById(`${mode}_role`).value;
            const wrapper = document.getElementById(`${mode}_doctor_wrapper`);
            if (roleSelect === 'Doctor') {
                wrapper.classList.remove('hidden');
            } else {
                wrapper.classList.add('hidden');
            }
        }

        function openDeleteModal() {
            if (!selectedUserId) return;
            document.getElementById('deleteUserForm').action = `/admin/users/${selectedUserId}/delete`;
            toggleModal('deleteUserModal');
        }

        function openEditModal() {
            if (!selectedUserId) return;

            fetch(`/admin/users/${selectedUserId}/edit`)
                .then(response => response.json())
                .then(user => {
                    document.getElementById('edit_username').value = user.username;
                    document.getElementById('edit_email').value = user.email ?? '';
                    document.getElementById('edit_role').value = user.role;
                    
                    // If user being edited is already a Doctor, lock/hide the fields
                    if(user.role === 'Doctor') {
                        document.getElementById('edit_role').disabled = true;
                        document.getElementById('edit_role').classList.add('bg-gray-100', 'cursor-not-allowed');
                        document.getElementById('edit_doctor_wrapper').classList.add('hidden');
                    } else {
                        document.getElementById('edit_role').disabled = false;
                        document.getElementById('edit_role').classList.remove('bg-gray-100', 'cursor-not-allowed');
                        toggleDoctorDropdown('edit');
                    }

                    document.getElementById('editUserForm').action = `/admin/users/${selectedUserId}/update`;
                    toggleModal('editUserModal');
                });
        }
    </script>
@endsection