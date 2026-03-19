<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\InventoryOrder;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\InventoryCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InventoryService
{
    /**
     * Adjust stock for an inventory item
     *
     * @param InventoryItem $item
     * @param int $quantity
     * @param string $type 'add' or 'subtract'
     * @param string|null $reason
     * @param string|null $notes
     * @return array
     * @throws \Exception
     */
    public function adjustStock(InventoryItem $item, int $quantity, string $type, ?string $reason = null, ?string $notes = null): array
    {
        return DB::transaction(function () use ($item, $quantity, $type, $reason, $notes) {
            $oldStock = $item->current_stock;
            $newStock = $type === 'add' 
                ? $oldStock + $quantity 
                : $oldStock - $quantity;

            if ($newStock < 0) {
                throw new \Exception('Stock cannot be negative');
            }

            $item->update(['current_stock' => $newStock]);

            InventoryTransaction::create([
                'inventory_item_id' => $item->id,
                'type' => $type,
                'quantity' => $quantity,
                'previous_stock' => $oldStock,
                'new_stock' => $newStock,
                'reason' => $reason,
                'notes' => $notes,
                'user_id' => auth()->id()
            ]);

            ActivityLogService::logInventoryActivity(
                'adjustment',
                "Stock adjusted for {$item->name} from {$oldStock} to {$newStock}",
                ['item_id' => $item->id, 'sku' => $item->code]
            );

            return [
                'success' => true,
                'new_stock' => $newStock,
                'old_stock' => $oldStock
            ];
        });
    }

    /**
     * Bulk create supply orders for locked items
     *
     * @return int Number of orders created
     */
    public function orderLockedItems(): int
    {
        $lockedItems = InventoryItem::where('is_locked', true)->get();
        if ($lockedItems->isEmpty()) {
            return 0;
        }

        $supplierGroups = $lockedItems->groupBy('supplier_id');
        $ordersCount = 0;

        foreach ($supplierGroups as $supplierId => $items) {
            if (!$supplierId) continue;

            $order = new InventoryOrder();
            $order->supplier_id = $supplierId;
            $order->order_number = $order->generateOrderNumber();
            $order->status = 'pending';
            $order->ordered_at = now();
            $order->total_amount = 0;
            $order->save();
            
            $total = 0;
            foreach ($items as $item) {
                $qty = $item->reorder_point > 0 ? $item->reorder_point : 1;
                $itemTotal = $qty * $item->unit_price;
                
                $order->items()->create([
                    'inventory_item_id' => $item->id,
                    'quantity' => $qty,
                    'unit_price' => $item->unit_price,
                    'total_price' => $itemTotal
                ]);
                
                $total += $itemTotal;
                $item->update(['is_locked' => false]);
            }
            
            $order->update(['total_amount' => $total]);
            $ordersCount++;
        }

        return $ordersCount;
    }

    /**
     * Get data for inventory export
     *
     * @param string $exportType 'all', 'low_stock', or 'category'
     * @param int|null $categoryId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getExportData(string $exportType, ?int $categoryId = null)
    {
        $query = InventoryItem::with(['category', 'supplier', 'branch']);

        switch ($exportType) {
            case 'low_stock':
                $query->whereRaw('current_stock <= reorder_point');
                break;
            case 'category':
                if ($categoryId) {
                    $query->where('category_id', $categoryId);
                }
                break;
        }

        return $query->get();
    }

    /**
     * Process inventory CSV import
     *
     * @param string $filePath
     * @param bool $updateExisting
     * @return array
     */
    public function processImport(string $filePath, bool $updateExisting = false): array
    {
        $importedCount = 0;
        $updatedCount = 0;
        $errors = [];

        if (($handle = fopen($filePath, "r")) !== FALSE) {
            // Skip header row
            fgetcsv($handle);
            
            while (($data = fgetcsv($handle)) !== FALSE) {
                if (count($data) >= 6) {
                    $sku = $data[0];
                    $name = $data[1];
                    $categoryName = $data[2];
                    $supplierName = $data[3];
                    $currentStock = $data[4];
                    $unitPrice = $data[5];
                    
                    // Find or create category
                    $category = InventoryCategory::firstOrCreate(
                        ['name' => $categoryName],
                        ['slug' => Str::slug($categoryName)]
                    );
                    
                    // Find or create supplier
                    $supplier = null;
                    if ($supplierName && $supplierName !== 'N/A') {
                        $mainBranch = Branch::where('is_main', true)->first();
                        
                        if (!$mainBranch) {
                            $mainBranch = Branch::create([
                                'name' => 'Main Branch',
                                'code' => 'MB001',
                                'address' => 'Main Branch Address',
                                'contact_person' => 'Main Branch Contact',
                                'email' => 'main@momoshop.com',
                                'phone' => '1234567890',
                                'is_active' => true,
                                'is_main' => true
                            ]);
                        }
                        
                        $supplier = Supplier::firstOrCreate(
                            ['name' => $supplierName],
                            [
                                'code' => Str::random(8),
                                'branch_id' => $mainBranch->id
                            ]
                        );
                    }
                    
                    $itemData = [
                        'name' => $name,
                        'code' => $sku,
                        'category_id' => $category->id,
                        'supplier_id' => $supplier ? $supplier->id : null,
                        'current_stock' => $currentStock,
                        'unit_price' => $unitPrice,
                        'unit' => 'pcs',
                        'reorder_point' => 10,
                        'status' => 'active'
                    ];
                    
                    $existingItem = InventoryItem::where('code', $sku)->first();
                    
                    if ($existingItem && $updateExisting) {
                        $existingItem->update($itemData);
                        $updatedCount++;
                    } elseif (!$existingItem) {
                        InventoryItem::create($itemData);
                        $importedCount++;
                    } else {
                        $errors[] = "SKU {$sku} already exists and update_existing is false";
                    }
                }
            }
            fclose($handle);
        }

        return [
            'imported' => $importedCount,
            'updated' => $updatedCount,
            'errors' => $errors
        ];
    }
}
