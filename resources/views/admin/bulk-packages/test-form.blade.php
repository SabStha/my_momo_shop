@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Test Bulk Package Form</h1>
        
        <form action="{{ route('admin.test-bulk-package-form') }}" method="POST" x-data="testForm()">
            @csrf
            
            <div class="bg-white shadow-md rounded-lg p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Package Name</label>
                    <input type="text" name="name" x-model="formData.name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Emoji</label>
                    <input type="text" name="emoji" x-model="formData.emoji" required placeholder="🎉"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" x-model="formData.description" rows="3" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                    <select name="type" x-model="formData.type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Type</option>
                        <option value="cooked">Cooked</option>
                        <option value="frozen">Frozen</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Package Key</label>
                    <input type="text" name="package_key" x-model="formData.package_key" required placeholder="test"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Price</label>
                    <input type="number" name="total_price" x-model="formData.total_price" step="0.01" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                    <input type="number" name="sort_order" x-model="formData.sort_order" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <!-- Test Items -->
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Test Items</h3>
                        <button type="button" @click="addItem()" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                            Add Item
                        </button>
                    </div>
                    
                    <div class="space-y-3">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex gap-3 items-end">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Item Name</label>
                                    <input type="text" x-model="item.name" :name="'items[' + index + '][name]'" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="w-32">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Price (Rs.)</label>
                                    <input type="number" x-model.number="item.price" :name="'items[' + index + '][price]'" step="0.01" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <button type="button" @click="removeItem(index)" class="bg-red-600 text-white px-3 py-2 rounded hover:bg-red-700">
                                    Remove
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                        Test Submit
                    </button>
                </div>
            </div>
        </form>
        
        <div class="mt-8 bg-gray-100 p-4 rounded-lg">
            <h3 class="font-semibold mb-2">Form Data (Live Preview):</h3>
            <pre x-text="JSON.stringify({...formData, items}, null, 2)" class="text-sm"></pre>
        </div>
    </div>
</div>

<script>
function testForm() {
    return {
        formData: {
            name: 'Test Package',
            emoji: '🎉',
            description: 'This is a test package',
            type: 'cooked',
            package_key: 'test-package',
            total_price: 100,
            sort_order: 1
        },
        items: [
            { name: 'Test Item 1', price: 50 },
            { name: 'Test Item 2', price: 50 }
        ],
        
        addItem() {
            this.items.push({ name: '', price: 0 });
        },
        
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        }
    }
}
</script>
@endsection
