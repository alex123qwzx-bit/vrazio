<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create categories table
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // 2. Create subcategories table
        if (! Schema::hasTable('subcategories')) {
            Schema::create('subcategories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
                $table->string('name');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['category_id', 'slug']);
                $table->index(['category_id', 'is_active', 'sort_order']);
            });
        }

        // 3. Seed authoritative categories
        $now = now();
        $categoriesData = [
            ['name' => 'Jewelry',  'slug' => 'jewelry',  'sort_order' => 1, 'description' => 'Fine handcrafted haute joaillerie, diamonds, and precious metals.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Clothing', 'slug' => 'clothing', 'sort_order' => 2, 'description' => 'Artisanal couture, pure silks, and heritage bridal apparel.',      'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Watches',  'slug' => 'watches',  'sort_order' => 3, 'description' => 'Horological timepieces, automatic calibres, and luxury watches.',  'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Footwear', 'slug' => 'footwear', 'sort_order' => 4, 'description' => 'Hand-burnished leather footwear, bespoke loafers, and formal shoes.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($categoriesData as $catData) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $catData['slug']],
                $catData
            );
        }

        // Fetch inserted category IDs
        $jewelryId  = DB::table('categories')->where('slug', 'jewelry')->value('id');
        $clothingId = DB::table('categories')->where('slug', 'clothing')->value('id');
        $watchesId  = DB::table('categories')->where('slug', 'watches')->value('id');
        $footwearId = DB::table('categories')->where('slug', 'footwear')->value('id');

        // 4. Seed initial subcategories under authoritative categories
        $subcategories = [
            // Jewelry subcategories
            ['category_id' => $jewelryId,  'name' => 'Rings',       'slug' => 'rings',       'sort_order' => 1],
            ['category_id' => $jewelryId,  'name' => 'Necklaces',   'slug' => 'necklaces',   'sort_order' => 2],
            ['category_id' => $jewelryId,  'name' => 'Bracelets',   'slug' => 'bracelets',   'sort_order' => 3],
            ['category_id' => $jewelryId,  'name' => 'Earrings',    'slug' => 'earrings',    'sort_order' => 4],
            ['category_id' => $jewelryId,  'name' => 'Solitaires',  'slug' => 'solitaires',  'sort_order' => 5],

            // Clothing subcategories
            ['category_id' => $clothingId, 'name' => 'Couture',     'slug' => 'couture',     'sort_order' => 1],
            ['category_id' => $clothingId, 'name' => 'Sarees',      'slug' => 'sarees',      'sort_order' => 2],
            ['category_id' => $clothingId, 'name' => 'Sherwanis',   'slug' => 'sherwanis',   'sort_order' => 3],

            // Watches subcategories
            ['category_id' => $watchesId,  'name' => 'Automatic',   'slug' => 'automatic',   'sort_order' => 1],
            ['category_id' => $watchesId,  'name' => 'Chronograph', 'slug' => 'chronograph', 'sort_order' => 2],

            // Footwear subcategories
            ['category_id' => $footwearId, 'name' => 'Oxfords',     'slug' => 'oxfords',     'sort_order' => 1],
            ['category_id' => $footwearId, 'name' => 'Loafers',     'slug' => 'loafers',     'sort_order' => 2],
        ];

        foreach ($subcategories as $sub) {
            if ($sub['category_id']) {
                DB::table('subcategories')->updateOrInsert(
                    ['category_id' => $sub['category_id'], 'slug' => $sub['slug']],
                    array_merge($sub, [
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])
                );
            }
        }

        // 5. Add category_id and subcategory_id to products table
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('category')->constrained('categories')->nullOnDelete();
            }
            if (! Schema::hasColumn('products', 'subcategory_id')) {
                $table->foreignId('subcategory_id')->nullable()->after('category_id')->constrained('subcategories')->nullOnDelete();
            }
        });

        // 6. Safely associate existing products with category_id and subcategory_id
        $allSubs = DB::table('subcategories')->get()->keyBy(fn ($item) => $item->category_id . '_' . $item->slug);

        $products = DB::table('products')->get();
        foreach ($products as $prod) {
            $catStr = strtolower(trim((string) $prod->category));
            $targetCatSlug = 'jewelry';
            $targetSubSlug = null;

            if (in_array($catStr, ['jewelry', 'rings', 'necklaces', 'bracelets', 'earrings', 'solitaires'], true)) {
                $targetCatSlug = 'jewelry';
                if ($catStr !== 'jewelry') {
                    $targetSubSlug = $catStr;
                }
            } elseif (in_array($catStr, ['clothing', 'couture', 'clothes'], true)) {
                $targetCatSlug = 'clothing';
                if ($catStr === 'couture') {
                    $targetSubSlug = 'couture';
                }
            } elseif (in_array($catStr, ['watches', 'watch'], true)) {
                $targetCatSlug = 'watches';
            } elseif (in_array($catStr, ['footwear', 'shoes'], true)) {
                $targetCatSlug = 'footwear';
            }

            $catId = match ($targetCatSlug) {
                'jewelry' => $jewelryId,
                'clothing' => $clothingId,
                'watches' => $watchesId,
                'footwear' => $footwearId,
                default => $jewelryId,
            };

            // Attempt title/tag detection if no subcategory matched directly
            if (! $targetSubSlug && $targetCatSlug === 'jewelry') {
                $titleLower = strtolower((string) $prod->title);
                $tagsLower = strtolower((string) $prod->tags);

                if (str_contains($titleLower, 'ring') && ! str_contains($titleLower, 'earring')) {
                    $targetSubSlug = 'rings';
                } elseif (str_contains($titleLower, 'necklace')) {
                    $targetSubSlug = 'necklaces';
                } elseif (str_contains($titleLower, 'bracelet')) {
                    $targetSubSlug = 'bracelets';
                } elseif (str_contains($titleLower, 'earring')) {
                    $targetSubSlug = 'earrings';
                } elseif (str_contains($titleLower, 'solitaire') || str_contains($tagsLower, 'solitaire')) {
                    $targetSubSlug = 'solitaires';
                }
            }

            $subId = null;
            if ($targetSubSlug && isset($allSubs[$catId . '_' . $targetSubSlug])) {
                $subId = $allSubs[$catId . '_' . $targetSubSlug]->id;
            }

            DB::table('products')->where('id', $prod->id)->update([
                'category' => $targetCatSlug, // Standardize parent string
                'category_id' => $catId,
                'subcategory_id' => $subId,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'subcategory_id')) {
                $table->dropForeign(['subcategory_id']);
                $table->dropColumn('subcategory_id');
            }
            if (Schema::hasColumn('products', 'category_id')) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            }
        });

        Schema::dropIfExists('subcategories');
        Schema::dropIfExists('categories');
    }
};
