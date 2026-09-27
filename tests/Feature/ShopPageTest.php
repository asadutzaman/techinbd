<?php

namespace Tests\Feature;

use App\Models\AttributeOptimized;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductAttributeOptimized;
use App\Models\ProductOptimized;
use App\Support\ShopFilters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

class ShopPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_heading_says_what_is_listed(): void
    {
        $laptops = Category::factory()->create(['name' => 'Laptop']);
        $apple = Brand::factory()->create(['name' => 'Apple']);
        ProductOptimized::factory()->onSale(1500)->featured()->create([
            'name' => 'MacBook Air', 'category_id' => $laptops->id, 'brand_id' => $apple->id, 'base_price' => 1000,
        ]);

        $this->assertSame('All products', $this->heading('/shop'));
        $this->assertSame('Laptop', $this->heading(route('shop.category', $laptops)));
        $this->assertSame('Apple', $this->heading('/shop?brand=' . $apple->id));
        $this->assertSame('Deals', $this->heading('/shop?sale=1'));
        $this->assertSame('Featured products', $this->heading('/shop?featured=1'));
        $this->assertSame('Results for “macbook”', $this->heading('/shop?search=macbook'));

        $this->get(route('shop.category', $laptops))
            ->assertSee('<title>Laptop | ' . config('shop.name') . '</title>', false)
            ->assertSee('<li aria-current="page">Laptop</li>', false);
    }

    public function test_categories_have_their_own_address_and_old_links_move_there(): void
    {
        $laptops = Category::factory()->create(['name' => 'Gaming Laptops', 'slug' => null]);
        $acme = Brand::factory()->create();
        ProductOptimized::factory()->create(['name' => 'Rog Strix', 'category_id' => $laptops->id, 'brand_id' => $acme->id]);
        ProductOptimized::factory()->create(['name' => 'Coffee Mug']);

        $this->assertSame('gaming-laptops', $laptops->fresh()->slug, 'Made from the name');
        $this->assertSame(['Rog Strix'], $this->listed('/category/gaming-laptops'));
        $this->assertStringContainsString('action="' . url('/category/gaming-laptops') . '"', $this->get('/category/gaming-laptops')->getContent(), 'The filters stay on the category page');

        // The shop's old ?category=id links, with their other filters
        $this->get('/shop?category=' . $laptops->id . '&brand=' . $acme->id . '&sort=price_low')
            ->assertStatus(301)
            ->assertRedirect(url('/category/gaming-laptops') . '?brand=' . $acme->id . '&sort=price_low');
        $this->get('/category/' . $laptops->id)->assertStatus(301)->assertRedirect(url('/category/gaming-laptops'));

        // A changed address leaves a redirect behind
        $laptops->forceFill(['slug' => 'laptops'])->save();
        $this->get('/category/gaming-laptops')->assertStatus(301)->assertRedirect(url('/category/laptops'));

        $this->get('/category/no-such-thing')->assertNotFound();
        $laptops->update(['status' => false]);
        $this->get('/category/laptops')->assertNotFound();
        $this->get('/category/gaming-laptops')->assertNotFound();
    }

    public function test_cards_show_key_specs_and_a_compare_button_without_made_up_ratings(): void
    {
        $product = ProductOptimized::factory()->create([
            'base_price' => 55000,
            'specs' => ['Display' => '14" FHD', 'Processor' => 'Ryzen 5', 'Memory' => '16GB', 'Storage' => '512GB SSD', 'Battery' => '60Wh'],
        ]);

        $html = $this->get('/shop')->assertOk()->getContent();
        $results = Str::betweenFirst($html, 'class="shop-results"', '</section>');
        $card = Str::betweenFirst($results, '<article', '</article>');

        preg_match_all('#<li><span class="sf-card-spec-label">(.*?):</span> (.*?)</li>#', Str::betweenFirst($card, 'class="sf-card-specs">', '</ul>'), $specs);
        $this->assertSame(['Display', 'Processor', 'Memory', 'Storage'], $specs[1], 'The first four specs, labelled');
        $this->assertSame(['14&quot; FHD', 'Ryzen 5', '16GB', '512GB SSD'], $specs[2]);
        $this->assertStringContainsString('data-compare-id="' . $product->id . '"', $card);
        $this->assertStringContainsString('data-compare-price="55000"', $card);
        $this->assertStringContainsString('data-cart-url="' . route('cart.add') . '"', $card);

        $this->assertStringNotContainsString('fa-star', $results);
        $this->assertStringNotContainsString('href=""', $html);
        $this->assertStringNotContainsString('BDT ', $results);
    }

    public function test_brands_prices_and_attributes_narrow_the_list_together(): void
    {
        $category = Category::factory()->create();
        [$acme, $zeta, $other] = Brand::factory()->count(3)->create();
        $ram = AttributeOptimized::create(['name' => 'RAM', 'type' => 'select', 'filterable' => true, 'status' => true]);
        foreach ([['Cheap', $acme, 30000, '8GB'], ['Middle', $zeta, 60000, '16GB'], ['Dear', $acme, 150000, '16GB'], ['Stranger', $other, 60000, '16GB']] as [$name, $brand, $price, $memory]) {
            $product = ProductOptimized::factory()->create(['name' => $name, 'category_id' => $category->id, 'brand_id' => $brand->id, 'base_price' => $price]);
            ProductAttributeOptimized::create(['product_id' => $product->id, 'attribute_id' => $ram->id, 'value' => $memory]);
        }

        $this->assertEqualsCanonicalizing(['Cheap', 'Middle', 'Dear'], $this->listed(route('shop', ['brand' => [$acme->id, $zeta->id]])), 'Either brand');
        $this->assertSame(['Middle'], $this->listed(route('shop', ['brand' => $zeta->id])), 'A single brand from a product page link');
        $this->assertEqualsCanonicalizing(['Middle', 'Dear'], $this->listed(route('shop', ['brand' => [$acme->id, $zeta->id], 'min_price' => 50000, 'max_price' => 200000])));
        $this->assertSame(['Cheap'], $this->listed(route('shop', ['attributes' => [$ram->id => ['8GB']]])));
        $this->assertEqualsCanonicalizing(['Cheap', 'Middle', 'Dear', 'Stranger'], $this->listed(route('shop', ['attributes' => [$ram->id => ['8GB', '16GB']]])), 'Either value');
        $this->assertSame([], $this->listed(route('shop', ['brand' => $acme->id, 'max_price' => 100000, 'attributes' => [$ram->id => ['16GB']]])));

        // The chosen boxes stay ticked
        $html = $this->get(route('shop', ['brand' => [$acme->id], 'attributes' => [$ram->id => ['16GB']]]))->getContent();
        $this->assertMatchesRegularExpression('#name="brand\[\]" value="' . $acme->id . '" checked>#', $html);
        $this->assertMatchesRegularExpression('#name="attributes\[' . $ram->id . '\]\[\]" value="16GB" checked>#', $html);
    }

    public function test_filters_in_use_are_chips_that_each_remove_one_filter(): void
    {
        $category = Category::factory()->create(['name' => 'Laptop']);
        $acme = Brand::factory()->create(['name' => 'Acme']);
        $zeta = Brand::factory()->create(['name' => 'Zeta']);
        $ram = AttributeOptimized::create(['name' => 'RAM', 'type' => 'select', 'filterable' => true, 'status' => true]);
        $product = ProductOptimized::factory()->create(['category_id' => $category->id, 'brand_id' => $acme->id, 'base_price' => 60000]);
        ProductAttributeOptimized::create(['product_id' => $product->id, 'attribute_id' => $ram->id, 'value' => '16GB']);

        $html = $this->get(route('shop.category', [
            'category' => $category, 'brand' => [$acme->id, $zeta->id], 'attributes' => [$ram->id => ['16GB']],
            'min_price' => 50000, 'in_stock' => 1, 'sort' => 'price_low',
        ]))->assertOk()->getContent();

        preg_match_all('#<a class="shop-chip" href="([^"]*)"><span class="sr-only">Remove filter: </span>(.*?)<i class#', $html, $chips);
        $this->assertSame(['Acme', 'Zeta', 'From ৳50,000', 'In stock', 'RAM: 16GB'], array_map('strip_tags', $chips[2]));
        $this->assertStringContainsString('From <span class="price-sign">৳</span>50,000', $chips[2][2], 'The taka sign gets the price font');

        $removeAcme = html_entity_decode($chips[1][0]);
        $this->assertSame(route('shop.category', [
            'category' => $category, 'brand' => $zeta->id, 'attributes' => [$ram->id => ['16GB']],
            'min_price' => 50000, 'in_stock' => 1, 'sort' => 'price_low',
        ]), $removeAcme, 'Removing one brand keeps the other and every other filter');
        $this->assertStringNotContainsString('attributes', html_entity_decode($chips[1][4]), 'Removing the last RAM value drops the attribute');

        // Clear all keeps the category and the sort
        $clear = route('shop.category', ['category' => $category, 'sort' => 'price_low']);
        $this->assertStringContainsString('<a class="shop-chips-clear" href="' . e($clear) . '">Clear all</a>', $html);
        $this->assertStringContainsString('<span class="shop-filter-count">5<', $html);

        // Moving to another category keeps the sort and the ticks for stock, but not the brands, prices or attributes
        $categories = Str::betweenFirst($html, '<span class="shop-facet-name">Category</span>', '</a>');
        $this->assertStringContainsString('<span class="shop-facet-value">Laptop</span>', $categories, 'The open category is named, and its list folded');
        $this->assertStringContainsString('href="' . e(route('shop', ['in_stock' => 1, 'sort' => 'price_low'])) . '"', $categories);
        $this->assertFalse($this->categoryFacetOpen($html));
        $this->assertTrue($this->categoryFacetOpen($this->get('/shop')->getContent()), 'Without a category the list is open');

        // Without filters there are no chips
        $this->assertStringNotContainsString('class="shop-chips"', $this->get(route('shop.category', $category))->getContent());
    }

    public function test_the_brand_list_follows_the_category(): void
    {
        $laptops = Category::factory()->create();
        $phones = Category::factory()->create();
        $apple = Brand::factory()->create(['name' => 'Apple']);
        $dell = Brand::factory()->create(['name' => 'Dell']);
        $samsung = Brand::factory()->create(['name' => 'Samsung']);
        ProductOptimized::factory()->count(2)->create(['category_id' => $laptops->id, 'brand_id' => $apple->id]);
        ProductOptimized::factory()->create(['category_id' => $laptops->id, 'brand_id' => $dell->id]);
        ProductOptimized::factory()->create(['category_id' => $phones->id, 'brand_id' => $samsung->id]);
        ProductOptimized::factory()->create(['category_id' => $phones->id, 'brand_id' => $apple->id]);

        $this->assertSame(['Apple' => '3', 'Dell' => '1', 'Samsung' => '1'], $this->brandList('/shop'));
        $this->assertSame(['Apple' => '2', 'Dell' => '1'], $this->brandList(route('shop.category', $laptops)));
        // A brand that has nothing here but was chosen stays listed, so it can be unticked
        $this->assertSame(['Apple' => '2', 'Dell' => '1', 'Samsung' => '0'], $this->brandList(route('shop.category', ['category' => $laptops, 'brand' => $samsung->id])));
    }

    public function test_the_summary_and_price_hints_give_the_range(): void
    {
        $category = Category::factory()->create();
        foreach ([1200, 45000, 99999] as $price) {
            ProductOptimized::factory()->create(['category_id' => $category->id, 'base_price' => $price]);
        }

        $html = $this->get(route('shop.category', $category))->getContent();
        $this->assertSame('3 products · ৳Tk 1,200 to ৳Tk 99,999', $this->summary($html));
        $this->assertStringContainsString('placeholder="1,200"', $html);
        $this->assertStringContainsString('placeholder="99,999"', $html);

        $html = $this->get(route('shop.category', ['category' => $category, 'max_price' => 50000]))->getContent();
        $this->assertSame('2 products · ৳Tk 1,200 to ৳Tk 45,000', $this->summary($html));
        $this->assertStringContainsString('name="max_price" value="50000"', $html);
    }

    public function test_sorting(): void
    {
        foreach (['Mid' => 200, 'Low' => 100, 'High' => 300] as $name => $price) {
            ProductOptimized::factory()->create(['name' => $name, 'base_price' => $price]);
        }

        $this->assertSame(['Low', 'Mid', 'High'], $this->listed('/shop?sort=price_low'));
        $this->assertSame(['High', 'Mid', 'Low'], $this->listed('/shop?sort=price_high'));
        $this->assertSame(['High', 'Low', 'Mid'], $this->listed('/shop?sort=name'));
        $this->assertSame(['High', 'Low', 'Mid'], $this->listed('/shop?sort=nonsense'), 'Unknown sorts fall back to newest');
        $this->assertStringContainsString('<option value="price_high" selected>', $this->get('/shop?sort=price_high')->getContent());
    }

    public function test_deals_open_with_the_biggest_saving_on_deal_cards(): void
    {
        ProductOptimized::factory()->onSale(150)->create(['name' => 'Saves 50', 'base_price' => 100]);
        ProductOptimized::factory()->onSale(600)->create(['name' => 'Saves 500', 'base_price' => 100]);
        ProductOptimized::factory()->onSale(300)->create(['name' => 'Saves 200', 'base_price' => 100]);

        $this->assertSame(['Saves 500', 'Saves 200', 'Saves 50'], $this->listed('/shop?sale=1'));
        $html = $this->get('/shop?sale=1')->getContent();
        $this->assertStringContainsString('sf-card is-deal', $html);
        $this->assertStringContainsString('<option value="saving" selected>', $html);
    }

    public function test_pages_hold_24_products_in_a_stable_order(): void
    {
        // Seeded in the same second: only the id tells them apart
        $products = ProductOptimized::factory()->count(30)->create(['created_at' => now()->subDay()]);
        $expected = $products->sortByDesc('id')->pluck('name')->values()->all();

        $first = $this->get('/shop')->assertSee('Showing 1–24 of 30 products')->getContent();
        $this->assertSame(array_slice($expected, 0, 24), $this->names($first));
        $this->assertStringContainsString('<span class="sf-pager-page" aria-current="page">1</span>', $first);
        $this->assertStringContainsString('rel="next"', $first);

        $second = $this->get('/shop?page=2')->assertSee('Showing 25–30 of 30 products')->getContent();
        $this->assertSame(array_slice($expected, 24), $this->names($second));
    }

    public function test_empty_results_offer_a_way_back(): void
    {
        $category = Category::factory()->create(['name' => 'Laptop']);
        ProductOptimized::factory()->create(['category_id' => $category->id, 'base_price' => 60000]);

        $this->get(route('shop.category', ['category' => $category, 'max_price' => 100]))
            ->assertOk()
            ->assertSee('No products match these filters')
            ->assertSee('<a class="sf-btn" href="' . e(route('shop.category', $category)) . '">Clear all filters</a>', false);

        $this->get('/shop?search=zzzz')
            ->assertOk()
            ->assertSee('Results for “zzzz”')
            ->assertSee('No products found')
            ->assertSee('href="' . e(route('shop.category', $category)) . '">Laptop</a>', false)
            ->assertDontSee('id="shop-sort"', false);
    }

    public function test_the_query_string_is_read_forgivingly(): void
    {
        $filters = ShopFilters::fromRequest(Request::create('/shop', 'GET', [
            'search' => '  galaxy  ',
            'category' => 'abc',
            'brand' => ['3', 'x', '3', '-1', ['nested']],
            'attributes' => ['2' => ['16GB', '', '16GB'], 'bad' => ['x'], '5' => 'Black'],
            'min_price' => '90,000',
            'max_price' => '10000',
            'sort' => 'bogus',
            'sale' => '1',
        ]));

        $this->assertSame('galaxy', $filters->search);
        $this->assertNull($filters->category);
        $this->assertSame([3], $filters->brands);
        $this->assertSame([2 => ['16GB'], 5 => ['Black']], $filters->attributes);
        $this->assertSame([10000, 90000], [$filters->minPrice, $filters->maxPrice], 'Min and max swap when reversed');
        $this->assertNull($filters->sort);
        $this->assertSame('saving', $filters->sortKey());
        $this->assertNull(ShopFilters::fromRequest(Request::create('/shop', 'GET', ['sort' => 'latest']))->sort, 'The default order stays out of URLs');

        $this->get('/shop?search[]=x&category[]=1&sort[]=a&min_price[]=5&brand[][]=2')->assertOk();
    }

    private function heading(string $uri): string
    {
        return html_entity_decode(Str::betweenFirst($this->get($uri)->assertOk()->getContent(), 'id="shop-title">', '</h1>'));
    }

    /**
     * Product names on the page, in order.
     */
    private function listed(string $uri): array
    {
        return $this->names($this->get($uri)->assertOk()->getContent());
    }

    private function names(string $html): array
    {
        preg_match_all('#class="sf-card-title"><a href="[^"]*">([^<]*)</a>#', $html, $matches);

        return array_map('html_entity_decode', $matches[1]);
    }

    /**
     * The Brand filter as name => count.
     */
    private function brandList(string $uri): array
    {
        $section = Str::betweenFirst($this->get($uri)->assertOk()->getContent(), '<summary>Brand</summary>', '</details>');
        preg_match_all('#<span class="shop-option-label">([^<]*)</span>\s*<span class="shop-count">(\d+)</span>#', $section, $matches);

        return array_combine($matches[1], $matches[2]);
    }

    private function categoryFacetOpen(string $html): bool
    {
        preg_match('#<details class="shop-facet"([^>]*)>\s*<summary>\s*<span class="shop-facet-name">Category#', $html, $match);

        return str_contains($match[1], 'open');
    }

    private function summary(string $html): string
    {
        return trim(preg_replace('/\s+/', ' ', strip_tags(Str::betweenFirst($html, 'class="shop-summary">', '</p>'))));
    }
}
