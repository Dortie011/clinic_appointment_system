<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Clinic Appointment System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center font-sans">

    <div class="w-full max-w-md bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
        <div class="bg-[#4a90e2] text-white p-6 text-center">
            <h2 class="text-2xl font-bold">Clinic Appointment System</h2>
            <p class="text-blue-100 text-sm mt-1">Please sign in to access the dashboard</p>
        </div>

        <form action="{{ route('login.store') }}" method="POST" class="p-6 space-y-4">
            @csrf

            @if($errors->any())
                <div class="bg-red-50 border border-red-300 text-red-700 px-3 py-2 rounded text-sm font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Username</label>
                <input type="text" name="username" value="{{ old('username') }}" required autofocus
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded shadow transition duration-150">
                    Login
                </button>
            </div>
        </form>
    </div>

</body>
</html>