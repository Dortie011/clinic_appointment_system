<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Appointment System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

    <header class="bg-[#4a90e2] text-white px-6 py-4 flex justify-between items-center shadow-md">
        <h1 class="text-xl font-bold tracking-wide">Clinic Appointment System</h1>
        <div class="flex items-center space-x-4">
            @auth
                <span>Logged in as: <strong class="font-semibold">{{ auth()->user()->username }} ({{ auth()->user()->role }})</strong></span>
            @else
                <span>Logged in as: <strong class="font-semibold">Guest</strong></span>
            @endauth
            
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-white text-gray-800 px-4 py-1 rounded shadow hover:bg-gray-100 text-sm font-medium">Logout</button>
            </form>
        </div>
    </header>

    <nav class="bg-white border-b border-gray-200 px-6 py-2 flex space-x-1">
        @php $route = Route::currentRouteName(); @endphp
        
        @if(in_array(auth()->user()->role, ['Admin', 'Receptionist', 'Doctor']))
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 text-sm font-medium rounded-t {{ $route == 'admin.dashboard' ? 'bg-gray-100 border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 hover:text-blue-500' }}">Dashboard</a>
        @endif

        @if(in_array(auth()->user()->role, ['Admin', 'Receptionist']))
            <a href="{{ route('admin.patients') }}" class="px-4 py-2 text-sm font-medium rounded-t {{ $route == 'admin.patients' ? 'bg-gray-100 border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 hover:text-blue-500' }}">Patients</a>
        @endif

        @if(auth()->user()->role === 'Admin')
            <a href="{{ route('admin.doctors') }}" class="px-4 py-2 text-sm font-medium rounded-t {{ $route == 'admin.doctors' ? 'bg-gray-100 border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 hover:text-blue-500' }}">Doctors</a>
        @endif

        @if(in_array(auth()->user()->role, ['Admin', 'Receptionist', 'Doctor']))
            <a href="{{ route('admin.schedules') }}" class="px-4 py-2 text-sm font-medium rounded-t {{ $route == 'admin.schedules' ? 'bg-gray-100 border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 hover:text-blue-500' }}">Schedules</a>
        @endif

        @if(in_array(auth()->user()->role, ['Admin', 'Receptionist', 'Doctor']))
            <a href="{{ route('admin.appointments') }}" class="px-4 py-2 text-sm font-medium rounded-t {{ $route == 'admin.appointments' ? 'bg-gray-100 border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 hover:text-blue-500' }}">Appointments</a>
        @endif
        
        @if(auth()->user()->role === 'Admin')
            <a href="{{ route('admin.users') }}" class="px-4 py-2 text-sm font-medium rounded-t {{ $route == 'admin.users' ? 'bg-gray-100 border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 hover:text-blue-500' }}">Users</a>
        @endif
    </nav>

    <main class="p-6">
        @if($errors->has('unauthorized'))
            <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-800 p-4 rounded shadow-sm mb-6 text-sm font-medium">
                {{ $errors->first('unauthorized') }}
            </div>
        @endif

        @yield('content')
    </main>

</body>
</html>