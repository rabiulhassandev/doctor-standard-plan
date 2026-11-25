<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomeSlideRequest;
use App\Models\HomeSlide;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class HomeSlideController extends Controller
{
    use StoresImages;

    public function index(): View
    {
        return view('admin.slides.index', ['slides' => HomeSlide::query()->orderBy('sort_order')->orderBy('id')->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.slides.edit', ['slide' => new HomeSlide(['kind' => 'image', 'is_active' => true, 'sort_order' => 0])]);
    }

    public function store(HomeSlideRequest $request): RedirectResponse
    {
        return $this->save($request, new HomeSlide);
    }

    public function edit(HomeSlide $homeSlide): View
    {
        return view('admin.slides.edit', ['slide' => $homeSlide]);
    }

    public function update(HomeSlideRequest $request, HomeSlide $homeSlide): RedirectResponse
    {
        return $this->save($request, $homeSlide);
    }

    public function destroy(HomeSlide $homeSlide): RedirectResponse
    {
        $paths = DB::transaction(function () use ($homeSlide): array {
            $slide = HomeSlide::query()->lockForUpdate()->findOrFail($homeSlide->id);
            $slide->delete();

            return array_filter([$slide->file_path, $slide->poster_path]);
        });
        Storage::disk('local')->delete($paths);

        return to_route('admin.slides.index')->with('status', 'Slide deleted.');
    }

    private function save(HomeSlideRequest $request, HomeSlide $homeSlide): RedirectResponse
    {
        $data = $request->safe()->only(['title', 'kind', 'alt_text', 'sort_order', 'is_active']);
        $newFiles = [];
        $disk = Storage::disk('local');

        try {
            if ($file = $request->file('media')) {
                if ($data['kind'] === 'image') {
                    $newFiles['file_path'] = $this->storeImage($file);
                } else {
                    $extension = $file->getMimeType() === 'video/webm' ? 'webm' : 'mp4';
                    $path = $disk->putFileAs('slides', $file, Str::uuid().'.'.$extension);
                    if ($path === false) {
                        throw ValidationException::withMessages(['media' => 'The video could not be saved. Please try again.']);
                    }
                    $newFiles['file_path'] = $path;
                }
            }
            if ($data['kind'] === 'video' && ($poster = $request->file('poster'))) {
                $newFiles['poster_path'] = $this->storeImage($poster);
            }

            $oldFiles = DB::transaction(function () use ($data, $newFiles, $homeSlide, $request): array {
                $slide = $homeSlide->exists ? HomeSlide::query()->lockForUpdate()->findOrFail($homeSlide->id) : $homeSlide;
                if ($slide->kind !== $data['kind'] && ! isset($newFiles['file_path'])) {
                    throw ValidationException::withMessages(['media' => 'Upload a file when changing the slide type.']);
                }

                $changes = $newFiles;
                if (! isset($changes['poster_path']) && ($data['kind'] === 'image' || $request->boolean('remove_poster'))) {
                    $changes['poster_path'] = null;
                }
                $oldFiles = array_filter($slide->only(array_keys($changes)));
                $slide->fill([...$data, ...$changes])->save();

                if ($slide->is_active) {
                    SiteSetting::query()->upsert([['id' => 1, 'home_slider_managed' => true]], ['id'], ['home_slider_managed']);
                }

                return $oldFiles;
            });
        } catch (Throwable $exception) {
            $disk->delete(array_values($newFiles));

            throw $exception;
        }

        $disk->delete(array_values($oldFiles));

        return to_route('admin.slides.index')->with('status', 'Slide saved.');
    }
}
