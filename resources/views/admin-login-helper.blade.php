<!DOCTYPE html>
<html>
<head>
    <title>Admin Login Helper</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 600px; margin: 0 auto; }
        .info { background: #f0f0f0; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .form { background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        input, button { padding: 10px; margin: 5px; width: 100%; }
        button { background: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Login Helper</h1>
        
        <div class="info">
            <h3>Admin Credentials:</h3>
            <p><strong>Email:</strong> {{ $admin_email }}</p>
            <p><strong>Password:</strong> {{ $admin_password }}</p>
        </div>
        
        <div class="form">
            <h3>Login Form:</h3>
            <form method="POST" action="/login">
                @csrf
                <input type="email" name="email" value="{{ $admin_email }}" placeholder="Email" required>
                <input type="password" name="password" value="{{ $admin_password }}" placeholder="Password" required>
                <button type="submit">Login as Admin</button>
            </form>
        </div>
        
        <div class="info">
            <h3>After Login:</h3>
            <p>Once logged in, you can access:</p>
            <ul>
                <li><a href="/admin/badges" target="_blank">Admin Badges</a></li>
                <li><a href="/test-admin" target="_blank">Test Admin Access</a></li>
            </ul>
        </div>
    </div>
</body>
</html> 