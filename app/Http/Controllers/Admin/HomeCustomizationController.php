<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeHighlight;
use App\Models\HomeMedia;
use App\Models\HomeSection;
use App\Models\HomeTicker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class HomeCustomizationController extends Controller
{
    public function index(): Response
    {
        $this->ensureDefaultSections();

        return Inertia::render('admin/pages/HomeCustomization', [
            'sections' => HomeSection::query()
                ->ordered()
                ->get()
                ->map(fn(HomeSection $section) => [
                    'id' => $section->id,
                    'key' => $section->key,
                    'badge' => $section->badge,
                    'title' => $section->title,
                    'title_highlight' => $section->title_highlight,
                    'description' => $section->description,
                    'primary_button_label' => $section->primary_button_label,
                    'primary_button_url' => $section->primary_button_url,
                    'secondary_button_label' => $section->secondary_button_label,
                    'secondary_button_url' => $section->secondary_button_url,
                    'meta' => $section->meta ?? [],
                    'is_active' => (bool) $section->is_active,
                    'sort_order' => (int) $section->sort_order,
                ])
                ->values()
                ->all(),

            'tickers' => HomeTicker::query()
                ->ordered()
                ->get()
                ->map(fn(HomeTicker $ticker) => [
                    'id' => $ticker->id,
                    'label' => $ticker->label,
                    'is_active' => (bool) $ticker->is_active,
                    'sort_order' => (int) $ticker->sort_order,
                ])
                ->values()
                ->all(),

            'highlights' => HomeHighlight::query()
                ->ordered()
                ->get()
                ->map(fn(HomeHighlight $highlight) => [
                    'id' => $highlight->id,
                    'title' => $highlight->title,
                    'description' => $highlight->description,
                    'is_active' => (bool) $highlight->is_active,
                    'sort_order' => (int) $highlight->sort_order,
                ])
                ->values()
                ->all(),

            'media' => HomeMedia::query()
                ->ordered()
                ->get()
                ->map(fn(HomeMedia $media) => [
                    'id' => $media->id,
                    'group' => $media->group,
                    'title' => $media->title,
                    'subtitle' => $media->subtitle,
                    'category' => $media->category,
                    'image' => $media->image,
                    'image_url' => $media->image_url,
                    'alt_text' => $media->alt_text,
                    'layout' => $media->layout,
                    'is_active' => (bool) $media->is_active,
                    'sort_order' => (int) $media->sort_order,
                ])
                ->values()
                ->all(),
        ]);
    }

    public function updateSection(Request $request, HomeSection $homeSection): RedirectResponse
    {
        $validated = $request->validate([
            'badge' => ['nullable', 'string', 'max:160'],
            'title' => ['nullable', 'string', 'max:180'],
            'title_highlight' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'primary_button_label' => ['nullable', 'string', 'max:120'],
            'primary_button_url' => ['nullable', 'string', 'max:255'],
            'secondary_button_label' => ['nullable', 'string', 'max:120'],
            'secondary_button_url' => ['nullable', 'string', 'max:255'],
            'meta' => ['nullable', 'array'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $homeSection->update($validated);

        return back()->with('success', 'Section Home berhasil diperbarui.');
    }

    public function storeTicker(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        HomeTicker::create($validated);

        return back()->with('success', 'Ticker berhasil ditambahkan.');
    }

    public function updateTicker(Request $request, HomeTicker $homeTicker): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $homeTicker->update($validated);

        return back()->with('success', 'Ticker berhasil diperbarui.');
    }

    public function destroyTicker(HomeTicker $homeTicker): RedirectResponse
    {
        $homeTicker->delete();

        return back()->with('success', 'Ticker berhasil dihapus.');
    }

    public function storeHighlight(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        HomeHighlight::create($validated);

        return back()->with('success', 'Highlight berhasil ditambahkan.');
    }

    public function updateHighlight(Request $request, HomeHighlight $homeHighlight): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $homeHighlight->update($validated);

        return back()->with('success', 'Highlight berhasil diperbarui.');
    }

    public function destroyHighlight(HomeHighlight $homeHighlight): RedirectResponse
    {
        $homeHighlight->delete();

        return back()->with('success', 'Highlight berhasil dihapus.');
    }

    public function storeMedia(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'group' => ['required', 'string', 'in:hero,gallery'],
            'title' => ['nullable', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:160'],
            'category' => ['nullable', 'string', 'max:120'],
            'image_file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'layout' => ['required', 'string', 'in:normal,large,wide'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $validated['image'] = $request->file('image_file')->store('home-media', 'public');
        $validated['alt_text'] = $validated['title'] ?? 'Media Home HMPS RPL';

        unset($validated['image_file']);

        HomeMedia::create($validated);

        return back()->with('success', 'Media Home berhasil ditambahkan.');
    }

    public function updateMedia(Request $request, HomeMedia $homeMedia): RedirectResponse
    {
        $validated = $request->validate([
            'group' => ['required', 'string', 'in:hero,gallery'],
            'title' => ['nullable', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:160'],
            'category' => ['nullable', 'string', 'max:120'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'layout' => ['required', 'string', 'in:normal,large,wide'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        if ($request->hasFile('image_file')) {
            if (
                filled($homeMedia->image) &&
                ! str_starts_with($homeMedia->image, 'http://') &&
                ! str_starts_with($homeMedia->image, 'https://') &&
                Storage::disk('public')->exists($homeMedia->image)
            ) {
                Storage::disk('public')->delete($homeMedia->image);
            }

            $validated['image'] = $request->file('image_file')->store('home-media', 'public');
        }

        $validated['alt_text'] = $validated['title'] ?? $homeMedia->alt_text ?? 'Media Home HMPS RPL';

        unset($validated['image_file']);

        $homeMedia->update($validated);

        return back()->with('success', 'Media Home berhasil diperbarui.');
    }
    public function destroyMedia(HomeMedia $homeMedia): RedirectResponse
    {
        $homeMedia->delete();

        return back()->with('success', 'Media Home berhasil dihapus.');
    }

    private function ensureDefaultSections(): void
    {
        $sections = [
            'hero' => [
                'badge' => 'Organisasi Mahasiswa Rekayasa Perangkat Lunak',
                'title' => 'Rekayasa Perangkat Lunak',
                'title_highlight' => 'HMPS',
                'description' => 'Wadah aspirasi, kolaborasi, pengembangan skill, dan Program Kerja Mahasiswa RPL.',
                'primary_button_label' => 'Lihat Program Kerja',
                'primary_button_url' => '#proker',
                'secondary_button_label' => 'Konsultasi Gratis',
                'secondary_button_url' => '/konsultasi',
                'meta' => [
                    'top_badge_title' => 'Aktif & Produktif',
                    'top_badge_subtitle' => 'Organisasi mahasiswa RPL',
                    'bottom_badge_title' => 'Kegiatan Berkelanjutan',
                    'bottom_badge_subtitle' => 'Satu periode penuh',
                ],
                'sort_order' => 1,
            ],
            'about' => [
                'badge' => 'Tentang Kami',
                'title' => 'Sekilas tentang',
                'title_highlight' => 'HMPS RPL',
                'description' => 'HMPS Rekayasa Perangkat Lunak merupakan organisasi mahasiswa yang menjadi wadah aspirasi, pengembangan potensi, dan kolaborasi mahasiswa RPL.',
                'primary_button_label' => 'Lihat Profil Lengkap',
                'primary_button_url' => '/profil',
                'secondary_button_label' => null,
                'secondary_button_url' => null,
                'meta' => [
                    'highlight_title' => 'Organisasi mahasiswa yang fokus pada pengembangan, kebersamaan, dan kontribusi nyata.',
                ],
                'sort_order' => 2,
            ],
            'proker' => [
                'badge' => 'Agenda Kami',
                'title' => 'Program Kerja',
                'title_highlight' => 'Unggulan',
                'description' => 'Berbagai kegiatan yang dirancang untuk memperkuat kompetensi, solidaritas, dan pengalaman mahasiswa Rekayasa Perangkat Lunak.',
                'primary_button_label' => 'Lihat Semua Program',
                'primary_button_url' => '/program-kerja',
                'secondary_button_label' => null,
                'secondary_button_url' => null,
                'meta' => [],
                'sort_order' => 3,
            ],
            'gallery' => [
                'badge' => 'Dokumentasi Kegiatan',
                'title' => 'Galeri',
                'title_highlight' => 'HMPS RPL',
                'description' => 'Potret momen, kebersamaan, dan aktivitas yang menunjukkan semangat HMPS RPL dalam bergerak dan berkarya.',
                'primary_button_label' => 'Lihat Semua Dokumentasi',
                'primary_button_url' => '/dokumentasi',
                'secondary_button_label' => null,
                'secondary_button_url' => null,
                'meta' => [],
                'sort_order' => 4,
            ],
        ];

        foreach ($sections as $key => $section) {
            HomeSection::query()->firstOrCreate(
                ['key' => $key],
                [
                    ...$section,
                    'is_active' => true,
                ]
            );
        }
    }
}
