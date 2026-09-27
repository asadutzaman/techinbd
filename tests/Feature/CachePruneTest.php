<?php

namespace Tests\Feature;

use App\Support\CatalogCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class CachePruneTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_removes_what_an_old_catalog_version_left_in_the_database_store(): void
    {
        config(['cache.default' => 'database']);
        $catalog = app(CatalogCache::class);

        $catalog->remember('home', 600, fn () => 'old home');
        // A product is saved: pages now build catalog:2:home and never read catalog:1:home again
        $catalog->bump();
        $this->travel(11)->minutes();
        $catalog->remember('home', 600, fn () => 'new home');

        $this->artisan('cache:prune-expired')
            ->expectsOutput('Removed 1 expired cache entry.')
            ->assertSuccessful();

        $keys = DB::table('cache')->pluck('key')->map(fn ($key) => Str::after($key, config('cache.prefix')))->sort()->values()->all();
        $this->assertSame(['catalog:2:home', 'catalog:version'], $keys);
        $this->assertSame('new home', $catalog->remember('home', 600, fn () => 'rebuilt'));
    }

    public function test_it_removes_expired_files_from_the_file_store(): void
    {
        $directory = storage_path('framework/testing/cache-prune');
        File::deleteDirectory($directory);
        config(['cache.stores.file.path' => $directory]);
        $store = Cache::store('file');

        $store->put('old', 'x', 60);
        $store->forever('kept', 'x');
        $this->travel(2)->minutes();
        $store->put('fresh', 'x', 60);
        File::put("{$directory}/notes.txt", 'not a cache entry');

        $this->artisan('cache:prune-expired', ['store' => 'file'])
            ->expectsOutput('Removed 1 expired cache entry.')
            ->assertSuccessful();

        $path = fn (string $key) => $store->getStore()->path($key);
        $this->assertFileDoesNotExist($path('old'));
        $this->assertFileExists($path('fresh'));
        $this->assertFileExists($path('kept'));
        $this->assertFileExists("{$directory}/notes.txt");

        File::deleteDirectory($directory);
    }

    public function test_stores_that_expire_keys_themselves_are_left_alone(): void
    {
        $this->artisan('cache:prune-expired', ['store' => 'array'])
            ->expectsOutput('The array store expires cache entries itself.')
            ->assertSuccessful();
    }

    public function test_it_runs_every_day(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('cache:prune-expired')
            ->assertSuccessful();
    }
}
