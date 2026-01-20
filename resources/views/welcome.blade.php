<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Emirates Park Zoo - Ticketing API</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            text-align: center;
            background: white;
            padding: 3rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            max-width: 400px;
        }

        .logo {
            font-size: 2rem;
            font-weight: 700;
            color: #00A651;
            /* Zoo Green */
            margin-bottom: 0.5rem;
        }

        .subtitle {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 2rem;
        }

        .status {
            display: inline-flex;
            align-items: center;
            background: #ecfdf5;
            color: #047857;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .dot {
            height: 8px;
            width: 8px;
            background-color: #10b981;
            border-radius: 50%;
            margin-right: 0.5rem;
        }

        .links {
            margin-top: 2rem;
        }

        .btn {
            display: inline-block;
            background-color: #00A651;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.2s;
        }

        .btn:hover {
            background-color: #008c44;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">Emirates Park Zoo</div>
        <div class="subtitle">Ticketing & VIP Experience Platform</div>

        <div class="status">
            <span class="dot"></span> All Systems Operational
        </div>

        <div class="links">
            <a href="/admin" class="btn">Login to Admin Portal</a>
        </div>

        <p style="margin-top: 2rem; font-size: 0.75rem; color: #94a3b8;">
            API Version 1.0 • Laravel v{{ Illuminate\Foundation\Application::VERSION }}
        </p>
    </div>
</body>

</html>