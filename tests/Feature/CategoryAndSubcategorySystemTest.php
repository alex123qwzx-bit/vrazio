<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\SubcategoryResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryAndSubcategorySystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@vrazio.com',
            'role' => 'super_admin',
            'is_approved' => true,
            'password' => bcrypt('AdminPassword123!'),
        ]);
    }

    /**
     * Test that authoritative categories and initial subcategories exist.
     */
    public function test_categories_and_subcategories_database_structure(): void
    {
        $jewelry = Category::where('slug', 'jewelry')->first();
        $this->assertNotNull($jewelry);
        $this->assertEquals('Jewelry', $jewelry->name);

        $subcategories = $jewelry->subcategories;
        $this->assertGreaterThanOrEqual(5, $subcategories->count());

        $rings = $subcategories->firstWhere('slug', 'rings');
        $this->assertNotNull($rings);
        $this->assertEquals($jewelry->id, $rings->category_id);
    }

    /**
     * Test that product belongs to category and subcategory.
     */
    public function test_product_category_and_subcategory_relationship(): void
    {
        $jewelry = Category::where('slug', 'jewelry')->first();
        $rings = Subcategory::where('category_id', $jewelry->id)->where('slug', 'rings')->first();

        $product = Product::create([
            'sku' => 'VRZ-TEST-RING-01',
            'title' => 'Solitaire Diamond Ring 18KT',
            'category' => 'jewelry',
            'category_id' => $jewelry->id,
            'subcategory_id' => $rings->id,
            'price' => 50000.0,
            'gst_percent' => 3.0,
            'discount_percent' => 0.0,
            'stock_quantity' => 10,
        ]);

        $this->assertEquals($jewelry->id, $product->categoryRelation->id);
        $this->assertEquals('Rings', $product->subcategory->name);
        $this->assertEquals(51500.0, (float) $product->final_price);

        // Verify product without subcategory works seamlessly
        $productNoSub = Product::create([
            'sku' => 'VRZ-TEST-NOSUB-01',
            'title' => 'Generic Jewelry Piece',
            'category' => 'jewelry',
            'category_id' => $jewelry->id,
            'subcategory_id' => null,
            'price' => 10000.0,
            'gst_percent' => 3.0,
            'discount_percent' => 0.0,
            'stock_quantity' => 5,
        ]);

        $this->assertNull($productNoSub->subcategory);
        $this->assertEquals('jewelry', $productNoSub->category);
    }

    /**
     * Test admin can access Category and Subcategory resources in Filament.
     */
    public function test_admin_can_access_category_and_subcategory_resources(): void
    {
        $this->actingAs($this->admin);

        $responseCat = $this->get(CategoryResource::getUrl('index'));
        $responseCat->assertSuccessful();

        $responseSub = $this->get(SubcategoryResource::getUrl('index'));
        $responseSub->assertSuccessful();
    }

    /**
     * Test admin can create, edit and delete subcategories under a category.
     */
    public function test_admin_can_manage_subcategories(): void
    {
        $this->actingAs($this->admin);
        $jewelry = Category::where('slug', 'jewelry')->first();

        // 1. Create subcategory
        $newSub = Subcategory::create([
            'category_id' => $jewelry->id,
            'name' => 'Anklets',
            'slug' => 'anklets',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $this->assertDatabaseHas('subcategories', [
            'id' => $newSub->id,
            'name' => 'Anklets',
            'slug' => 'anklets',
            'category_id' => $jewelry->id,
        ]);

        // 2. Edit subcategory
        $newSub->update([
            'name' => 'Gold Anklets & Charms',
        ]);

        $this->assertEquals('Gold Anklets & Charms', $newSub->fresh()->name);

        // 3. Delete subcategory
        $newSub->delete();
        $this->assertDatabaseMissing('subcategories', ['id' => $newSub->id]);
    }

    /**
     * Test storefront catalog filtering by category and subcategory.
     */
    public function test_storefront_filtering_by_category_and_subcategory(): void
    {
        $jewelry = Category::where('slug', 'jewelry')->first();
        $rings = Subcategory::where('category_id', $jewelry->id)->where('slug', 'rings')->first();
        $necklaces = Subcategory::where('category_id', $jewelry->id)->where('slug', 'necklaces')->first();

        Product::create([
            'sku' => 'VRZ-RING-ABC',
            'title' => 'Signature Diamond Ring',
            'category' => 'jewelry',
            'category_id' => $jewelry->id,
            'subcategory_id' => $rings->id,
            'price' => 25000.0,
            'gst_percent' => 3.0,
            'discount_percent' => 0.0,
            'stock_quantity' => 5,
        ]);

        Product::create([
            'sku' => 'VRZ-NECK-ABC',
            'title' => 'Heritage Emerald Necklace',
            'category' => 'jewelry',
            'category_id' => $jewelry->id,
            'subcategory_id' => $necklaces->id,
            'price' => 75000.0,
            'gst_percent' => 3.0,
            'discount_percent' => 0.0,
            'stock_quantity' => 5,
        ]);

        // Filter by category: should return both
        $jewelryProducts = Product::byCategory('jewelry')->get();
        $this->assertCount(2, $jewelryProducts);

        // Filter by subcategory 'rings': should return only the ring
        $ringProducts = Product::byCategory('rings')->get();
        $this->assertCount(1, $ringProducts);
        $this->assertEquals('VRZ-RING-ABC', $ringProducts->first()->sku);

        // Filter by subcategory 'necklaces': should return only the necklace
        $neckProducts = Product::byCategory('necklaces')->get();
        $this->assertCount(1, $neckProducts);
        $this->assertEquals('VRZ-NECK-ABC', $neckProducts->first()->sku);
    }

    /**
     * Test Filament ProductResource Create and Edit forms render category and subcategory successfully.
     */
    public function test_filament_product_create_and_edit_pages_restore_subcategory(): void
    {
        $this->actingAs($this->admin);

        $jewelry = Category::where('slug', 'jewelry')->first();
        $rings = Subcategory::where('category_id', $jewelry->id)->where('slug', 'rings')->first();

        // 1. Visit Create Product page
        $createRes = $this->get(ProductResource::getUrl('create'));
        $createRes->assertSuccessful();

        // 2. Create product with subcategory
        $product = Product::create([
            'sku' => 'VRZ-FILAMENT-TEST-01',
            'title' => 'Filament Test Diamond Solitaire',
            'category' => 'jewelry',
            'category_id' => $jewelry->id,
            'subcategory_id' => $rings->id,
            'price' => 30000.0,
            'gst_percent' => 3.0,
            'discount_percent' => 0.0,
            'stock_quantity' => 2,
        ]);

        // 3. Visit Edit Product page
        $editRes = $this->get(ProductResource::getUrl('edit', ['record' => $product]));
        $editRes->assertSuccessful();
        $editRes->assertSee('Product Category');
        $editRes->assertSee('Product Subcategory');

        // 4. Create product WITHOUT subcategory
        $productNoSub = Product::create([
            'sku' => 'VRZ-FILAMENT-NOSUB-02',
            'title' => 'Filament Test Plain Band',
            'category' => 'jewelry',
            'category_id' => $jewelry->id,
            'subcategory_id' => null,
            'price' => 15000.0,
            'gst_percent' => 3.0,
            'discount_percent' => 0.0,
            'stock_quantity' => 4,
        ]);

        $editNoSubRes = $this->get(ProductResource::getUrl('edit', ['record' => $productNoSub]));
        $editNoSubRes->assertSuccessful();
    }

    /**
     * Test Requirement 4 example workflow:
     * Admin creates Rings category with Gold Rings, Diamond Rings, Silver Rings, and Engagement Rings.
     * Selects Diamond Rings in product, saves, and filters dynamically.
     */
    public function test_example_workflow_admin_creates_category_with_subcategories_and_assigns_to_product(): void
    {
        $this->actingAs($this->admin);

        // 1. Admin creates custom category 'Rings'
        $ringsCat = Category::create([
            'name' => 'Cocktail Rings',
            'slug' => 'cocktail-rings',
            'description' => 'Fine cocktail rings and statement pieces.',
            'is_active' => true,
            'sort_order' => 5,
        ]);

        // 2. Admin adds multiple subcategories
        $subNames = ['Gold Rings', 'Diamond Rings', 'Silver Rings', 'Engagement Rings'];
        $createdSubs = [];
        foreach ($subNames as $name) {
            $createdSubs[$name] = Subcategory::create([
                'category_id' => $ringsCat->id,
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name),
                'is_active' => true,
            ]);
        }

        $this->assertCount(4, $ringsCat->subcategories);

        // 3. Admin creates product assigning category 'Cocktail Rings' and subcategory 'Diamond Rings'
        $diamondRingSub = $createdSubs['Diamond Rings'];
        $product = Product::create([
            'sku' => 'VRZ-COCKTAIL-DIA-01',
            'title' => 'Solitaire Diamond Cocktail Ring',
            'category_id' => $ringsCat->id,
            'subcategory_id' => $diamondRingSub->id,
            'price' => 85000.0,
            'gst_percent' => 3.0,
            'discount_percent' => 0.0,
            'stock_quantity' => 3,
        ]);

        // 4. Verify relations and dynamic resolution
        $this->assertEquals($ringsCat->id, $product->categoryRelation->id);
        $this->assertEquals('Cocktail Rings', $product->categoryRelation->name);
        $this->assertEquals('Diamond Rings', $product->subcategory->name);
        $this->assertEquals('cocktail-rings', $product->category); // Auto-synced string

        // 5. Test storefront query by subcategory slug
        $matchedProducts = Product::byCategory('diamond-rings')->get();
        $this->assertCount(1, $matchedProducts);
        $this->assertEquals('VRZ-COCKTAIL-DIA-01', $matchedProducts->first()->sku);

        // 6. Test storefront query by parent category slug
        $categoryProducts = Product::byCategory('cocktail-rings')->get();
        $this->assertCount(1, $categoryProducts);
        $this->assertEquals('VRZ-COCKTAIL-DIA-01', $categoryProducts->first()->sku);
    }
}
