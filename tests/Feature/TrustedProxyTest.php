<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\ProductOptimized;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The public site reaches the app through a Cloudflare Tunnel on the same machine: requests arrive
 * from 127.0.0.1 with the visitor's scheme in X-Forwarded-Proto.
 */
class TrustedProxyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Banner::forceCreate([
            'title' => 'MacBook Air M5',
            'show_text' => false,
            'is_active' => true,
            'image_path' => 'banners/originals/m5.jpg',
            'images' => [
                'desktop' => [800 => 'banners/1/desktop-800-abc.webp', 1600 => 'banners/1/desktop-1600-abc.webp'],
                'mobile' => [600 => 'banners/1/mobile-600-abc.webp', 1200 => 'banners/1/mobile-1200-abc.webp'],
            ],
        ]);
    }

    public function test_https_visits_through_the_tunnel_get_https_links(): void
    {
        ProductOptimized::factory()->featured()->create();

        // Browsers block http:// images and AJAX on an https page, which hid the banner on the public site
        $html = $this->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-For' => '203.0.113.9'])
            ->get('/')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('srcset="https://localhost/storage/banners/1/desktop-800-abc.webp 800w', $html);
        $this->assertStringContainsString('imagesrcset="https://localhost/storage/banners/1/mobile-600-abc.webp 600w', $html);
        $this->assertStringContainsString("url: 'https://localhost/search/suggestions'", $html);
        $this->assertStringContainsString('data-cart-url="https://localhost/cart/add"', $html);
        $this->assertStringNotContainsString('http://localhost', $html);
    }

    public function test_a_forwarded_host_is_ignored(): void
    {
        // Trusting it would let a visitor point generated links, such as password resets, at another site
        $this->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-Host' => 'attacker.example'])
            ->get('/')
            ->assertOk()
            ->assertSee("url: 'https://localhost/search/suggestions'", false)
            ->assertDontSee('attacker.example');
    }

    public function test_proxy_headers_from_other_addresses_are_ignored(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.9'])
            ->withHeaders(['X-Forwarded-Proto' => 'https'])
            ->get('/')
            ->assertOk()
            ->assertSee("url: 'http://localhost/search/suggestions'", false)
            ->assertDontSee('https://localhost');
    }
}
