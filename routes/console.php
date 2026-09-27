<?php

use App\Models\ProductImageOptimized;
use App\Models\ProductOptimized;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Str;

// Needs the scheduler cron in production: * * * * * php artisan schedule:run
Schedule::command('model:prune')->daily();
Schedule::command('cache:prune-expired')->daily();

// The database and file cache stores only delete an expired entry when the same key is read again. CatalogCache
// moves to a new version on every catalog change and never reads the old one's keys again, so without this they
// pile up. Redis and Memcached expire keys themselves.
Artisan::command('cache:prune-expired {store? : The cache store to prune (the default one when left out)}', function (?string $store = null) {
    $store ??= config('cache.default');
    $config = config("cache.stores.{$store}");
    $now = now()->getTimestamp();

    if (($config['driver'] ?? null) === 'database') {
        $deleted = DB::connection($config['connection'] ?? null)
            ->table($config['table'] ?? 'cache')
            ->where('expiration', '<=', $now)
            ->delete();
    } elseif (($config['driver'] ?? null) === 'file') {
        $deleted = 0;
        // Each file starts with its expiry as a 10-digit timestamp (9999999999 for "forever")
        foreach (File::isDirectory($config['path']) ? File::allFiles($config['path']) : [] as $file) {
            $expiration = (string) file_get_contents($file->getPathname(), false, null, 0, 10);
            if (ctype_digit($expiration) && (int) $expiration <= $now && File::delete($file->getPathname())) {
                $deleted++;
            }
        }
    } else {
        $this->info("The {$store} store expires cache entries itself.");

        return;
    }

    $this->info("Removed {$deleted} expired cache " . Str::plural('entry', $deleted) . '.');
})->purpose('Delete the expired entries the database and file cache stores leave behind');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:grant {email} {--revoke : Remove admin access instead}', function (string $email) {
    $user = User::where('email', $email)->first();

    if (! $user) {
        $this->error("No user with email {$email}. Register the account first.");

        return 1;
    }

    $user->is_admin = ! $this->option('revoke');
    $user->save();

    $this->info($user->is_admin ? "{$email} is now an admin." : "{$email} is no longer an admin.");
})->purpose('Grant (or --revoke) admin panel access for a user');

// Admin accounts can't reset their password by email (User::sendPasswordResetNotification); this is how they do
Artisan::command('admin:password {email} {--password= : The new password; asked for when left out}', function (string $email) {
    $user = User::where('email', $email)->first();
    if (! $user) {
        $this->error("No user with email {$email}.");

        return 1;
    }

    $password = $this->option('password') ?? $this->secret('New password (at least 8 characters)');
    if (mb_strlen((string) $password) < 8) {
        $this->error('The password needs at least 8 characters.');

        return 1;
    }

    // Signs out any "remember me" sessions along with the old password
    $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
    $this->info("Password changed for {$email}.");
})->purpose('Set a new password for an account (admins cannot reset theirs by email)');

Artisan::command('products:reindex', function () {
    $count = 0;
    ProductOptimized::query()->chunkById(200, function ($products) use (&$count) {
        foreach ($products as $product) {
            $product->updateSearchIndex();
            $count++;
        }
    });

    $this->info("Rebuilt the search index for {$count} products.");
})->purpose('Rebuild product_search_index (run after importing or seeding products)');

Artisan::command('products:thumbnails', function () {
    $count = 0;
    ProductImageOptimized::where(fn ($query) => $query->whereNull('thumb_url')->orWhereNull('large_url'))
        ->chunkById(100, function ($images) use (&$count) {
            foreach ($images as $image) {
                $image->generateVersions();
                $count += $image->thumb_url && $image->large_url ? 1 : 0;
            }
        });

    $this->info("Generated card and gallery copies for {$count} images.");
})->purpose('Generate the card (600px) and gallery (1200px) WebP copies for product images that lack them');
