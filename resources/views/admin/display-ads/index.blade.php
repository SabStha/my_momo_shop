@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Display Ads</h1>
            <p class="text-sm text-gray-500 mt-1">Videos shown on the customer payment viewer when idle</p>
        </div>
        <a href="{{ route('admin.display-ads.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
            <i class="fas fa-plus"></i> Add Video
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($ads->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400">
            <i class="fas fa-tv text-4xl mb-3 block"></i>
            <p class="font-medium">No ads yet.</p>
            <p class="text-sm mt-1">Add a video and it will play on the customer viewer when no order is active.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 w-8">#</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Title</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Type</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Branch</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Active</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600">Order</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody id="adsList" class="divide-y divide-gray-100">
                    @foreach($ads as $ad)
                    <tr data-id="{{ $ad->id }}" class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-400 cursor-move">
                            <i class="fas fa-grip-vertical"></i>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $ad->title }}</div>
                            @if($ad->type === 'upload' && $ad->file_path)
                                <div class="text-xs text-gray-400 truncate max-w-xs">{{ $ad->file_path }}</div>
                            @elseif($ad->video_url)
                                <div class="text-xs text-gray-400 truncate max-w-xs">{{ $ad->video_url }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($ad->type === 'upload')
                                <span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded text-xs font-medium">
                                    <i class="fas fa-upload mr-1"></i>Upload
                                </span>
                            @elseif($ad->type === 'youtube')
                                <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded text-xs font-medium">
                                    <i class="fab fa-youtube mr-1"></i>YouTube
                                </span>
                            @else
                                <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-xs font-medium">
                                    <i class="fab fa-vimeo mr-1"></i>Vimeo
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ $ad->branch_id ? ('Branch #' . $ad->branch_id) : 'All branches' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="toggleAd({{ $ad->id }}, this)"
                                    data-active="{{ $ad->is_active ? '1' : '0' }}"
                                    class="relative inline-flex h-6 w-11 rounded-full transition-colors {{ $ad->is_active ? 'bg-green-500' : 'bg-gray-300' }}"
                                    title="{{ $ad->is_active ? 'Active — click to disable' : 'Inactive — click to enable' }}">
                                <span class="inline-block h-5 w-5 rounded-full bg-white shadow transform transition-transform mt-0.5 {{ $ad->is_active ? 'translate-x-5' : 'translate-x-0.5' }}"></span>
                            </button>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button onclick="moveAd({{ $ad->id }}, 'up')"
                                        class="text-gray-400 hover:text-gray-600 px-1" title="Move up">
                                    <i class="fas fa-chevron-up text-xs"></i>
                                </button>
                                <span class="text-gray-400 text-xs w-6 text-center">{{ $ad->display_order }}</span>
                                <button onclick="moveAd({{ $ad->id }}, 'down')"
                                        class="text-gray-400 hover:text-gray-600 px-1" title="Move down">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </button>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.display-ads.edit', $ad) }}"
                                   class="text-blue-600 hover:text-blue-800 text-xs font-medium px-2 py-1 rounded hover:bg-blue-50">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </a>
                                <form method="POST" action="{{ route('admin.display-ads.destroy', $ad) }}"
                                      onsubmit="return confirm('Delete this ad?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 hover:text-red-800 text-xs font-medium px-2 py-1 rounded hover:bg-red-50">
                                        <i class="fas fa-trash mr-1"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@push('scripts')
<script>
function toggleAd(id, btn) {
    fetch(`/admin/display-ads/${id}/toggle`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
    .then(r => r.json())
    .then(data => {
        btn.dataset.active = data.is_active ? '1' : '0';
        btn.className = `relative inline-flex h-6 w-11 rounded-full transition-colors ${data.is_active ? 'bg-green-500' : 'bg-gray-300'}`;
        btn.querySelector('span').className = `inline-block h-5 w-5 rounded-full bg-white shadow transform transition-transform mt-0.5 ${data.is_active ? 'translate-x-5' : 'translate-x-0.5'}`;
        btn.title = data.is_active ? 'Active — click to disable' : 'Inactive — click to enable';
    });
}

function moveAd(id, dir) {
    const rows = Array.from(document.querySelectorAll('#adsList tr'));
    const idx  = rows.findIndex(r => r.dataset.id == id);
    if (dir === 'up'   && idx === 0)              return;
    if (dir === 'down' && idx === rows.length - 1) return;

    const swap = dir === 'up' ? rows[idx - 1] : rows[idx + 1];
    if (dir === 'up') swap.before(rows[idx]);
    else              swap.after(rows[idx]);

    saveOrder();
}

function saveOrder() {
    const order = Array.from(document.querySelectorAll('#adsList tr')).map(r => r.dataset.id);
    fetch('{{ route("admin.display-ads.reorder") }}', {
        method: 'POST',
        headers: {
            'Content-Type':  'application/json',
            'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ order })
    });
}
</script>
@endpush
@endsection
