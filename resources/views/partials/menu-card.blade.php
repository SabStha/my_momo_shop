<!-- resources/views/partials/menu-item.blade.php -->
<div class="bg-white p-4 rounded-xl shadow-md flex gap-4">
<img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-full h-40 object-cover rounded-xl shadow-md" />

    <div>
        <h3 class="text-lg font-bold text-[#2E2E2E]">{{ $item->name }}</h3>
        <p class="text-sm text-gray-600">{{ $item->description }}</p>
        <div class="text-[#6E0D25] font-semibold mt-1">Rs.{{ number_format($item->price, 2) }}</div>
    </div>
</div>
