<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            background-color: #f8f9fa;
        }
        .content {
            padding: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px 0;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Our Campaign</h1>
        </div>
        
        <div class="content">
            {!! $content !!}
            
            @if(isset($user))
                <p>Dear {{ $user->name }},</p>
                
                <p>We noticed that you haven't placed an order in a while. Here's a quick summary of your account:</p>
                
                <ul>
                    <li>Last Order Date: {{ $user->last_order_date }}</li>
                    <li>Total Orders: {{ $user->total_orders }}</li>
                    <li>Total Spent: ${{ $user->total_spent }}</li>
                </ul>
                
                <p>We'd love to have you back! Check out our latest products and special offers.</p>
                
                <a href="{{ route('shop.index') }}" class="button">Shop Now</a>
            @endif
        </div>
        
        <div class="footer">
            <p>This email was sent to {{ $user->email }}. If you no longer wish to receive these emails, you can unsubscribe here.</p>
            <p>&copy; {{ date('Y') }} Your Company Name. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 