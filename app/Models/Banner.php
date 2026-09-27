<?php

namespace App\Models;

use App\Models\Concerns\BustsCatalogCache;
use App\Support\WebpImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * A home page banner: a slide in the main slider, or one of the two banners beside it.
 *
 * Uploads are kept as originals and served as WebP versions cropped to the slot. Slides are
 * 3:1 on tablets and desktops and 2:1 on phones (from the phone image when one is uploaded);
 * side banners are 2:1 everywhere.
 */
class Banner extends Model
{
    use HasFactory, BustsCatalogCache;

    public const PLACEMENTS = [
        'slider' => 'Main slider',
        'side' => 'Beside the slider',
    ];

    /** Only this many side banners show, the first ones in banner order */
    public const SIDE_SLOTS = 2;

    public const DESKTOP_RATIO = 3.0;
    public const MOBILE_RATIO = 2.0;
    public const SIDE_RATIO = 2.0;

    /** Widths generated per version; each is capped at the upload's own width */
    private const WIDTHS = [
        'desktop' => [800, 1600],
        'mobile' => [600, 1200],
        'side' => [400, 800],
    ];

    protected $fillable = [
        'title',
        'subtitle',
        'button_text',
        'link_url',
        'placement',
        'show_text',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'show_text' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'images' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function isSide(): bool
    {
        return $this->placement === 'side';
    }

    /**
     * The version shown on computers: the slide's 3:1 crop, or the side banner's 2:1 crop.
     */
    public function mainVersion(): string
    {
        return $this->isSide() ? 'side' : 'desktop';
    }

    /**
     * Active and inside its schedule. Checked per request, since home data is cached.
     */
    public function isLive(?Carbon $now = null): bool
    {
        $now ??= now();

        return $this->is_active
            && (! $this->starts_at || $this->starts_at->lte($now))
            && (! $this->ends_at || $this->ends_at->gt($now));
    }

    public function getStatusLabelAttribute(): string
    {
        return match (true) {
            ! $this->is_active => 'Hidden',
            $this->starts_at && $this->starts_at->isFuture() => 'Scheduled',
            $this->ends_at && $this->ends_at->isPast() => 'Ended',
            default => 'Live',
        };
    }

    /**
     * Generate the WebP versions shown on the storefront, replacing any earlier ones.
     */
    public function generateImages(): void
    {
        $this->deleteGeneratedImages();

        // Side banners are small on every screen, so one 2:1 crop serves phones too
        $sources = $this->isSide()
            ? ['side' => [$this->image_path, self::SIDE_RATIO]]
            : [
                'desktop' => [$this->image_path, self::DESKTOP_RATIO],
                'mobile' => [$this->mobile_image_path ?: $this->image_path, self::MOBILE_RATIO],
            ];

        // A new name per generation, so browsers don't keep showing a replaced banner
        $token = Str::lower(Str::random(6));
        $images = [];
        foreach ($sources as $version => [$source, $ratio]) {
            foreach (self::WIDTHS[$version] as $width) {
                $image = WebpImage::make($source, "banners/{$this->id}/{$version}-{$width}-{$token}.webp", $width, $ratio);
                if (! $image) {
                    continue;
                }

                // Keyed by real width; a small upload yields the same width twice, so drop the copy
                if (isset($images[$version][$image['width']])) {
                    Storage::disk('public')->delete($image['path']);
                } else {
                    $images[$version][$image['width']] = $image['path'];
                }
            }
        }

        $this->forceFill(['images' => $images])->save();
    }

    /**
     * "url 800w, url 1600w" for a version's srcset.
     */
    public function srcset(string $version): string
    {
        // Sorted explicitly: MySQL reorders JSON object keys
        return collect($this->images[$version] ?? [])
            ->sortKeys()
            ->map(fn ($path, $width) => asset('storage/' . $path) . " {$width}w")
            ->implode(', ');
    }

    /**
     * The largest version, used as the fallback src and in the admin list.
     */
    public function src(?string $version = null): string
    {
        $paths = $this->images[$version ?? $this->mainVersion()] ?? [];

        return $paths ? asset('storage/' . $paths[max(array_keys($paths))]) : asset('storage/' . $this->image_path);
    }

    public function deleteFiles(): void
    {
        $this->deleteGeneratedImages();
        Storage::disk('public')->delete(array_filter([$this->image_path, $this->mobile_image_path]));
    }

    private function deleteGeneratedImages(): void
    {
        $paths = collect($this->images ?? [])->flatten()->all();
        if ($paths) {
            Storage::disk('public')->delete($paths);
        }
    }
}
