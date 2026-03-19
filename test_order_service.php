<?php

use App\Services\OrderService;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$orderService = new OrderService();

$num1 = $orderService->generateOrderNumber();
$num2 = $orderService->generateOrderNumber();

echo "Order Number 1: $num1\n";
echo "Order Number 2: $num2\n";

if ($num1 !== $num2) {
    echo "SUCCESS: Order numbers are unique.\n";
} else {
    echo "FAILURE: Order numbers are NOT unique.\n";
}
