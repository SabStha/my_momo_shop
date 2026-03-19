<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=database4', 'root', '');

echo "=== ama_credit_transactions ===\n";
$count = $pdo->query("SELECT COUNT(*) FROM ama_credit_transactions")->fetchColumn();
echo "Total rows: $count\n\n";

if ($count > 0) {
    // Type breakdown
    echo "By type:\n";
    foreach($pdo->query("SELECT type, COUNT(*) as cnt FROM ama_credit_transactions GROUP BY type") as $r) {
        echo "  {$r['type']}: {$r['cnt']}\n";
    }
    // Date range
    $range = $pdo->query("SELECT MIN(created_at) as earliest, MAX(created_at) as latest FROM ama_credit_transactions")->fetch(PDO::FETCH_ASSOC);
    echo "\nDate range: {$range['earliest']} → {$range['latest']}\n";
    // Sample
    echo "\nSample rows:\n";
    foreach($pdo->query("SELECT id, user_id, type, amount, source, description, created_at FROM ama_credit_transactions LIMIT 5") as $r) {
        echo "  " . implode(' | ', array_values($r)) . "\n";
    }
} else {
    echo "(no rows)\n";
}

echo "\n=== ama_credits row count ===\n";
echo $pdo->query("SELECT COUNT(*) FROM ama_credits")->fetchColumn() . " rows\n";
