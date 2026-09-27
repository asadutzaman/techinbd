<?php

namespace Tests\Feature;

use App\Models\AttributeOptimized;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductAttributeOptimized;
use App\Models\ProductImageOptimized;
use App\Models\ProductOptimized;
use App\Models\ProductVariantOptimized;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_gallery_shows_every_image_main_first_with_thumbnails_and_zoom(): void
    {
        $product = ProductOptimized::factory()->create();
        $this->image($product, 'side', sortOrder: 0);
        $this->image($product, 'front', sortOrder: 1, main: true);
        $this->image($product, 'back', sortOrder: 2);

        $html = $this->page($product);
        $track = Str::betweenFirst($html, 'data-gallery-track', '</ul>');

        $this->assertMatchesRegularExpression('#large/front\.webp.*large/side\.webp.*large/back\.webp#s', $track, 'The main image leads');
        $this->assertStringContainsString('srcset="' . asset('storage/products/thumbs/front.webp') . ' 600w, ' . asset('storage/products/large/front.webp') . ' 1200w"', $track);
        $this->assertSame(1, substr_count($track, 'fetchpriority="high"'));
        $this->assertSame(2, substr_count($track, 'loading="lazy"'));
        $this->assertSame(3, substr_count($html, 'data-thumb="'));
        $this->assertStringContainsString('data-gallery-prev', $html);
        $this->assertStringContainsString('/ 3</span>', $html);
        $this->assertStringContainsString('<dialog class="pdp-lightbox"', $html);
        $this->assertStringNotContainsString('storage/products/front.jpg', $html, 'The gallery never loads the original upload');
    }

    public function test_a_single_image_has_zoom_but_no_thumbnails_or_arrows(): void
    {
        $product = ProductOptimized::factory()->create();
        $this->image($product, 'only', main: true);

        $html = $this->page($product);

        $this->assertStringContainsString('data-zoom="0"', $html);
        $this->assertStringContainsString('<dialog class="pdp-lightbox"', $html);
        $this->assertStringNotContainsString('<ul class="pdp-thumbs"', $html);
        $this->assertStringNotContainsString('class="pdp-nav is-prev"', $html);
    }

    public function test_a_product_without_images_shows_a_placeholder(): void
    {
        $html = $this->page(ProductOptimized::factory()->create());

        $this->assertStringContainsString('No photo yet', $html);
        $this->assertStringNotContainsString('<dialog', $html);
    }

    public function test_no_made_up_reviews_ratings_or_dead_share_links(): void
    {
        $product = ProductOptimized::factory()->create(['category_id' => Category::factory()]);
        ProductOptimized::factory()->create(['category_id' => $product->category_id]);

        $this->get(route('product.detail', $product))
            ->assertOk()
            ->assertDontSee('Reviews')
            ->assertDontSee('John Doe')
            ->assertDontSee('Leave a review')
            ->assertDontSee('fa-star-half-alt')
            ->assertDontSee('far fa-star')
            ->assertDontSee('href=""', false);
    }

    public function test_specs_keep_their_order_in_storage(): void
    {
        // MySQL sorts the keys of a JSON object, so specs are stored as [label, value] pairs
        $product = ProductOptimized::factory()->create(['specs' => ['Zoom' => '5x', 'Audio' => 'Stereo']]);

        $this->assertSame('[["Zoom","5x"],["Audio","Stereo"]]', $product->getRawOriginal('specs'));
        $this->assertSame(['Zoom' => '5x', 'Audio' => 'Stereo'], $product->fresh()->specs);

        // Rows saved before as a label => value object still read
        ProductOptimized::whereKey($product->id)->update(['specs' => '{"Weight": "1 kg"}']);
        $this->assertSame(['Weight' => '1 kg'], $product->fresh()->specs);
    }

    public function test_specification_lists_specs_extra_attributes_and_general_details(): void
    {
        $product = ProductOptimized::factory()->create([
            'brand_id' => Brand::factory()->create(['name' => 'Acme']),
            'specs' => ['Display' => '14" IPS', 'RAM' => '16GB', 'Battery' => ''],
            'manufacturer_part_no' => 'AC-14',
            'sku' => 'SKU-14',
            'ean_upc' => '9410000000017',
            'weight' => 1.24,
            'warranty' => '2 Years Warranty',
        ]);
        $this->attribute($product, 'RAM', '16GB');
        $this->attribute($product, 'Memory', '16gb');
        $this->attribute($product, 'Color', 'Silver');

        $table = Str::betweenFirst($this->page($product), 'class="pdp-spec"', '</table>');

        $this->assertMatchesRegularExpression('#scope="colgroup">Specifications</th>.*Display</th>\s*<td>14&quot; IPS</td>#s', $table);
        $this->assertSame(1, substr_count($table, '>RAM</th>'), 'An attribute the specs already list appears once');
        $this->assertStringNotContainsString('>Memory</th>', $table, 'Nor does one whose value the specs already give');
        $this->assertMatchesRegularExpression('#>Color</th>\s*<td>Silver</td>#', $table);
        $this->assertStringNotContainsString('Battery', $table, 'Empty specs are left out');
        $this->assertMatchesRegularExpression('#scope="colgroup">General</th>.*>Brand</th>\s*<td>Acme</td>.*>Model</th>\s*<td>AC-14</td>.*>Weight</th>\s*<td>1.24 kg</td>.*>Warranty</th>\s*<td>2 Years Warranty</td>#s', $table);
    }

    public function test_key_features_are_the_first_four_specs(): void
    {
        $product = ProductOptimized::factory()->create(['specs' => ['A' => 'One', 'B' => 'Two', 'C' => 'Three', 'D' => 'Four', 'E' => 'Five']]);

        $features = Str::betweenFirst($this->page($product), 'class="pdp-features"', '</ul>');

        $this->assertMatchesRegularExpression('#<li>One</li>\s*<li>Two</li>\s*<li>Three</li>\s*<li>Four</li>#', $features);
        $this->assertStringNotContainsString('Five', $features);
    }

    public function test_description_section_shows_the_prose_and_is_hidden_when_empty(): void
    {
        $withText = ProductOptimized::factory()->create(['description' => '<p>A <strong>fast</strong> laptop.</p>']);
        $html = $this->page($withText);
        $this->assertStringContainsString('<div class="pdp-prose"><p>A <strong>fast</strong> laptop.</p></div>', $html);
        $this->assertStringContainsString('href="#description"', $html);

        $empty = ProductOptimized::factory()->create(['description' => '<p> </p>']);
        $html = $this->page($empty);
        $this->assertStringNotContainsString('id="description"', $html);
        $this->assertStringNotContainsString('href="#description"', $html);
    }

    public function test_a_marked_down_price_shows_the_old_price_and_the_saving(): void
    {
        $product = ProductOptimized::factory()->onSale(2000)->create(['base_price' => 1500]);

        $box = Str::betweenFirst($this->page($product), 'class="pdp-box pdp-price-box"', 'class="pdp-box"');

        $this->assertMatchesRegularExpression('#data-price-now[^>]*>.*1,500</span>#', $box);
        $this->assertMatchesRegularExpression('#data-price-was\s*>.*2,000</span>#', $box);
        $this->assertMatchesRegularExpression('#data-price-save\s*>Save .*500</span>#', $box);
    }

    public function test_options_show_their_prices_and_sold_out_ones_are_disabled(): void
    {
        $product = ProductOptimized::factory()->create(['base_price' => 1000, 'manage_stock' => true]);
        $default = $this->option($product, '8GB', null, stock: 4, default: true);
        $this->option($product, '16GB', 1400, stock: 2);
        $soldOut = $this->option($product, '32GB', 1900, stock: 0);

        $html = $this->page($product);
        $options = Str::betweenFirst($html, 'role="radiogroup"', '</div>');

        $this->assertSame(3, substr_count($options, 'role="radio"'));
        $this->assertMatchesRegularExpression('#aria-checked="true"[^>]*data-variant="' . $default->id . '"#', $options);
        $this->assertStringContainsString('1,400', $options);
        $this->assertMatchesRegularExpression('#data-variant="' . $soldOut->id . '"[^>]*\s+disabled\s*>#', $options);
        $this->assertStringContainsString('name="variant_id" value="' . $default->id . '"', $html);
    }

    public function test_a_lone_standard_variant_is_not_offered_as_an_option(): void
    {
        $html = $this->page(ProductOptimized::factory()->onSale(2000)->create());

        $this->assertStringNotContainsString('role="radiogroup"', $html);
        $this->assertStringNotContainsString('name="variant_id"', $html);
    }

    public function test_out_of_stock_products_cannot_be_added_to_the_cart(): void
    {
        $html = $this->page(ProductOptimized::factory()->create(['stock_status' => 'out_of_stock']));

        $this->assertMatchesRegularExpression('#class="pdp-cart-btn"\s+disabled\s*>\s*Out of stock#', $html);
        $this->assertStringContainsString('Out of stock</li>', $html);
    }

    public function test_related_products_are_active_from_the_same_category(): void
    {
        $category = Category::factory()->create();
        $product = ProductOptimized::factory()->create(['category_id' => $category->id, 'name' => 'Main Laptop']);
        ProductOptimized::factory()->create(['category_id' => $category->id, 'name' => 'Sibling Laptop']);
        ProductOptimized::factory()->inactive()->create(['category_id' => $category->id, 'name' => 'Hidden Laptop']);
        ProductOptimized::factory()->create(['name' => 'Unrelated Kettle']);

        $related = Str::betweenFirst($this->page($product), 'class="pdp-related"', '</aside>');

        $this->assertStringContainsString('Sibling Laptop', $related);
        $this->assertStringNotContainsString('Main Laptop', $related);
        $this->assertStringNotContainsString('Hidden Laptop', $related);
        $this->assertStringNotContainsString('Unrelated Kettle', $related);
        $this->assertStringContainsString('href="' . route('shop', ['category' => $category->id]) . '"', $related);
    }

    public function test_search_engines_get_the_seo_title_and_structured_data(): void
    {
        $product = ProductOptimized::factory()->create([
            'name' => 'Acme Laptop',
            'base_price' => 55000,
            'meta_title' => 'Acme Laptop Price in Bangladesh | MultiShop',
            'meta_description' => 'A light laptop.',
            'sku' => 'ACME-1',
        ]);

        $html = $this->page($product);
        $json = json_decode(Str::betweenFirst($html, '<script type="application/ld+json">', '</script>'), true);

        $this->assertStringContainsString('<title>Acme Laptop Price in Bangladesh | MultiShop</title>', $html);
        $this->assertStringContainsString('<meta name="description" content="A light laptop.">', $html);
        $this->assertSame('Product', $json['@type']);
        $this->assertSame('ACME-1', $json['sku']);
        $this->assertSame('55000.00', $json['offers']['price']);
        $this->assertSame('BDT', $json['offers']['priceCurrency']);
        $this->assertSame('https://schema.org/InStock', $json['offers']['availability']);
    }

    public function test_inactive_products_are_not_found(): void
    {
        $this->get(route('product.detail', ProductOptimized::factory()->inactive()->create()))->assertNotFound();
    }

    private function page(ProductOptimized $product): string
    {
        return $this->get(route('product.detail', $product))->assertOk()->getContent();
    }

    private function image(ProductOptimized $product, string $name, int $sortOrder = 0, bool $main = false): ProductImageOptimized
    {
        return ProductImageOptimized::create([
            'product_id' => $product->id,
            'url' => "products/{$name}.jpg",
            'thumb_url' => "products/thumbs/{$name}.webp",
            'large_url' => "products/large/{$name}.webp",
            'alt_text' => ucfirst($name),
            'sort_order' => $sortOrder,
            'is_main' => $main,
        ]);
    }

    private function attribute(ProductOptimized $product, string $name, string $value): void
    {
        $attribute = AttributeOptimized::firstOrCreate(['name' => $name], ['type' => 'select', 'filterable' => true, 'status' => true]);
        ProductAttributeOptimized::create(['product_id' => $product->id, 'attribute_id' => $attribute->id, 'value' => $value]);
    }

    private function option(ProductOptimized $product, string $name, ?float $price, int $stock, bool $default = false): ProductVariantOptimized
    {
        $option = new ProductVariantOptimized(['name' => $name, 'price' => $price, 'stock' => $stock, 'is_default' => $default, 'manage_stock' => true]);
        $option->product()->associate($product);
        $option->save();

        return $option;
    }
}
