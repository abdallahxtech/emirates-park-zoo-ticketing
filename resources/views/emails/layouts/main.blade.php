<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f6f6f6; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; margin-top: 20px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .header { background-color: #ffffff; padding: 20px; text-align: center; border-bottom: 3px solid #4CAF50; }
        .header img { max-height: 80px; }
        .content { padding: 30px; }
        .footer { background-color: #f6f6f6; padding: 20px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; }
        .btn { display: inline-block; padding: 12px 24px; background-color: #4CAF50; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold; margin: 10px 0; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th { text-align: left; padding: 10px; border-bottom: 2px solid #eee; color: #666; font-size: 14px; }
        .table td { padding: 10px; border-bottom: 1px solid #eee; }
        .total-row td { font-weight: bold; font-size: 16px; border-top: 2px solid #333; border-bottom: none; }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; text-align: center; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .vip-box { background-color: #fff8e1; border: 1px solid #ffe0b2; padding: 15px; border-radius: 4px; margin-top: 20px; }
        .vip-title { color: #f57c00; font-weight: bold; text-transform: uppercase; font-size: 14px; margin-bottom: 10px; }
        .text-center { text-align: center; }
        .small { font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(config('app.logo_url'))
                <img src="{{ config('app.logo_url') }}" alt="{{ config('app.name') }}">
            @else
                <h1>{{ config('app.name', 'Emirates Park Zoo') }}</h1>
            @endif
        </div>

        <div class="content">
            @yield('content')
        </div>

        <div class="footer">
            <p>{{ config('app.name') }} &middot; Abu Dhabi, UAE</p>
            <p>
                <a href="{{ url('/') }}">Website</a> | 
                <a href="tel:{{ config('app.support_phone') }}">Support: {{ config('app.support_phone') }}</a>
            </p>
            <p class="small">
                This email was sent to {{ $customer_email ?? 'you' }} regarding booking #{{ $booking_reference ?? 'N/A' }}.
                <br>
                <a href="{{ url('/privacy') }}">Privacy Policy</a> | <a href="{{ url('/terms') }}">Terms</a>
            </p>
        </div>
    </div>
</body>
</html>
