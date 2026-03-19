<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\Branch;
use App\Models\Category;
use App\Models\InventoryOrder;
use App\Models\InventoryTransaction;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\ActivityLogService;

class InventoryCRUDController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = null;
        
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
        }

        $query = InventoryItem::query();
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        } else {
            $query->whereNull('branch_id')
                  ->orWhereHas('branch', function($q) {
                      $q->where('is_main', true);
                  });
        }

        $items = $query->with(['category', 'supplier', 'branch'])
            ->orderBy('name')
            ->paginate(10);

        $categories = Category::orderBy('name')->get();
        
        $mainBranch = Branch::where('is_main', true)->first();
        $suppliers = collect();
        if ($mainBranch) {
            $mainBranchSupplier = new \stdClass();
            $mainBranchSupplier->id = $mainBranch->id;
            $mainBranchSupplier->name = 'Main Branch';
            $suppliers->push($mainBranchSupplier);
        }
        
        $lowStockCount = $query->whereRaw('current_stock <= reorder_point')->count();
        $branches = Branch::orderBy('name')->get();
        
        $orders = InventoryOrder::when($branchId, function($query) use ($branchId) {
            return $query->where('branch_id', $branchId);
        })->with(['items', 'branch', 'supplier'])
          ->orderBy('created_at', 'desc')
          ->paginate(10);
        
        return view('admin.inventory.index', compact('items', 'categories', 'lowStockCount', 'branches', 'branch', 'orders'));
    }

    public function create(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = null;
        
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
        }

        $query = InventoryItem::query();
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        } else {
            $query->whereNull('branch_id')
                  ->orWhereHas('branch', function($q) {
                      $q->where('is_main', true);
                  });
        }

        $items = $query->with(['category', 'supplier', 'branch'])
            ->orderBy('name')
            ->paginate(10);

        $categories = Category::orderBy('name')->get();
        
        $mainBranch = Branch::where('is_main', true)->first();
        $suppliers = collect();
        
        if ($mainBranch) {
            $mainBranchSupplier = new \stdClass();
            $mainBranchSupplier->id = $mainBranch->id;
            $mainBranchSupplier->name = 'Main Branch';
            $suppliers->push($mainBranchSupplier);
        }
        
        $lowStockCount = $query->whereRaw('current_stock <= reorder_point')->count();
        $branches = Branch::orderBy('name')->get();
        
        $orders = InventoryOrder::when($branchId, function($query) use ($branchId) {
            return $query->where('branch_id', $branchId);
        })->with('supplier')->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.inventory.create', compact('items', 'categories', 'suppliers', 'branch', 'lowStockCount', 'branches', 'orders'));
    }

    public function store(Request $request)
    {
        try {
            $branchId = $request->input('branch_id');
            $branch = null;
            
            if ($branchId) {
                $branch = Branch::findOrFail($branchId);
            } else {
                $branch = Branch::find(session('selected_branch_id'));
                if (!$branch) {
                    $mainBranch = Branch::where('is_main', true)->first();
                    if (!$mainBranch) {
                        return redirect()->route('admin.branches.index')
                            ->with('error', 'Main branch not found. Please set up a main branch first.');
                    }
                    $branch = $mainBranch;
                }
                $branchId = $branch->id;
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:inventory_items',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'unit' => 'required|string|max:50',
                'unit_price' => 'required|numeric|min:0',
                'reorder_point' => 'required|numeric|min:0',
                'current_stock' => 'required|numeric|min:0',
                'supplier_id' => 'nullable|exists:suppliers,id'
            ]);

            $validated['branch_id'] = $branchId;
            $mainBranch = Branch::where('is_main', true)->first();

            if ($validated['supplier_id'] && $mainBranch && $validated['supplier_id'] == $mainBranch->id) {
                $defaultSupplier = Supplier::where('branch_id', $mainBranch->id)->first();
                if ($defaultSupplier) {
                    $validated['supplier_id'] = $defaultSupplier->id;
                } else {
                    $defaultSupplier = Supplier::create([
                        'name' => 'Default Supplier',
                        'code' => Str::random(8),
                        'contact_person' => 'Main Branch',
                        'email' => 'main@momoshop.com',
                        'phone' => '1234567890',
                        'address' => 'Main Branch Address',
                        'branch_id' => $mainBranch->id
                    ]);
                    $validated['supplier_id'] = $defaultSupplier->id;
                }
            }

            DB::beginTransaction();
            $validated['status'] = 'active';
            $item = InventoryItem::create($validated);

            if ($validated['current_stock'] > 0) {
                InventoryTransaction::create([
                    'inventory_item_id' => $item->id,
                    'type' => 'purchase',
                    'quantity' => $validated['current_stock'],
                    'unit_price' => $validated['unit_price'],
                    'total_amount' => $validated['current_stock'] * $validated['unit_price'],
                    'notes' => 'Initial stock',
                    'user_id' => auth()->id(),
                ]);
            }

            ActivityLogService::logInventoryActivity(
                'create',
                'Created inventory item: ' . $item->name,
                [
                    'item_id' => $item->id,
                    'sku' => $item->code,
                    'initial_stock' => $validated['current_stock'],
                    'branch_id' => $branchId
                ]
            );

            DB::commit();

            return redirect()
                ->route('admin.inventory.index', ['branch' => $branchId])
                ->with('success', 'Inventory item created successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating inventory item: ' . $e->getMessage());
            return back()
                ->with('error', 'Error creating inventory item. Please try again.')
                ->withInput();
        }
    }

    public function show(InventoryItem $item)
    {
        $transactions = $item->transactions()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.inventory.show', compact('item', 'transactions'));
    }

    public function edit(InventoryItem $item)
    {
        $categories = Category::all();
        $suppliers = Supplier::orderBy('name')->get();
        return view('admin.inventory.edit', compact('item', 'categories', 'suppliers'));
    }

    public function update(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:inventory_items,code,' . $item->id,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'unit_price' => 'required|numeric|min:0',
            'reorder_point' => 'required|numeric|min:0',
            'current_stock' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id'
        ]);

        try {
            $oldData = $item->toArray();
            $item->update($validated);

            ActivityLogService::logInventoryActivity(
                'update',
                'Updated inventory item: ' . $item->name,
                [
                    'item_id' => $item->id,
                    'old_data' => $oldData,
                    'new_data' => $validated
                ]
            );

            return redirect()
                ->route('admin.inventory.edit', $item)
                ->with('success', 'Inventory item updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating inventory item: ' . $e->getMessage());
            return back()->with('error', 'Error updating inventory item. Please try again.');
        }
    }

    public function destroy(InventoryItem $item)
    {
        try {
            $itemData = $item->toArray();
            $item->delete();

            ActivityLogService::logInventoryActivity(
                'delete',
                'Deleted inventory item: ' . $itemData['name'],
                [
                    'item_id' => $itemData['id'],
                    'sku' => $itemData['code'],
                    'final_stock' => $itemData['current_stock']
                ]
            );

            return redirect()->route('admin.inventory.index')
                ->with('success', 'Inventory item deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting inventory item: ' . $e->getMessage());
            return back()->with('error', 'Error deleting inventory item. Please try again.');
        }
    }

    public function categories(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = null;
        
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
        }

        $query = InventoryCategory::query();
        
        if ($branchId) {
            $query->withCount(['items' => function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            }]);
        } else {
            $query->withCount('items');
        }

        $categories = $query->orderBy('name')->get();

        return view('admin.inventory.categories', compact('categories', 'branch'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:inventory_categories',
            'description' => 'nullable|string',
        ]);

        try {
            $category = InventoryCategory::create([
                ...$validated,
                'is_active' => true
            ]);

            $branchId = $request->query('branch') ?? session('selected_branch_id');
            if ($branchId) {
                return redirect()->route('admin.inventory.categories', ['branch' => $branchId])
                    ->with('success', 'Category created successfully.');
            }

            return redirect()->route('admin.inventory.categories')
                ->with('success', 'Category created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating category: ' . $e->getMessage());
            return back()->with('error', 'Error creating category. Please try again.');
        }
    }

    public function updateCategory(Request $request, InventoryCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:inventory_categories,code,' . $category->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        try {
            $category->update($validated);

            $branchId = $request->query('branch') ?? session('selected_branch_id');
            if ($branchId) {
                return redirect()->route('admin.inventory.categories', ['branch' => $branchId])
                    ->with('success', 'Category updated successfully.');
            }

            return redirect()->route('admin.inventory.categories')
                ->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating category: ' . $e->getMessage());
            return back()->with('error', 'Error updating category. Please try again.');
        }
    }

    public function deleteCategory(InventoryCategory $category)
    {
        try {
            if ($category->items()->count() > 0) {
                return back()->with('error', 'Cannot delete category with associated items.');
            }

            $category->delete();

            $branchId = request()->query('branch') ?? session('selected_branch_id');
            if ($branchId) {
                return redirect()->route('admin.inventory.categories', ['branch' => $branchId])
                    ->with('success', 'Category deleted successfully.');
            }

            return redirect()->route('admin.inventory.categories')
                ->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting category: ' . $e->getMessage());
            return back()->with('error', 'Error deleting category. Please try again.');
        }
    }
}
