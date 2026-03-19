@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">

    <div class="mb-6">
        <a href="{{ route('admin.display-ads.index') }}" class="text-blue-600 text-sm hover:underline">
            <i class="fas fa-arrow-left mr-1"></i>Back to Display Ads
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Add Video Ad</h1>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-4 text-sm">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.display-ads.store') }}" enctype="multipart/form-data"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        @csrf

        {{-- Title --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input type="text" name="title" value="{{ old('title') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
                   placeholder="e.g. Momo Special Offer">
        </div>

        {{-- Type --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Video Source</label>
            <div class="flex gap-3">
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="type" value="upload" class="sr-only" {{ old('type','upload')==='upload'?'checked':'' }} onchange="switchType('upload')">
                    <div class="type-btn border-2 rounded-lg p-3 text-center transition-all {{ old('type','upload')==='upload'?'border-blue-500 bg-blue-50':'border-gray-200' }}">
                        <i class="fas fa-upload text-xl text-purple-500 mb-1 block"></i>
                        <div class="text-sm font-medium">Upload File</div>
                        <div class="text-xs text-gray-400">MP4, WebM, OGG</div>
                    </div>
                </label>
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="type" value="youtube" class="sr-only" {{ old('type')==='youtube'?'checked':'' }} onchange="switchType('youtube')">
                    <div class="type-btn border-2 rounded-lg p-3 text-center transition-all {{ old('type')==='youtube'?'border-blue-500 bg-blue-50':'border-gray-200' }}">
                        <i class="fab fa-youtube text-xl text-red-500 mb-1 block"></i>
                        <div class="text-sm font-medium">YouTube</div>
                        <div class="text-xs text-gray-400">Paste URL</div>
                    </div>
                </label>
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="type" value="vimeo" class="sr-only" {{ old('type')==='vimeo'?'checked':'' }} onchange="switchType('vimeo')">
                    <div class="type-btn border-2 rounded-lg p-3 text-center transition-all {{ old('type')==='vimeo'?'border-blue-500 bg-blue-50':'border-gray-200' }}">
                        <i class="fab fa-vimeo text-xl text-blue-500 mb-1 block"></i>
                        <div class="text-sm font-medium">Vimeo</div>
                        <div class="text-xs text-gray-400">Paste URL</div>
                    </div>
                </label>
            </div>
        </div>

        {{-- Upload field --}}
        <div id="uploadField" class="{{ old('type','upload')!=='upload'?'hidden':'' }}">
            <label class="block text-sm font-medium text-gray-700 mb-1">Video File</label>
            <input type="file" name="video" accept="video/mp4,video/webm,video/ogg"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <p class="text-xs text-gray-400 mt-1">Max 200MB. MP4 recommended.</p>
        </div>

        {{-- URL field --}}
        <div id="urlField" class="{{ in_array(old('type'),['youtube','vimeo'])?'':'hidden' }}">
            <label class="block text-sm font-medium text-gray-700 mb-1">Video URL</label>
            <input type="text" name="video_url" value="{{ old('video_url') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
                   placeholder="https://www.youtube.com/watch?v=... or https://vimeo.com/...">
            <p id="urlHint" class="text-xs text-gray-400 mt-1"></p>
        </div>

        {{-- Branch --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Branch <span class="text-gray-400">(optional — leave blank to show on all branches)</span></label>
            <select name="branch_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="">All branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Active --}}
        <div class="flex items-center gap-3">
            <input type="checkbox" name="is_active" id="is_active" value="1"
                   class="w-4 h-4 rounded text-blue-600" {{ old('is_active', '1') ? 'checked' : '' }}>
            <label for="is_active" class="text-sm font-medium text-gray-700">Active (show in viewer)</label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium">
                Save Ad
            </button>
            <a href="{{ route('admin.display-ads.index') }}"
               class="px-6 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function switchType(type) {
    document.getElementById('uploadField').classList.toggle('hidden', type !== 'upload');
    document.getElementById('urlField').classList.toggle('hidden', type === 'upload');

    const hints = {
        youtube: 'e.g. https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        vimeo:   'e.g. https://vimeo.com/123456789'
    };
    const hint = document.getElementById('urlHint');
    if (hint) hint.textContent = hints[type] || '';

    // Update card highlight
    document.querySelectorAll('.type-btn').forEach(el => {
        el.classList.remove('border-blue-500', 'bg-blue-50');
        el.classList.add('border-gray-200');
    });
    const active = document.querySelector(`input[value="${type}"] + .type-btn`);
    if (active) {
        active.classList.remove('border-gray-200');
        active.classList.add('border-blue-500', 'bg-blue-50');
    }
}

// Initialise hint on load
document.addEventListener('DOMContentLoaded', () => {
    const checked = document.querySelector('input[name="type"]:checked');
    if (checked) switchType(checked.value);
});
</script>
@endpush
@endsection
