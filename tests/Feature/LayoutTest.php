<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ProductOptimized;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The storefront chrome every page shares: header, category bar, phone menu, bottom bar and footer.
 */
class LayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_search_keeps_the_autocomplete_hooks(): void
    {
        $this->get('/shop?search=router')
            ->assertOk()
            ->assertSee('id="search-form"', false)
            ->assertSee('id="search-input"', false)
            ->assertSee('id="search-suggestions"', false)
            ->assertSee('value="router"', false)
            ->assertSee('sf-header is-search-open', false);
    }

    public function test_deals_lead_to_the_marked_down_products(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<a class="sf-pill" href="' . route('shop', ['sale' => 1]) . '">', $html);
        $this->assertSame(4, substr_count($html, 'href="' . route('shop', ['sale' => 1]) . '"'), 'Header, phone menu, footer and bottom bar');
    }

    public function test_the_made_up_offers_and_pc_builder_are_gone(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('offersModal')
            ->assertDontSee('pcBuilderModal')
            ->assertDontSee('WELCOME20')
            ->assertDontSee('Buy 2 Get 1 Free')
            ->assertSee('id="compareModal"', false);
    }

    public function test_cart_badges_show_the_cart_count(): void
    {
        $user = User::factory()->create();
        $html = $this->actingAs($user)->get('/')->assertOk()->getContent();
        $this->assertSame(3, preg_match_all('#class="sf-count cart-count"\s+hidden\s*>0</span>#', $html), 'Empty badges are hidden');

        $product = ProductOptimized::factory()->create();
        $this->actingAs($user)->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 2])->assertOk();

        $html = $this->actingAs($user)->get('/')->getContent();
        $this->assertSame(3, preg_match_all('#class="sf-count cart-count"\s*>2</span>#', $html), 'Header, phone header and bottom bar');
    }

    public function test_category_bar_and_phone_menu_list_menu_categories_with_products(): void
    {
        $laptops = Category::factory()->create(['name' => 'Laptop', 'is_menu' => true, 'sort_order' => 2]);
        $phones = Category::factory()->create(['name' => 'Smartphone', 'is_menu' => true, 'sort_order' => 1]);
        $hidden = Category::factory()->create(['name' => 'Spare Parts', 'is_menu' => false]);
        Category::factory()->create(['name' => 'Empty Aisle', 'is_menu' => true]);
        foreach ([$laptops, $phones, $hidden] as $category) {
            ProductOptimized::factory()->create(['category_id' => $category->id]);
        }

        $html = $this->get('/')->assertOk()->getContent();
        $bar = Str::betweenFirst($html, 'class="sf-catbar-list"', '</ul>');
        $menu = Str::betweenFirst($html, 'id="sf-drawer"', 'class="sf-drawer-label">Shop');

        foreach (['category bar' => $bar, 'phone menu' => $menu] as $where => $list) {
            $this->assertMatchesRegularExpression('/Smartphone.*Laptop/s', $list, "Menu order in the $where");
            $this->assertStringNotContainsString('Spare Parts', $list, "Only menu categories in the $where");
            $this->assertStringNotContainsString('Empty Aisle', $list, "No empty categories in the $where");
        }
        $this->assertStringContainsString('<svg class="category-icon"', $menu);
    }

    public function test_current_category_is_marked_in_the_bar(): void
    {
        $laptops = Category::factory()->create(['name' => 'Laptop', 'is_menu' => true]);
        ProductOptimized::factory()->create(['category_id' => $laptops->id]);

        $bar = Str::betweenFirst($this->get(route('shop', ['category' => $laptops->id]))->assertOk()->getContent(), 'class="sf-catbar-list"', '</ul>');

        $this->assertMatchesRegularExpression('#href="' . preg_quote(route('shop', ['category' => $laptops->id]), '#') . '"\s+aria-current="page"#', $bar);
    }

    public function test_bottom_bar_marks_the_current_page(): void
    {
        $bar = Str::betweenFirst($this->get('/cart')->assertOk()->getContent(), 'class="sf-bottom-nav"', '</nav>');

        $this->assertMatchesRegularExpression('#href="' . preg_quote(route('cart'), '#') . '"\s+aria-current="page"#', $bar);
        $this->assertStringContainsString('href="' . route('login') . '"', $bar, 'Guests reach their account through sign in');
    }

    public function test_footer_shows_promises_and_contact_details_only_when_set(): void
    {
        config(['shop.promises' => ['Free gift wrapping'], 'shop.contact.phone' => null]);

        $this->get('/')->assertOk()->assertSee('Free gift wrapping')->assertDontSee('7-day exchange')->assertDontSee('href="tel:', false);

        config(['shop.contact.phone' => '+880 1700-000000']);

        $this->get('/')->assertSee('href="tel:+8801700000000"', false)->assertSee('HTML Codex');
    }
}
