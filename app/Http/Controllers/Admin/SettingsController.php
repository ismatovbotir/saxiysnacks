<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(SiteSettings $settings): View
    {
        return view('admin.settings.edit', ['settings' => $settings]);
    }

    public function update(SettingsRequest $request, SiteSettings $settings): RedirectResponse
    {
        $values = $request->safe()->except('og_image');

        if ($request->hasFile('og_image')) {
            if ($settings->get('og_image_path')) {
                Storage::disk('public')->delete($settings->get('og_image_path'));
            }

            $values['og_image_path'] = $request->file('og_image')->store('site', 'public');
        }

        $settings->save($values);

        return back()->with('status', 'Contacts and social links saved.');
    }
}
