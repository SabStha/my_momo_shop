<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Branch;
use Illuminate\Support\Str;

class InventoryCategoryController extends Controller
{
    public function index(Request $request)
    {
        \Log::info('InventoryCategoryController@index called', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'path' => $request->path(),
            'user_id' => auth()->id(),
            'user_is_admin' => auth()->check() ? auth()->user()->isAdmin() : false,
            'user_roles' => auth()->check() ? auth()->user()->getRoleNames() : [],
            'branch_id' => $request->query('branch')
        ]);

        $branchId = $request->query('branch');
        
        // Get categories with inventory items count
        $categories = Category::withCount(['inventoryItems' => function($query) use ($branchId) {
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }
        }])->get();

        // Get branches for branch selector
        $branches = Branch::all();
        
        // Get current branch if specified
        $branch = $branchId ? Branch::find($branchId) : null;

        \Log::info('InventoryCategoryController@index returning view', [
            'categories_count' => $categories->count(),
            'branches_count' => $branches->count(),
            'branch_found' => $branch ? true : false
        ]);

        return view('admin.inventory.categories.index', compact('categories', 'branches', 'branch'));
    }

    public function create()
    {
        $branches = Branch::all();
        return view('admin.inventory.categories.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
        ]);

        Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->route('admin.admin.inventory.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show(Category $category)
    {
        $branches = Branch::all();
        return view('admin.inventory.categories.show', compact('category', 'branches'));
    }

    public function edit(Category $category)
    {
        $branches = Branch::all();
        return view('admin.inventory.categories.edit', compact('category', 'branches'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->route('admin.admin.inventory.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        // Check if category has any inventory items
        if ($category->inventoryItems()->count() > 0) {
            return redirect()->route('admin.admin.inventory.categories.index')
                ->with('error', 'Cannot delete category that has inventory items.');
        }

        $category->delete();

        return redirect()->route('admin.admin.inventory.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
} 