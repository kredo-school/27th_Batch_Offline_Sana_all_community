<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    // Category list
    public function index()
    {
        $categories = Category::all();

        return view('admin.categories.index', compact('categories'));
    }

    // Show category creation form
    public function create()
    {
        return view('admin.categories.create');
    }

    // Store a new category
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ja' => 'nullable|string|max:255',
        ]);

        $category = Category::create(['name' => $validated['name']]);
        $this->saveJapaneseName($category, $validated['name_ja'] ?? null);

        return redirect()->route('admin.categories.index');
    }

    // Show category edit form
    public function edit(Category $category)
    {
        $nameJa = $category->japanese_name;

        return view('admin.categories.edit', compact('category', 'nameJa'));
    }

    // Update category
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ja' => 'nullable|string|max:255',
        ]);

        $category->update(['name' => $validated['name']]);
        $this->saveJapaneseName($category, $validated['name_ja'] ?? null);

        return redirect()->route('admin.categories.index');
    }

    // Delete category
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index');
    }

    private function saveJapaneseName(Category $category, ?string $nameJa): void
    {
        $translation = $category->translations()
            ->where('field', 'name')
            ->where('target_language', 'ja');

        if ($nameJa === null || trim($nameJa) === '') {
            $translation->delete();

            return;
        }

        $category->translations()->updateOrCreate(
            ['field' => 'name', 'target_language' => 'ja'],
            ['source_language' => 'en', 'translated_text' => trim($nameJa)],
        );
    }
}