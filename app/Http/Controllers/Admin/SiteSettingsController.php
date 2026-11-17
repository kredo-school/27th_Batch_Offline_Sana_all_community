<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteSettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit', [
            'categories' => Category::query()->orderBy('name')->get(),
            'settings' => SiteSetting::query()->pluck('value', 'key'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'header_category_order' => ['nullable', 'array'],
            'header_category_order.*' => ['nullable', 'string', 'max:255'],
            'policy_terms' => ['nullable', 'string', 'max:100000'],
            'policy_guidelines' => ['nullable', 'string', 'max:100000'],
            'policy_report' => ['nullable', 'string', 'max:100000'],
            'policy_event' => ['nullable', 'string', 'max:100000'],
            'policy_job' => ['nullable', 'string', 'max:100000'],
            'policy_advertising' => ['nullable', 'string', 'max:100000'],
            'policy_p2p' => ['nullable', 'string', 'max:100000'],
            'home_background' => ['nullable', 'image','mimes:jpeg,png,jpg,gif', 'max:6144'],
        ]);

        SiteSetting::put('header_category_order', array_values(array_filter($data['header_category_order'] ?? [])));
        foreach (['policy_terms', 'policy_guidelines', 'policy_report', 'policy_event', 'policy_job', 'policy_advertising', 'policy_p2p'] as $key) {
            SiteSetting::put($key, $data[$key] ?? '');
        }

        if ($request->hasFile('home_background')) {
            $oldPath = SiteSetting::get('home_background');
            $path = $request->file('home_background')->store('site', 'public');

            if (!is_string($path)) {
                return back()
                    ->withInput()
                    ->withErrors(['home_background' => __('The background image could not be saved. Check that the storage directory is writable.')]);
            }

            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
            SiteSetting::put('home_background', $path);
        }

        return redirect()->route('admin.settings.edit')->with('status', __('Site settings updated.'));
    }
}
