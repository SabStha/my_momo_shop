<!DOCTYPE html>
<html>
<head>
    <title>Test Page</title>
</head>
<body>
    <h1>Test Page</h1>
    <p>This is a test page to check if blade processing works.</p>
    <p>Product ID: {{ $product->id }}</p>
    <p>Product Name: {{ $product->name }}</p>
    <p>Current Time: {{ now() }}</p>
</body>
</html> 