<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status - Laundry App</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --success: #10b981;
            --error: #ef4444;
            --background: #f8fafc;
            --card: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--background);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 400px;
        }

        .card {
            background: var(--card);
            border-radius: 24px;
            padding: 40px 32px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .icon-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .success .icon-wrapper {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .error .icon-wrapper {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--error);
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        p {
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 32px;
        }

        .details {
            background-color: #f1f5f9;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 32px;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .detail-row:last-child {
            margin-bottom: 0;
            padding-top: 12px;
            border-top: 1px dashed #cbd5e1;
            font-weight: 700;
        }

        .label {
            color: var(--text-muted);
        }

        .value {
            font-weight: 600;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
        }
    </style>
</head>
<body>
    <div class="container {{ $success ? 'success' : 'error' }}">
        <div class="card">
            <div class="icon-wrapper">
                @if($success)
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                @endif
            </div>

            <h1>{{ $success ? 'Payment Successful' : 'Payment Failed' }}</h1>
            <p>{{ $message }}</p>

            @if($order)
                <div class="details">
                    <div class="detail-row">
                        <span class="label">Order ID</span>
                        <span class="value">#{{ $order->id }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Date</span>
                        <span class="value">{{ $order->paid_at ? $order->paid_at->format('d M Y, H:i') : now()->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Total Amount</span>
                        <span class="value">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endif

            <a href="#" class="btn btn-primary" onclick="window.close()">Close Page</a>
        </div>
    </div>
</body>
</html>
