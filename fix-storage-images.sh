#!/bin/bash

# Fix all blade files that use asset('storage/' . $product->image) without null check
# Replace with conditional check to prevent /storage/ directory loading

echo "Fixing blade image references..."

# Find and replace in all blade files
find resources/views -name "*.blade.php" -type f -exec sed -i \
  's/asset('\''storage\/'\'' \. \$product->image)/\$product->image ? asset('\''storage\/'\'' . \$product->image) : asset('\''images\/no-image.svg'\'')/' {} \;

find resources/views -name "*.blade.php" -type f -exec sed -i \
  's/asset("storage\/" \. \$product->image)/\$product->image ? asset("storage\/" . \$product->image) : asset("images\/no-image.svg")/' {} \;

# Also fix $item->product->image cases
find resources/views -name "*.blade.php" -type f -exec sed -i \
  's/asset('\''storage\/'\'' \. \$item->product->image)/\$item->product && \$item->product->image ? asset('\''storage\/'\'' . \$item->product->image) : asset('\''images\/no-image.svg'\'')/' {} \;

echo "✅ Fixed all blade image references"

