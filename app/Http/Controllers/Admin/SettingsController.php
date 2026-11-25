<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    use StoresImages;

    public function edit(): View
    {
        return view('admin.settings', ['settings' => SiteSetting::current()]);
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        $settings = SiteSetting::current();
        $data = $request->safe()->except(['logo', 'og_image']);
        $stored = [];
        $previous = [];
        try {
            foreach (['logo', 'og_image'] as $field) {
                if ($request->hasFile($field)) {
                    $stored[$field.'_path'] = $this->storeImage($request->file($field));
                    $previous[] = $settings->{$field.'_path'};
                }
            }
            $settings->fill([...$data, ...$stored, 'is_demo' => false])->save();
        } catch (Throwable $exception) {
            Storage::disk('local')->delete(array_values($stored));
            throw $exception;
        }
        foreach ($previous as $path) {
            if ($path && str_starts_with($path, 'media/')) {
                Storage::disk('local')->delete($path);
            }
        }

        return back()->with('status', 'Site settings saved.');
    }
}
