@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">

    <div class="mb-6">
        <a href="{{ route('admin.display-ads.index') }}" class="text-blue-600 text-sm hover:underline">
            <i class="fas fa-arrow-left mr-1"></i>Back to Display Ads
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Ad: {{ $displayAd->title }}</h1>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-4 text-sm">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.display-ads.update', $displayAd) }}" enctype="multipart/form-data"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        @csrf @method('PUT')

        {{-- Type badge (read-only) --}}
        <div class="bg-gray-50 rounded-lg px-4 py-3 flex items-center gap-2 text-sm text-gray-600">
            @if($displayAd->type === 'upload')
                <i class="fas fa-upload text-purple-500"></i> <strong>Upload</strong>
            @elseif($displayAd->type === 'youtube')
                <i class="fab fa-youtube text-red-500"></i> <strong>YouTube</strong>
            @else
                <i class="fab fa-vimeo text-blue-500"></i> <strong>Vimeo</strong>
            @endif
            <span class="text-gray-400 ml-1">(type cannot be changed — delete and re-add to switch)</span>
        </div>

        {{-- Title --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input type="text" name="title" value="{{ old('title', $displayAd->title) }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        @if($displayAd->type === 'upload')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Replace Video File <span class="text-gray-400">(leave blank to keep current)</span></label>
                @if($displayAd->file_path)
                    <p class="text-xs text-gray-500 mb-2">Current: {{ $displayAd->file_path }}</p>
                @endif
                <input type="file" name="video" accept="video/mp4,video/webm,video/ogg"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
        @else
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Video URL</label>
                <input type="text" name="video_url" value="{{ old('video_url', $displayAd->video_url) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>
        @endif

        {{-- Branch --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
            <select name="branch_id" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="">All branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id', $displayAd->branch_id) == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Active --}}
        <div class="flex items-center gap-3">
            <input type="checkbox" name="is_active" id="is_active" value="1"
                   class="w-4 h-4 rounded text-blue-600"
                   {{ old('is_active', $displayAd->is_active) ? 'checked' : '' }}>
            <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium">
                Save Changes
            </button>
            <a href="{{ route('admin.display-ads.index') }}"
               class="px-6 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
