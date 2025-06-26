@php
    $editing = isset($offer);
@endphp

<div class="space-y-6">
    <div>
        <label class="block font-semibold mb-1">Title</label>
        <input type="text" name="title" class="w-full border rounded px-3 py-2" value="{{ old('title', $offer->title ?? '') }}" required>
    </div>
    <div>
        <label class="block font-semibold mb-1">Description</label>
        <textarea name="description" class="w-full border rounded px-3 py-2" rows="2" required>{{ old('description', $offer->description ?? '') }}</textarea>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block font-semibold mb-1">Code</label>
            <input type="text" name="code" class="w-full border rounded px-3 py-2 font-mono" value="{{ old('code', $offer->code ?? '') }}" required>
        </div>
        <div>
            <label class="block font-semibold mb-1">Discount (%)</label>
            <input type="number" name="discount" class="w-full border rounded px-3 py-2" min="0" max="100" step="0.01" value="{{ old('discount', $offer->discount ?? '') }}" required>
        </div>
        <div>
            <label class="block font-semibold mb-1">Type</label>
            <select name="type" class="w-full border rounded px-3 py-2">
                <option value="">Select type</option>
                <option value="discount" @if(old('type', $offer->type ?? '')=='discount') selected @endif>Discount</option>
                <option value="bogo" @if(old('type', $offer->type ?? '')=='bogo') selected @endif>Buy 2 Get 1</option>
                <option value="flash" @if(old('type', $offer->type ?? '')=='flash') selected @endif>Flash Sale</option>
                <option value="loyalty" @if(old('type', $offer->type ?? '')=='loyalty') selected @endif>Loyalty</option>
                <option value="bulk" @if(old('type', $offer->type ?? '')=='bulk') selected @endif>Bulk</option>
            </select>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block font-semibold mb-1">Valid From</label>
            <input type="date" name="valid_from" class="w-full border rounded px-3 py-2" value="{{ old('valid_from', isset($offer->valid_from) ? $offer->valid_from->format('Y-m-d') : '') }}">
        </div>
        <div>
            <label class="block font-semibold mb-1">Valid Until</label>
            <input type="date" name="valid_until" class="w-full border rounded px-3 py-2" value="{{ old('valid_until', isset($offer->valid_until) ? $offer->valid_until->format('Y-m-d') : '') }}">
        </div>
    </div>
    <div class="flex items-center gap-2">
        <input type="checkbox" name="is_active" id="is_active" value="1" @if(old('is_active', $offer->is_active ?? true)) checked @endif>
        <label for="is_active" class="font-semibold">Active</label>
    </div>
</div> 