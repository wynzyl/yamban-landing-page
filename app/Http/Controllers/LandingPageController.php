<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class LandingPageController extends Controller
{
    /**
     * Show the landing page.
     */
    public function home(): View
    {
        $categories = Category::query()
            ->active()
            ->ordered()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->get();

        $featuredProducts = Product::query()
            ->active()
            ->featured()
            ->ordered()
            ->with(['category', 'images'])
            ->take(4)
            ->get();

        return view('pages.home', [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
        ]);
    }

    /**
     * Show the about page.
     */
    public function about(): View
    {
        $categories = Category::query()
            ->active()
            ->ordered()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->get();

        return view('pages.about', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the product catalog.
     */
    public function products(): View
    {
        return view('pages.products');
    }

    /**
     * Show the design library.
     */
    public function designs(): View
    {
        return view('pages.designs');
    }

    /**
     * Show the contact page, optionally focused on a product.
     */
    public function contact(): View
    {
        $product = Product::query()
            ->active()
            ->with(['category', 'images'])
            ->where('slug', request()->query('product'))
            ->first();

        return view('pages.contact', [
            'product' => $product,
        ]);
    }
}
