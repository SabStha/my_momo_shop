<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Branch Data Check ===\n\n";

// Check all branches
echo "All Branches:\n";
$branches = \App\Models\Branch::all(['id', 'name', 'is_main']);
foreach ($branches as $branch) {
    echo "- Branch {$branch->id}: {$branch->name} (is_main: " . ($branch->is_main ? 'Yes' : 'No') . ")\n";
}

echo "\n=== Session Data ===\n";
echo "Selected branch in session: " . (session('selected_branch') ? session('selected_branch')->name : 'None') . "\n";

if (session('selected_branch')) {
    $selectedBranch = session('selected_branch');
    echo "Selected branch ID: {$selectedBranch->id}\n";
    echo "Selected branch is_main: " . ($selectedBranch->is_main ? 'Yes' : 'No') . "\n";
    
    // Check if the selected branch matches the database
    $dbBranch = \App\Models\Branch::find($selectedBranch->id);
    if ($dbBranch) {
        echo "Database branch is_main: " . ($dbBranch->is_main ? 'Yes' : 'No') . "\n";
        echo "Match: " . ($selectedBranch->is_main === $dbBranch->is_main ? 'Yes' : 'No') . "\n";
    }
}

echo "\n=== Main Branch Check ===\n";
$mainBranch = \App\Models\Branch::where('is_main', true)->first();
if ($mainBranch) {
    echo "Main branch found: {$mainBranch->name} (ID: {$mainBranch->id})\n";
} else {
    echo "No main branch found!\n";
}

echo "\n=== Test Complete ===\n"; 