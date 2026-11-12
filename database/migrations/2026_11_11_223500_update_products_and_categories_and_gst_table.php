<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('gst_percent', 5, 2)->default(0.00)->after('price');
            $table->decimal('discount_percent', 5, 2)->default(0.00)->after('gst_percent');
            $table->decimal('final_price', 12, 2)->nullable()->after('discount_percent');
            $table->boolean('is_warranty_eligible')->default(true)->after('bis_hallmarked');
            $table->unsignedInteger('warranty_period_months')->default(12)->after('is_warranty_eligible');
            $table->text('warranty_coverage')->nullable()->after('warranty_period_months');
            $table->text('warranty_exclusions')->nullable()->after('warranty_coverage');
            $table->text('warranty_remedies')->nullable()->after('warranty_exclusions');
        });

        // Migrate existing product data safely
        $products = DB::table('products')->get();

        foreach ($products as $prod) {
            $cat = strtolower((string) $prod->category);
            $newCat = match ($cat) {
                'rings', 'necklaces', 'bracelets', 'earrings', 'solitaires', 'jewelry' => 'jewelry',
                'couture', 'clothes', 'clothing' => 'clothing',
                'watches', 'watch' => 'watches',
                'footwear', 'shoes' => 'footwear',
                default => 'jewelry',
            };

            // Existing GST: 3% for jewellery, 12% for couture/clothing
            $gst = ($newCat === 'clothing') ? 12.00 : 3.00;

            // Preserve existing customer price exactly:
            $effectivePrice = (float) ($prod->sale_price ?? $prod->price);
            $origPrice = (float) $prod->price;

            $discountPercent = 0.00;
            if ($prod->sale_price && (float) $prod->sale_price < $origPrice && $origPrice > 0) {
                $discountPercent = round((($origPrice - (float) $prod->sale_price) / $origPrice) * 100, 2);
            }

            // Calculate pre-GST base price so that:
            // Discounted Price = Base * (1 - Discount/100)
            // Final Price = Discounted Price * (1 + GST/100) == Effective Price
            $discMultiplier = (1.0 - ($discountPercent / 100.0));
            if ($discMultiplier <= 0) {
                $discMultiplier = 1.0;
            }
            $gstMultiplier = (1.0 + ($gst / 100.0));
            $basePrice = round($effectivePrice / ($discMultiplier * $gstMultiplier), 2);

            DB::table('products')
                ->where('id', $prod->id)
                ->update([
                    'category' => $newCat,
                    'price' => $basePrice,
                    'gst_percent' => $gst,
                    'discount_percent' => $discountPercent,
                    'final_price' => $effectivePrice,
                    'is_warranty_eligible' => true,
                    'warranty_period_months' => ($newCat === 'jewelry' ? 60 : 12), // 5 yrs for jewellery, 1 yr for couture
                    'warranty_coverage' => 'Includes master prong tightening, surface ultrasonic cleaning, BIS hallmarked alloy warranty, and craftsmanship defect resolution.',
                    'warranty_exclusions' => 'Excludes accidental breakage, severe impact fractures, chemical exposure, or unauthorized third-party repairs.',
                    'warranty_remedies' => 'Complimentary vault refurbishment, repair, or replacement upon assay inspection.',
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'gst_percent',
                'discount_percent',
                'final_price',
                'is_warranty_eligible',
                'warranty_period_months',
                'warranty_coverage',
                'warranty_exclusions',
                'warranty_remedies',
            ]);
        });
    }
};
