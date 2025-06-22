<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invalid Link - Momo Shop</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .error-icon {
            font-size: 64px;
            color: #dc2626;
            margin-bottom: 20px;
        }
        .error-message {
            background-color: #fef2f2;
            border: 1px solid #dc2626;
            color: #991b1b;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e3e3e3;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">⚠️</div>
        
        <h1 style="color: #dc2626; margin-bottom: 10px;">Invalid Link</h1>
        
        <div class="error-message">
            <strong>{{ $message }}</strong>
        </div>

        <p>This link may have expired or is invalid. Please contact the branch directly for assistance.</p>

        <div class="footer">
            <p><strong>Momo Shop Inventory Management System</strong></p>
            <p>For support, please contact the main branch.</p>
        </div>
    </div>
</body>
</html> 