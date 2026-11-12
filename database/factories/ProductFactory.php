<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['rings', 'necklaces', 'bracelets', 'earrings', 'solitaires'];

        $metalTypes = [
            '18KT Yellow Gold',
            '18KT White Gold',
            '18KT Rose Gold',
            '22KT Yellow Gold',
            'Platinum 950',
            '14KT Yellow Gold',
            '14KT White Gold',
        ];

        $prefixes = ['Royal', 'Empress', 'Celestial', 'Imperial', 'Divine', 'Eternal', 'Majestic', 'Regal', 'Opulent', 'Radiant'];
        $stones = ['Diamond', 'Ruby', 'Emerald', 'Sapphire', 'Pearl', 'Tanzanite', 'Amethyst'];
        $styles = ['Solitaire', 'Cluster', 'Halo', 'Pavé', 'Channel Set', 'Bezel Set', 'Vintage', 'Art Deco'];

        $category = fake()->randomElement($categories);
        $categoryLabel = ucfirst(rtrim($category, 's'));

        $title = fake()->randomElement($prefixes)
            . ' ' . fake()->randomElement($stones)
            . ' ' . fake()->randomElement($styles)
            . ' ' . $categoryLabel;

        $grossWeight = fake()->randomFloat(2, 2.5, 45.0);
        $netWeight = round($grossWeight * fake()->randomFloat(2, 0.70, 0.92), 2);
        $price = fake()->randomFloat(2, 25000, 5000000);
        $hasSalePrice = fake()->boolean(30);

        $certifications = ['GIA Certified', 'IGI Certified', 'SGL Certified', 'GIA & IGI Certified', null];
        $clarities = ['IF', 'VVS1', 'VVS2', 'VS1', 'VS2', 'SI1', 'SI2'];
        $cuts = ['Excellent', 'Very Good', 'Good', 'Ideal'];
        $stoneTypes = ['Natural Diamond', 'Lab Grown Diamond', 'Natural Ruby', 'Natural Emerald', 'Natural Sapphire', 'Moissanite'];

        return [
            'sku' => 'VRZ-' . strtoupper(Str::random(6)),
            'title' => $title,
            'category' => $category,
            'metal_type' => fake()->randomElement($metalTypes),
            'gross_weight_grams' => $grossWeight,
            'net_weight_grams' => $netWeight,
            'price' => $price,
            'gst_percent' => 3.00,
            'discount_percent' => 0.00,
            'sale_price' => $hasSalePrice ? round($price * fake()->randomFloat(2, 0.80, 0.95), 2) : null,
            'stock_quantity' => fake()->numberBetween(0, 15),
            'is_featured' => fake()->boolean(25),
            'bis_hallmarked' => true,
            'certification' => fake()->randomElement($certifications),
            'description' => 'Exquisitely crafted ' . strtolower($title) . ' featuring premium quality stones set in ' . fake()->randomElement($metalTypes) . '. This piece is BIS hallmarked and comes with a certificate of authenticity. Perfect for special occasions and everyday luxury.',
            'specs' => [
                'stone' => fake()->randomElement($stoneTypes),
                'clarity' => fake()->randomElement($clarities),
                'cut' => fake()->randomElement($cuts),
                'carat' => (string) fake()->randomFloat(2, 0.25, 5.0),
                'color' => fake()->randomElement(['D', 'E', 'F', 'G', 'H', 'I']),
                'setting' => fake()->randomElement($styles),
            ],
            'images' => [
                '/images/products/placeholder-1.jpg',
                '/images/products/placeholder-2.jpg',
                '/images/products/placeholder-3.jpg',
                '/images/products/placeholder-4.jpg',
            ],
        ];
    }

    /**
     * Indicate that the product is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate the product is in a specific category.
     */
    public function category(string $category): static
    {
        return $this->state(fn (array $attributes): array => [
            'category' => $category,
        ]);
    }

    /**
     * Indicate the product is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes): array => [
            'stock_quantity' => 0,
        ]);
    }
}
