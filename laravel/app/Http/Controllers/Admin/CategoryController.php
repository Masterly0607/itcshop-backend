<?php


namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class CategoryController extends Controller
{
public function index()
{
    return Category::orderBy('updated_at', 'desc')->get(); // ✅ clean
}



  public function store(Request $request)
{
    try {
        $request->validate(['name' => 'required|string|max:255']);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);
    } catch (\Throwable $e) {
        Log::error('Category Store Error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Server Error',
            'error' => $e->getMessage(), // This will show in Vue dev console
        ], 500);
    }
}


    public function show($id)
    {
        return Category::findOrFail($id);
    }

 public function update(Request $request, $id)
{
    try {
        $request->validate(['name' => 'required|string|max:255']);

        $category = Category::findOrFail($id);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category,
        ]);
    } catch (\Throwable $e) {
        Log::error('Category Update Error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Server Error',
            'error' => $e->getMessage(),
        ], 500);
    }
}



    public function destroy($id)
    {
        Category::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
