<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed the storefront categories.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Athletic Jersey',
                'slug' => 'athletic-jersey',
                'tagline' => 'Team kits that play as hard as you do',
                'description' => 'Full sublimation jerseys for basketball, football, volleyball, cycling and e-sports teams. Lightweight fabric, sharp prints and a fit that moves with the game.',
                'image_path' => 'images/categories/athletic-jersey.jpg',
                'accent_color' => '#ff5a1f',
                'sort_order' => 1,
            ],
            [
                'name' => 'Corporate Uniforms',
                'slug' => 'corporate-uniforms',
                'tagline' => 'A uniform your whole team is proud to wear',
                'description' => 'Polos, button-downs, blouses and office sets with clean stitching and embroidery options. Perfect for hotels, clinics, offices and front-desk teams.',
                'image_path' => 'images/categories/corporate-uniforms.jpg',
                'accent_color' => '#1d4ed8',
                'sort_order' => 2,
            ],
            [
                'name' => 'Longsleeves for Riding',
                'slug' => 'longsleeves-for-riding',
                'tagline' => 'Built for long rides and changing weather',
                'description' => 'Riding jerseys and base layers with breathable panels, flat seams and sun protection — made for motorcycle and cycling groups who cover serious distance.',
                'image_path' => 'images/categories/riding-longsleeves.jpg',
                'accent_color' => '#0f766e',
                'sort_order' => 3,
            ],
            [
                'name' => 'Hoodies',
                'slug' => 'hoodies',
                'tagline' => 'Everyday comfort with your crew on it',
                'description' => 'Pullover and zip-up hoodies in heavyweight fleece. Add your logo, team name or event design with screen print, embroidery or direct-to-film transfer.',
                'image_path' => 'images/categories/hoodies.jpg',
                'accent_color' => '#7c3aed',
                'sort_order' => 4,
            ],
            [
                'name' => 'Jackets',
                'slug' => 'jackets',
                'tagline' => 'Layering pieces that finish the kit',
                'description' => 'Bombers, varsity jackets and windbreakers made for travel, warm-ups and everyday wear. Choose your shell, lining and placement for logos and patches.',
                'image_path' => 'images/categories/jackets.jpg',
                'accent_color' => '#b45309',
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                $category,
            );
        }
    }
}
