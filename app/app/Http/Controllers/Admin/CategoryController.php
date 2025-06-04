<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    // Get /api/categories
    public function getCategories()
    {
        return response()->json(Category::all());
        // return ['message' => "Getting list of categories"];
    }

    // Post /api/categories
    public function createCategory(Request $request)
    {

        $request->validate([
            'name' => 'required|string|unique:categories',
        ]);

        $category = Category::create([
            'name' => $request->name
        ]);

        return response()->json([
            'message' => 'Added successfully ! ',
            'category' => $category
        ], 201);
        // return ['message' => "Creating new 1 of category"];

    }

    // Get /api/categories/{categoryId}
    public function getCategory($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        if (!$category) {
            return response()->json([
                'message' => 'Category not found !'
            ]);
        }
        return response()->json($category);


        // return ['message' => "Getting 1 category by Id"];

    }

    // Patch /api/categories/{categoryId}
    public function updateCategory(Request $request, $categoryId)
    {

        $category = Category::findOrFail($categoryId);

        $request->validate([
            'name' => 'required|string|unique:categories,name,' . $categoryId
        ]);

        $category->update(['name' => $request->name]);

        return response()->json([
            'message' => 'updated successfully',
            'category' => $category
        ]);
    }

    // return ['message' => "Updating 1 of category by Id"];


    // Delete /api/categories/{categoryId}
    public function deleteCategory($categoryId)
    {

        $category = Category::findOrFail($categoryId);
        $category->delete();

        return response()->json(['message' => 'Deleted successfully !']);

        // return ['message' => "Deleting 1 of category by Id"];

    }

    // get product by category
    public function getProductsByCategory($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        return response()->json($category->products);
    }

    public function searchCategory(Request $request)
    {

        $query = $request->input('query');

        if (empty($query)) {
            return response()->json(['message' => 'Please search somethings'], 400);
        }

        $category = Category::where('name', 'like', '%' . $query . '%')
            ->get();

        // Check if any product were found

        if ($category->isEmpty()) {
            return response()->json([
                'query' => $query,
                'message' => 'No categories found'
            ], 404);
        }
        return response()->json($category, 200);
    }

    public function sortCategories(Request $request)
    {
        $sortBy = $request->input('sort_by', 'name');

        $categories = Category::orderBy($sortBy)->get();

        return response()->json($categories);
    }

    public function getLimitedCategories($limited)
    {
        $categories = Category::limit($limited)->get();
        return response()->json($categories);
    }

    public function getPaginatedCategories(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $categories = Category::paginate($perPage);

        return response()->json($categories);
    }

    public function restoreCategory($categoryId)
    {
        $category = Category::withTrashed()->findOrFail($categoryId);
        $category->restore();

        return response()->json(['message' => 'Category restored successfully']);
    }



}