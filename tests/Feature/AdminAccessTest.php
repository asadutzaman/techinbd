<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private const ADMIN_PAGES = ['/admin', '/admin/products', '/admin/orders', '/admin/categories', '/admin/brands', '/admin/attributes', '/admin/settings'];

    public function test_guests_are_sent_to_login(): void
    {
        foreach (self::ADMIN_PAGES as $uri) {
            $this->get($uri)->assertRedirect(route('login'));
        }
    }

    public function test_customers_are_forbidden(): void
    {
        $customer = User::factory()->create();

        foreach (self::ADMIN_PAGES as $uri) {
            $this->actingAs($customer)->get($uri)->assertForbidden();
        }
    }

    public function test_admins_can_use_the_panel(): void
    {
        $admin = User::factory()->create();
        $this->artisan('admin:grant', ['email' => $admin->email])->assertSuccessful();

        foreach (self::ADMIN_PAGES as $uri) {
            $this->actingAs($admin->fresh())->get($uri)->assertOk();
        }
    }

    public function test_is_admin_cannot_be_set_through_registration(): void
    {
        $this->post('/register', [
            'name' => 'Sneaky',
            'email' => 'sneaky@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'is_admin' => 1,
        ])->assertRedirect(route('home'));

        $this->assertFalse(User::where('email', 'sneaky@example.com')->sole()->is_admin);
    }

    public function test_admin_login_lands_on_the_dashboard(): void
    {
        $admin = User::factory()->create(['password' => bcrypt('secret123')]);
        $admin->forceFill(['is_admin' => true])->save();

        $this->post('/login', ['email' => $admin->email, 'password' => 'secret123'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $attempt) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])->assertStatus(429);
    }
}
