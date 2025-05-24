<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        return view('index');
    }

    /**
     * Search for products
     */
    public function search(Request $request)
    {
        // Validate search input
        $validated = $request->validate([
            'query' => 'required|string|min:3|max:100',
        ]);

        $searchTerm = $validated['query'];

        // Use proper parameter binding for search to prevent SQL injection
        $results = Product::where(function ($query) use ($searchTerm) {
            $query->where('name', 'LIKE', '%' . $searchTerm . '%')
                ->orWhere('description', 'LIKE', '%' . $searchTerm . '%');
        })
            ->with(['category:id,name', 'brand:id,name'])
            ->select(['id', 'name', 'description', 'regular_price', 'sale_price', 'image', 'category_id', 'brand_id'])
            ->limit(10)
            ->get();

        return response()->json($results);
    }
}
