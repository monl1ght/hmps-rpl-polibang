<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Documentation;
use App\Models\DocumentationImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DocumentationManagementController extends Controller
{
    public function index(): Response
    {
        $documentations = Documentation::query()
            ->with(['images' => fn($query) => $query->ordered()])
            ->ordered()
            ->get()
            ->map(fn(Documentation $documentation) => [
                'id' => $documentation->id,
                'title' => $documentation->title,
                'slug' => $documentation->slug,
                'category' => $documentation->category,
                'year' => $documentation->year,
                'event_date' => optional($documentation->event_date)?->format('Y-m-d'),
                'date_label' => optional($documentation->event_date)?->format('d M Y'),
                'location' => $documentation->location,
                'cover' => $documentation->cover,
                'cover_url' => $documentation->cover_url,
                'description' => $documentation->description,
                'is_featured' => (bool) $documentation->is_featured,
                'is_published' => (bool) $documentation->is_published,
                'sort_order' => (int) $documentation->sort_order,
                'images' => $documentation->images
                    ->map(fn(DocumentationImage $image) => [
                        'id' => $image->id,
                        'image' => $image->image,
                        'image_url' => $image->image_url,
                        'caption' => $image->caption,
                        'sort_order' => (int) $image->sort_order,
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();

        return Inertia::render('admin/pages/DocumentationManagement', [
            'documentations' => $documentations,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'in:kegiatan,proker,lainnya'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'event_date' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'cover_file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery_files' => ['nullable', 'array'],
            'gallery_files.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_featured' => ['required', 'boolean'],
            'is_published' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $coverPath = $request->file('cover_file')->store('documentations/covers', 'public');

        $documentation = Documentation::create([
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['title']),
            'category' => $validated['category'],
            'year' => $validated['year'],
            'event_date' => $validated['event_date'] ?? null,
            'location' => $validated['location'] ?? null,
            'description' => $validated['description'] ?? null,
            'cover' => $coverPath,
            'is_featured' => $validated['is_featured'],
            'is_published' => $validated['is_published'],
            'sort_order' => $validated['sort_order'],
        ]);

        if ($request->hasFile('gallery_files')) {
            foreach ($request->file('gallery_files') as $index => $file) {
                $path = $file->store('documentations/gallery', 'public');

                DocumentationImage::create([
                    'documentation_id' => $documentation->id,
                    'image' => $path,
                    'caption' => null,
                    'sort_order' => $index + 1,
                ]);
            }
        }

        return back()->with('success', 'Dokumentasi berhasil ditambahkan.');
    }

    public function update(Request $request, Documentation $documentation): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'in:kegiatan,proker,lainnya'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'event_date' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'cover_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery_files' => ['nullable', 'array'],
            'gallery_files.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_featured' => ['required', 'boolean'],
            'is_published' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $coverPath = $documentation->cover;

        if ($request->hasFile('cover_file')) {
            if (
                filled($documentation->cover) &&
                Storage::disk('public')->exists($documentation->cover)
            ) {
                Storage::disk('public')->delete($documentation->cover);
            }

            $coverPath = $request->file('cover_file')->store('documentations/covers', 'public');
        }

        $documentation->update([
            'title' => $validated['title'],
            'slug' => $documentation->title !== $validated['title']
                ? $this->uniqueSlug($validated['title'], $documentation->id)
                : $documentation->slug,
            'category' => $validated['category'],
            'year' => $validated['year'],
            'event_date' => $validated['event_date'] ?? null,
            'location' => $validated['location'] ?? null,
            'description' => $validated['description'] ?? null,
            'cover' => $coverPath,
            'is_featured' => $validated['is_featured'],
            'is_published' => $validated['is_published'],
            'sort_order' => $validated['sort_order'],
        ]);

        if ($request->hasFile('gallery_files')) {
            $lastOrder = $documentation->images()->max('sort_order') ?? 0;

            foreach ($request->file('gallery_files') as $index => $file) {
                $path = $file->store('documentations/gallery', 'public');

                DocumentationImage::create([
                    'documentation_id' => $documentation->id,
                    'image' => $path,
                    'caption' => null,
                    'sort_order' => $lastOrder + $index + 1,
                ]);
            }
        }

        return back()->with('success', 'Dokumentasi berhasil diperbarui.');
    }

    public function destroy(Documentation $documentation): RedirectResponse
    {
        if (
            filled($documentation->cover) &&
            Storage::disk('public')->exists($documentation->cover)
        ) {
            Storage::disk('public')->delete($documentation->cover);
        }

        foreach ($documentation->images as $image) {
            if (
                filled($image->image) &&
                Storage::disk('public')->exists($image->image)
            ) {
                Storage::disk('public')->delete($image->image);
            }
        }

        $documentation->delete();

        return back()->with('success', 'Dokumentasi berhasil dihapus.');
    }

    public function destroyImage(DocumentationImage $documentationImage): RedirectResponse
    {
        if (
            filled($documentationImage->image) &&
            Storage::disk('public')->exists($documentationImage->image)
        ) {
            Storage::disk('public')->delete($documentationImage->image);
        }

        $documentationImage->delete();

        return back()->with('success', 'Foto galeri berhasil dihapus.');
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Documentation::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
