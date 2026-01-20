<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'System Maintenance' }} - Emirates Park Zoo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-[#00A651] p-6 text-center">
            <h1 class="text-white text-xl font-bold">Emirates Park Zoo</h1>
            <p class="text-green-100 text-sm mt-1">System Administration</p>
        </div>

        <!-- Content -->
        <div class="p-8">
            <div class="flex justify-center mb-6">
                @if($status === 'success')
                <div class="h-16 w-16 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                @else
                <div class="h-16 w-16 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                @endif
            </div>

            <h2 class="text-center text-2xl font-bold text-gray-900 mb-2">{{ $title }}</h2>

            <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-100 text-sm text-gray-600 font-mono overflow-x-auto whitespace-pre-wrap">
                {!! $output !!}
            </div>

            <a href="/admin" class="block w-full bg-[#00A651] hover:bg-[#008c44] text-white font-semibold py-3 px-4 rounded-lg text-center transition-colors duration-200 shadow-md">
                Return to Dashboard
            </a>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 text-center">
            <p class="text-xs text-gray-400">Emirates Park Zoo & Resort • Ticketing Platform v1.0</p>
        </div>
    </div>
</body>

</html>