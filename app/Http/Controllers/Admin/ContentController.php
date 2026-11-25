<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContentRequest;
use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class ContentController extends Controller
{
    use StoresImages;

    public function index(Request $request, string $section): View
    {
        $definition = ContentDefinition::get($section);
        $query = $definition['model']::query();
        if ($definition['translated']) {
            $query->with('translations');
        }
        $search = mb_substr($request->string('q')->trim()->toString(), 0, 100);
        if ($search !== '') {
            if ($definition['translated']) {
                $query->whereHas('translations', fn ($query) => $query->where('title', 'like', '%'.$search.'%'));
            } else {
                $query->where($section === 'chambers' ? 'name' : 'title', 'like', '%'.$search.'%');
            }
        }
        if ($definition['translated'] && in_array($request->query('status'), ['draft', 'published'], true)) {
            $query->where('status', $request->query('status'));
        }
        $records = $query->orderBy('sort_order')->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.content.index', compact('section', 'definition', 'records', 'search'));
    }

    public function create(string $section): View
    {
        $definition = ContentDefinition::get($section);
        $record = new $definition['model'];

        return $this->editor($section, $definition, $record);
    }

    public function edit(string $section, int $record): View
    {
        $definition = ContentDefinition::get($section);
        $record = $definition['model']::findOrFail($record);

        return $this->editor($section, $definition, $record);
    }

    public function store(ContentRequest $request, string $section): RedirectResponse
    {
        $definition = ContentDefinition::get($section);
        $record = new $definition['model'];
        $this->persist($request, $section, $record);

        return redirect()->route('admin.content.edit', [$section, $record->id])->with('status', 'Content saved.');
    }

    public function preview(string $section, int $record, string $locale): View
    {
        $definition = ContentDefinition::get($section);
        abort_unless($definition['translated'], 404);
        $record = $definition['model']::with('translations')->findOrFail($record);
        $translation = $record->translations->firstWhere('locale', $locale);

        return view('admin.content.preview', compact('section', 'record', 'translation', 'locale'));
    }

    public function update(ContentRequest $request, string $section, int $record): RedirectResponse
    {
        $definition = ContentDefinition::get($section);
        $record = $definition['model']::findOrFail($record);
        $this->persist($request, $section, $record);

        return back()->with('status', 'Changes saved.');
    }

    public function destroy(Request $request, string $section, int $record): RedirectResponse
    {
        $definition = ContentDefinition::get($section);
        $record = $definition['model']::findOrFail($record);
        $request->validate(['confirm' => ['accepted']]);
        if ($section === 'chambers' || $section === 'credentials' || $section === 'categories') {
            $record->update(['is_active' => false]);
        } else {
            $record->update(['status' => 'draft']);
        }

        return redirect()->route('admin.content.index', $section)->with('status', 'Content archived. You can edit and publish it again.');
    }

    /** @param array<string, mixed> $definition */
    private function editor(string $section, array $definition, Model $record): View
    {
        $categories = Category::orderBy('sort_order')->get();
        if ($definition['translated'] && $record->exists) {
            $record->load('translations');
        }
        if ($section === 'chambers' && $record->exists) {
            $record->load('visitingWindows');
        }

        return view('admin.content.edit', compact('section', 'definition', 'record', 'categories'));
    }

    private function persist(ContentRequest $request, string $section, Model $record): void
    {
        $definition = ContentDefinition::get($section);
        $data = $request->validated();
        $image = $request->hasFile('image') ? $this->storeImage($request->file('image')) : null;
        $previousImage = $record->image_path;
        try {
            DB::transaction(function () use ($data, $definition, $record, $section, $image): void {
                foreach ($definition['fields'] as $field => $options) {
                    if ($field !== 'video_url') {
                        $value = $data[$field] ?? null;
                        if ($value !== null && $options[0] === 'datetime-local') {
                            $value = Carbon::parse($value, 'Asia/Dhaka')->utc();
                        }
                        $record->{$field} = $value;
                    }
                }
                if ($section === 'media') {
                    $record->provider = 'youtube';
                    $record->provider_id = $data['provider_id'];
                }
                if (! $definition['translated']) {
                    foreach ($definition['localized'] as $field) {
                        $record->{$field} = $data[$field];
                    }
                }
                if ($image) {
                    $record->image_path = $image;
                }
                $record->save();
                if ($definition['translated']) {
                    foreach (['bn', 'en'] as $locale) {
                        $translation = $data['translations'][$locale];
                        $translation['title'] = $translation['title'] ?? '';
                        $record->translations()->updateOrCreate(['locale' => $locale], $translation);
                    }
                }
                if ($section === 'chambers') {
                    $record->visitingWindows()->delete();
                    foreach ($data['windows'] ?? [] as $window) {
                        $record->visitingWindows()->create([...$window, 'is_active' => true]);
                    }
                }
            });
        } catch (Throwable $exception) {
            if ($image) {
                Storage::disk('local')->delete($image);
            }
            throw $exception;
        }
        if ($image && $previousImage && str_starts_with($previousImage, 'media/')) {
            Storage::disk('local')->delete($previousImage);
        }
    }
}
