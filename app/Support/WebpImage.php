<?php

namespace App\Support;

use GdImage;
use Illuminate\Support\Facades\Storage;

/**
 * Resized WebP copies of images on the public disk, made with GD (no extra package).
 */
class WebpImage
{
    /**
     * Write a WebP copy of $source (a public-disk path) no wider than $maxWidth, center-cropped
     * to $ratio (width / height) first when one is given. Never upscales.
     *
     * @return array{path: string, width: int, height: int}|null null when GD can't read the file
     */
    public static function make(string $source, string $target, int $maxWidth, ?float $ratio = null, int $quality = 80): ?array
    {
        $disk = Storage::disk('public');
        if (! function_exists('imagewebp') || ! $disk->exists($source)) {
            return null;
        }

        $image = @imagecreatefromstring($disk->get($source));
        if (! $image) {
            return null;
        }

        imagepalettetotruecolor($image);
        if ($ratio) {
            $image = self::cropToRatio($image, $ratio);
        }
        if (imagesx($image) > $maxWidth) {
            $image = imagescale($image, $maxWidth);
        }
        imagesavealpha($image, true);

        ob_start();
        imagewebp($image, null, $quality);
        $disk->put($target, ob_get_clean());

        return ['path' => $target, 'width' => imagesx($image), 'height' => imagesy($image)];
    }

    private static function cropToRatio(GdImage $image, float $ratio): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if (abs($width / $height - $ratio) < 0.01) {
            return $image;
        }

        // Too wide: trim both sides equally. Too tall: trim more from the bottom than the top,
        // since faces and headlines usually sit in the upper part of a photo.
        $crop = $width / $height > $ratio
            ? ['x' => intdiv($width - (int) round($height * $ratio), 2), 'y' => 0, 'width' => (int) round($height * $ratio), 'height' => $height]
            : ['x' => 0, 'y' => (int) round(($height - round($width / $ratio)) * .3), 'width' => $width, 'height' => (int) round($width / $ratio)];

        return imagecrop($image, $crop) ?: $image;
    }
}
