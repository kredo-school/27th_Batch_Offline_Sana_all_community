<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Support\CategoryTranslator;
use App\Support\LanguageFileWriter;
use App\Support\LocalizedValue;

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
        $locale = app()->getLocale();

        return view('admin.categories.create', [
            'localeLabel' => LocalizedValue::language($locale),
        ]);
    }

    // Store a new category and add its name to the language files
    public function store(Request $request, LanguageFileWriter $languageFiles, CategoryTranslator $translator)
    {
        $locales = array_keys(array_diff_key(config('app.supported_locales'), ['en' => true]));
        $rules = ['name' => 'required|string|max:255', 'translations' => 'nullable|array'];
        foreach ($locales as $locale) {
            $rules["translations.$locale"] = 'nullable|string|max:255';
        }
        $validated = $request->validate($rules);

        $name = trim($validated['name']);
        $translations = ['en' => $name];
        foreach ($locales as $locale) {
            $translated = trim($validated['translations'][$locale] ?? '');
            if ($translated === '') {
                $translated = $translator->translate($name, $locale);
            }

            if ($translated === null) {
                // Translation is optional; the English category must still be usable
                // when the translation service is unavailable.
                $translated = $name;
            }

            $translations[$locale] = $translated;
        }

        $category = Category::create(['name' => $name]);
        foreach ($locales as $locale) {
            $this->saveTranslation($category, $locale, $translations[$locale]);
        }

        $languageFiles->addMissingKey($name, $translations);

        return redirect()->route('admin.categories.index');
    }

    // Show category edit form
    public function edit(Category $category)
    {
        $locale = app()->getLocale();

        return view('admin.categories.edit', [
            'category' => $category,
            'localeLabel' => LocalizedValue::language($locale),
            'localizedName' => $category->localized_name,
        ]);
    }

    // Update category
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'localized_name' => 'nullable|string|max:255',
            'name_ja' => 'nullable|string|max:255',
        ]);

        $category->update(['name' => $validated['name']]);
        $this->saveLocalizedName($category, $validated['localized_name'] ?? null);
        $this->saveTranslation($category, 'ja', $validated['name_ja'] ?? null);

        return redirect()->route('admin.categories.index');
    }

    // Delete category
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index');
    }

    private function saveLocalizedName(Category $category, ?string $localizedName): void
    {
        $locale = app()->getLocale();
        if ($locale === 'en') {
            return;
        }
        $this->saveTranslation($category, $locale, $localizedName);
    }

    private function saveTranslation(Category $category, string $locale, ?string $translatedName): void
    {
        $translation = $category->translations()
            ->where('field', 'name')
            ->where('target_language', $locale);

        if ($translatedName === null || trim($translatedName) === '') {
            $translation->delete();

            return;
        }

        $category->translations()->updateOrCreate(
            ['field' => 'name', 'target_language' => $locale],
            ['source_language' => 'en', 'translated_text' => trim($translatedName)],
        );
    }
}