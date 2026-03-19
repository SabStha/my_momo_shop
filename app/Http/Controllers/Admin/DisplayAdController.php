<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DisplayAd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DisplayAdController extends Controller
{
    public function index()
    {
        $ads = DisplayAd::orderBy('display_order')->orderBy('id')->get();
        return view('admin.display-ads.index', compact('ads'));
    }

    public function create()
    {
        $branches = \App\Models\Branch::all();
        return view('admin.display-ads.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'     => 'required|string|max:255',
            'type'      => 'required|in:upload,youtube,vimeo',
            'video'     => 'required_if:type,upload|file|mimetypes:video/mp4,video/webm,video/ogg|max:204800',
            'video_url' => 'required_if:type,youtube|required_if:type,vimeo|nullable|string|max:500',
        ]);

        $data = [
            'title'         => $request->title,
            'type'          => $request->type,
            'is_active'     => $request->boolean('is_active', true),
            'branch_id'     => $request->branch_id ?: null,
            'display_order' => DisplayAd::max('display_order') + 1,
        ];

        if ($request->type === 'upload' && $request->hasFile('video')) {
            $data['file_path'] = $request->file('video')->store('display-ads', 'public');
        } else {
            $url = $request->video_url;
            $data['video_url'] = $url;
            if ($request->type === 'youtube') {
                $data['youtube_id'] = $this->extractYoutubeId($url);
            } elseif ($request->type === 'vimeo') {
                $data['vimeo_id'] = $this->extractVimeoId($url);
            }
        }

        DisplayAd::create($data);
        return redirect()->route('admin.display-ads.index')->with('success', 'Ad created successfully.');
    }

    public function edit(DisplayAd $displayAd)
    {
        $branches = \App\Models\Branch::all();
        return view('admin.display-ads.edit', compact('displayAd', 'branches'));
    }

    public function update(Request $request, DisplayAd $displayAd)
    {
        $request->validate([
            'title'     => 'required|string|max:255',
            'video'     => 'nullable|file|mimetypes:video/mp4,video/webm,video/ogg|max:204800',
            'video_url' => 'nullable|string|max:500',
        ]);

        $data = [
            'title'     => $request->title,
            'is_active' => $request->boolean('is_active'),
            'branch_id' => $request->branch_id ?: null,
        ];

        if ($request->hasFile('video')) {
            if ($displayAd->file_path) {
                Storage::disk('public')->delete($displayAd->file_path);
            }
            $data['file_path'] = $request->file('video')->store('display-ads', 'public');
        } elseif ($request->filled('video_url') && $request->video_url !== $displayAd->video_url) {
            $url = $request->video_url;
            $data['video_url'] = $url;
            if ($displayAd->type === 'youtube') {
                $data['youtube_id'] = $this->extractYoutubeId($url);
            } elseif ($displayAd->type === 'vimeo') {
                $data['vimeo_id'] = $this->extractVimeoId($url);
            }
        }

        $displayAd->update($data);
        return redirect()->route('admin.display-ads.index')->with('success', 'Ad updated.');
    }

    public function destroy(DisplayAd $displayAd)
    {
        if ($displayAd->file_path) {
            Storage::disk('public')->delete($displayAd->file_path);
        }
        $displayAd->delete();
        return redirect()->route('admin.display-ads.index')->with('success', 'Ad deleted.');
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array']);
        foreach ($request->order as $index => $id) {
            DisplayAd::where('id', $id)->update(['display_order' => $index]);
        }
        return response()->json(['success' => true]);
    }

    public function toggleActive(DisplayAd $displayAd)
    {
        $displayAd->update(['is_active' => !$displayAd->is_active]);
        return response()->json(['is_active' => $displayAd->is_active]);
    }

    private function extractYoutubeId(string $url): ?string
    {
        preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $url, $matches);
        return $matches[1] ?? null;
    }

    private function extractVimeoId(string $url): ?string
    {
        preg_match('/vimeo\.com\/(\d+)/', $url, $matches);
        return $matches[1] ?? null;
    }
}
