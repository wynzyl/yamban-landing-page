<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed the storefront products.
     */
    public function run(): void
    {
        $products = [
            [
                'category' => 'athletic-jersey',
                'name' => 'Matchday Football Kit',
                'slug' => 'matchday-football-kit',
                'description' => 'A full sublimation kit for 11-a-side and 7-a-side squads. Designed with you, printed edge to edge, and cut from lightweight moisture-wicking mesh.',
                'features' => ['Full sublimation print', 'Jersey, shorts and socks', 'Names and numbers included'],
                'price_from' => '₱850',
                'minimum_order' => '10 sets',
                'image_path' => 'images/products/matchday-football-kit.jpg',
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'category' => 'athletic-jersey',
                'name' => 'Court Basketball Set',
                'slug' => 'court-basketball-set',
                'description' => 'Breathable jersey and shorts set built for fast breaks. Choose your colors, panel pattern and lettering, then approve a digital proof before printing.',
                'features' => ['Quick-dry birdseye fabric', 'Free design proof', 'Youth and adult sizes'],
                'price_from' => '₱750',
                'minimum_order' => '10 sets',
                'image_path' => 'images/products/court-basketball-set.jpg',
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'category' => 'athletic-jersey',
                'name' => 'Aero Cycling Jersey',
                'slug' => 'aero-cycling-jersey',
                'description' => 'A close-fitting cycling jersey with three rear pockets and a full-length zip. Available in club cuts and race cuts.',
                'features' => ['Rear cargo pockets', 'Flatlock seams', 'UV-protective fabric'],
                'price_from' => '₱1,250',
                'minimum_order' => '10 pcs',
                'image_path' => 'images/products/aero-cycling-jersey.jpg',
                'is_featured' => false,
                'sort_order' => 3,
            ],
            [
                'category' => 'corporate-uniforms',
                'name' => 'Everyday Uniform Tee',
                'slug' => 'everyday-uniform-tee',
                'description' => 'A soft, durable cotton tee for staff, events and company merch. Add a chest logo or a full back print — ideal for large teams on a practical budget.',
                'features' => ['Embroidered or printed logo', 'Soft cotton blend', 'Unisex sizing'],
                'price_from' => '₱450',
                'minimum_order' => '20 pcs',
                'image_path' => 'images/products/everyday-uniform-tee.jpg',
                'is_featured' => true,
                'sort_order' => 4,
            ],
            [
                'category' => 'corporate-uniforms',
                'name' => 'Office Dress Shirt',
                'slug' => 'office-dress-shirt',
                'description' => 'Crisp button-downs for office and hotel teams. Pick a standard size run or send us measurements for a tailored fit.',
                'features' => ['Wrinkle-resistant weave', 'Custom size run', 'Optional name embroidery'],
                'price_from' => '₱650',
                'minimum_order' => '15 pcs',
                'image_path' => 'images/products/office-dress-shirt.jpg',
                'is_featured' => false,
                'sort_order' => 5,
            ],
            [
                'category' => 'corporate-uniforms',
                'name' => 'Executive Long Sleeve',
                'slug' => 'executive-long-sleeve',
                'description' => 'A polished long-sleeve shirt for management and client-facing teams, finished with a subtle embroidered mark.',
                'features' => ['Premium cotton poplin', 'Tone-on-tone embroidery', 'Slim and regular fits'],
                'price_from' => '₱780',
                'minimum_order' => '15 pcs',
                'image_path' => 'images/products/executive-long-sleeve.jpg',
                'is_featured' => false,
                'sort_order' => 6,
            ],
            [
                'category' => 'longsleeves-for-riding',
                'name' => 'Riding Performance Longsleeve',
                'slug' => 'riding-performance-longsleeve',
                'description' => 'A long-sleeve riding top with stretch panels and a relaxed cut that fits over a base layer. Made for riders who spend the whole day in the saddle.',
                'features' => ['UPF 40+ fabric', 'Thumbhole cuffs', 'Reflective print option'],
                'price_from' => '₱950',
                'minimum_order' => '10 pcs',
                'image_path' => 'images/products/riding-performance-longsleeve.jpg',
                'is_featured' => true,
                'sort_order' => 7,
            ],
            [
                'category' => 'longsleeves-for-riding',
                'name' => 'Base Layer Riding Top',
                'slug' => 'base-layer-riding-top',
                'description' => 'A light, quick-drying base layer that keeps you cool under a jacket and warm on early starts. Club logos and rider names welcome.',
                'features' => ['Moisture-wicking knit', 'Flatlock stitching', 'Club and sponsor prints'],
                'price_from' => '₱680',
                'minimum_order' => '10 pcs',
                'image_path' => 'images/products/base-layer-riding-top.jpg',
                'is_featured' => false,
                'sort_order' => 8,
            ],
            [
                'category' => 'hoodies',
                'name' => 'Heavyweight Pullover Hoodie',
                'slug' => 'heavyweight-pullover-hoodie',
                'description' => 'A 380 GSM fleece pullover with a double-layer hood and kangaroo pocket. The one your team will wear long after the season ends.',
                'features' => ['380 GSM fleece', 'Embroidered or printed logo', 'Sizes XS to 3XL'],
                'price_from' => '₱950',
                'minimum_order' => '20 pcs',
                'image_path' => 'images/products/heavyweight-pullover-hoodie.jpg',
                'is_featured' => true,
                'sort_order' => 9,
            ],
            [
                'category' => 'hoodies',
                'name' => 'Everyday Crewneck',
                'slug' => 'everyday-crewneck',
                'description' => 'A clean crewneck sweatshirt for company merch, school orgs and events. Soft brushed inside with a smooth print surface outside.',
                'features' => ['Brushed fleece interior', 'Screen print or embroidery', 'Bulk pricing available'],
                'price_from' => '₱780',
                'minimum_order' => '20 pcs',
                'image_path' => 'images/products/everyday-crewneck.jpg',
                'is_featured' => false,
                'sort_order' => 10,
            ],
            [
                'category' => 'jackets',
                'name' => 'Team Bomber Jacket',
                'slug' => 'team-bomber-jacket',
                'description' => 'A lightweight bomber for travel days and warm-ups. Add a chest patch, sleeve print or a full back design.',
                'features' => ['Water-resistant shell', 'Ribbed cuffs and hem', 'Embroidered patch option'],
                'price_from' => '₱1,450',
                'minimum_order' => '10 pcs',
                'image_path' => 'images/products/team-bomber-jacket.jpg',
                'is_featured' => true,
                'sort_order' => 11,
            ],
            [
                'category' => 'jackets',
                'name' => 'Classic Leather Jacket',
                'slug' => 'classic-leather-jacket',
                'description' => 'A statement riding jacket with a clean front panel for club patches. Heavy enough for the road, sharp enough for a night out.',
                'features' => ['Genuine leather shell', 'Club patch placement', 'Sizes XS to 3XL'],
                'price_from' => '₱3,200',
                'minimum_order' => '5 pcs',
                'image_path' => 'images/products/classic-leather-jacket.jpg',
                'is_featured' => false,
                'sort_order' => 12,
            ],
        ];

        $categories = Category::query()->get()->keyBy('slug');

        foreach ($products as $product) {
            $category = $categories->get($product['category']);

            if (! $category instanceof Category) {
                continue;
            }

            unset($product['category']);

            Product::query()->updateOrCreate(
                ['slug' => $product['slug']],
                [...$product, 'category_id' => $category->id, 'is_active' => true],
            );
        }

        Product::query()->whereNotIn('slug', array_column($products, 'slug'))->delete();
    }
}
