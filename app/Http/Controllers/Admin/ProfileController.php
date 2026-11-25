<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProfileRequest;
use App\Models\DoctorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    use StoresImages;

    public function edit(): View
    {
        return view('admin.profile', ['profile' => DoctorProfile::current()]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $profile = DoctorProfile::current();
        $data = $request->safe()->except('image');
        $previous = $profile->image_path;
        $image = $request->hasFile('image') ? $this->storeImage($request->file('image')) : null;
        try {
            $profile->fill($data);
            $profile->is_demo = false;
            if ($image) {
                $profile->image_path = $image;
            }
            $profile->save();
        } catch (Throwable $exception) {
            if ($image) {
                Storage::disk('local')->delete($image);
            }
            throw $exception;
        }
        if ($image && $previous && str_starts_with($previous, 'media/')) {
            Storage::disk('local')->delete($previous);
        }

        return back()->with('status', 'Doctor profile saved.');
    }
}
