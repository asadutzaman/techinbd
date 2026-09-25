<?php

use App\Models\ProductImageOptimized;
use App\Models\ProductOptimized;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Needs the scheduler cron in production: * * * * * php artisan schedule:run
Schedule::command('model:prune')->daily();

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
    ProductImageOptimized::whereNull('thumb_url')->chunkById(100, function ($images) use (&$count) {
        foreach ($images as $image) {
            $image->generateThumbnail();
            $count += $image->thumb_url ? 1 : 0;
        }
    });

    $this->info("Generated {$count} thumbnails.");
})->purpose('Generate card thumbnails for product images uploaded before thumbnails existed');
