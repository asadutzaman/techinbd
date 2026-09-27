<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create', ['banner' => new Banner(['placement' => 'slider', 'is_active' => true, 'show_text' => true])]);
    }

    public function store(Request $request)
    {
        $banner = new Banner($this->validated($request, creating: true));
        $banner->image_path = $request->file('image')->store('banners/originals', 'public');
        if ($request->hasFile('mobile_image')) {
            $banner->mobile_image_path = $request->file('mobile_image')->store('banners/originals', 'public');
        }
        $banner->save();
        $banner->generateImages();

        return redirect()->route('admin.banners.index')->with('success', 'Banner added.');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $banner->fill($this->validated($request, creating: false));
        $disk = Storage::disk('public');
        // Slides and side banners are cropped to different shapes
        $imagesChanged = $banner->isDirty('placement');

        if ($request->hasFile('image')) {
            $disk->delete($banner->image_path);
            $banner->image_path = $request->file('image')->store('banners/originals', 'public');
            $imagesChanged = true;
        }

        if ($request->hasFile('mobile_image') || $request->boolean('remove_mobile_image')) {
            if ($banner->mobile_image_path) {
                $disk->delete($banner->mobile_image_path);
            }
            $banner->mobile_image_path = $request->hasFile('mobile_image')
                ? $request->file('mobile_image')->store('banners/originals', 'public')
                : null;
            $imagesChanged = true;
        }

        $banner->save();
        if ($imagesChanged) {
            $banner->generateImages();
        }

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated.');
    }

    public function destroy(Banner $banner)
    {
        $banner->deleteFiles();
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Banner deleted.');
    }

    private function validated(Request $request, bool $creating): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'subtitle' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:40',
            // A page on this site ("/shop?sale=1") or a full web address
            'link_url' => ['nullable', 'string', 'max:500', 'regex:#^(/|https?://)#'],
            'placement' => ['required', Rule::in(array_keys(Banner::PLACEMENTS))],
            'image' => [$creating ? 'required' : 'nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'mobile_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'sort_order' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
        ], [
            'link_url.regex' => 'Start the link with "/" for a page on this site, or with http:// or https://.',
            'ends_at.after' => 'The end time must be after the start time.',
        ]);

        return [
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'button_text' => $data['button_text'] ?? null,
            'link_url' => $data['link_url'] ?? null,
            'placement' => $data['placement'],
            'sort_order' => $data['sort_order'] ?? 0,
            'starts_at' => $this->toAppTime($data['starts_at'] ?? null),
            'ends_at' => $this->toAppTime($data['ends_at'] ?? null),
            'show_text' => $request->boolean('show_text'),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /**
     * Schedule times are entered in the shop's timezone; the app stores its own (UTC).
     */
    private function toAppTime(?string $value): ?Carbon
    {
        return $value ? Carbon::parse($value, config('shop.timezone'))->setTimezone(config('app.timezone')) : null;
    }
}
