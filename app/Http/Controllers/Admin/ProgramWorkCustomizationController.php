<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramWork;
use App\Models\ProgramWorkCategory;
use App\Models\ProgramWorkHeroSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProgramWorkCustomizationController extends Controller
{
    public function index(): Response
    {
        $this->ensureDefaultHeroSetting();
        $this->ensureDefaultCategories();

        return Inertia::render('admin/pages/ProgramWorkCustomization', [
            'heroSetting' => $this->heroPayload(),
            'categories' => ProgramWorkCategory::query()
                ->ordered()
                ->get()
                ->map(fn(ProgramWorkCategory $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'title' => $category->title,
                    'description' => $category->description,
                    'sort_order' => (int) $category->sort_order,
                    'programs_count' => $category->programs()->count(),
                ])
                ->values()
                ->all(),

            'programs' => ProgramWork::query()
                ->with('category:id,name,slug,title,description,sort_order')
                ->ordered()
                ->get()
                ->map(fn(ProgramWork $program) => [
                    'id' => $program->id,
                    'program_work_category_id' => $program->program_work_category_id,
                    'category_name' => $program->category?->name,
                    'title' => $program->title,
                    'slug' => $program->slug,
                    'schedule_text' => $program->schedule_text,
                    'division_name' => $program->division_name,
                    'person_in_charge' => $program->person_in_charge,
                    'target' => $program->target,
                    'budget' => (int) $program->budget,
                    'budget_text' => $program->budget_text,
                    'image' => $program->image,
                    'image_url' => $program->image_url,
                    'excerpt' => $program->excerpt,
                    'description' => $program->description,
                    'goals' => $program->goals ?? [],
                    'benefits' => $program->benefits ?? [],
                    'funding_sources' => $program->funding_sources ?? [],
                    'goals_text' => implode("\n", $program->goals ?? []),
                    'benefits_text' => implode("\n", $program->benefits ?? []),
                    'funding_sources_text' => implode("\n", $program->funding_sources ?? []),
                    'is_featured' => (bool) $program->is_featured,
                    'is_published' => (bool) $program->is_published,
                    'sort_order' => (int) $program->sort_order,
                ])
                ->values()
                ->all(),
        ]);
    }

    public function updateHeroSetting(Request $request, ProgramWorkHeroSetting $programWorkHeroSetting): RedirectResponse
    {
        $validated = $request->validate($this->heroRules());

        $data = [
            'badge' => $validated['badge'] ?? null,
            'title' => $validated['title'] ?? null,
            'title_highlight' => $validated['title_highlight'] ?? null,
            'description' => $validated['description'] ?? null,
            'primary_button_label' => $validated['primary_button_label'] ?? null,
            'primary_button_url' => $validated['primary_button_url'] ?? null,
            'secondary_button_label' => $validated['secondary_button_label'] ?? null,
            'secondary_button_url' => $validated['secondary_button_url'] ?? null,
            'image_one_alt' => $validated['image_one_alt'] ?? null,
            'image_two_alt' => $validated['image_two_alt'] ?? null,
            'image_three_alt' => $validated['image_three_alt'] ?? null,
            'image_four_alt' => $validated['image_four_alt'] ?? null,
            'floating_badge_top_icon' => $validated['floating_badge_top_icon'] ?? null,
            'floating_badge_top_title' => $validated['floating_badge_top_title'] ?? null,
            'floating_badge_top_subtitle' => $validated['floating_badge_top_subtitle'] ?? null,
            'floating_badge_bottom_icon' => $validated['floating_badge_bottom_icon'] ?? null,
            'floating_badge_bottom_title' => $validated['floating_badge_bottom_title'] ?? null,
            'floating_badge_bottom_subtitle' => $validated['floating_badge_bottom_subtitle'] ?? null,
            'is_active' => (bool) $validated['is_active'],
        ];

        foreach (['one', 'two', 'three', 'four'] as $key) {
            $input = "image_{$key}_file";
            $column = "image_{$key}";

            if ($request->hasFile($input)) {
                $this->deleteHeroImageIfExists($programWorkHeroSetting->{$column});
                $data[$column] = $request->file($input)->store('program-work-hero', 'public');
            }
        }

        $programWorkHeroSetting->update($data);

        return back()->with('success', 'Hero section Program Kerja berhasil diperbarui.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140', 'unique:program_work_categories,slug'],
            'title' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1500'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $validated['slug'] = $validated['slug'] ?: $this->uniqueCategorySlug($validated['name']);

        ProgramWorkCategory::create($validated);

        return back()->with('success', 'Kategori program kerja berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, ProgramWorkCategory $programWorkCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140', 'unique:program_work_categories,slug,' . $programWorkCategory->id],
            'title' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1500'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $validated['slug'] = $validated['slug'] ?: $this->uniqueCategorySlug($validated['name'], $programWorkCategory->id);

        $programWorkCategory->update($validated);

        return back()->with('success', 'Kategori program kerja berhasil diperbarui.');
    }

    public function destroyCategory(ProgramWorkCategory $programWorkCategory): RedirectResponse
    {
        if ($programWorkCategory->programs()->exists()) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih memiliki program kerja.');
        }

        $programWorkCategory->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }

    public function storeProgram(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'program_work_category_id' => ['required', 'exists:program_work_categories,id'],
            'title' => ['required', 'string', 'max:180'],
            'schedule_text' => ['nullable', 'string', 'max:180'],
            'division_name' => ['nullable', 'string', 'max:180'],
            'person_in_charge' => ['nullable', 'string', 'max:180'],
            'target' => ['nullable', 'string', 'max:180'],
            'budget' => ['nullable', 'integer', 'min:0'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:10000'],
            'goals_text' => ['nullable', 'string', 'max:5000'],
            'benefits_text' => ['nullable', 'string', 'max:5000'],
            'funding_sources_text' => ['nullable', 'string', 'max:5000'],
            'is_featured' => ['required', 'boolean'],
            'is_published' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $data = $this->prepareProgramData($validated);
        $data['slug'] = $this->uniqueProgramSlug($validated['title']);

        if ($request->hasFile('image_file')) {
            $data['image'] = $request->file('image_file')->store('program-works', 'public');
        }

        ProgramWork::create($data);

        return back()->with('success', 'Program kerja berhasil ditambahkan.');
    }

    public function updateProgram(Request $request, ProgramWork $programWork): RedirectResponse
    {
        $validated = $request->validate([
            'program_work_category_id' => ['required', 'exists:program_work_categories,id'],
            'title' => ['required', 'string', 'max:180'],
            'schedule_text' => ['nullable', 'string', 'max:180'],
            'division_name' => ['nullable', 'string', 'max:180'],
            'person_in_charge' => ['nullable', 'string', 'max:180'],
            'target' => ['nullable', 'string', 'max:180'],
            'budget' => ['nullable', 'integer', 'min:0'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:10000'],
            'goals_text' => ['nullable', 'string', 'max:5000'],
            'benefits_text' => ['nullable', 'string', 'max:5000'],
            'funding_sources_text' => ['nullable', 'string', 'max:5000'],
            'is_featured' => ['required', 'boolean'],
            'is_published' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $data = $this->prepareProgramData($validated);

        if ($programWork->title !== $validated['title']) {
            $data['slug'] = $this->uniqueProgramSlug($validated['title'], $programWork->id);
        }

        if ($request->hasFile('image_file')) {
            $this->deleteImageIfExists($programWork->image);

            $data['image'] = $request->file('image_file')->store('program-works', 'public');
        }

        $programWork->update($data);

        return back()->with('success', 'Program kerja berhasil diperbarui.');
    }

    public function destroyProgram(ProgramWork $programWork): RedirectResponse
    {
        $this->deleteImageIfExists($programWork->image);

        $programWork->delete();

        return back()->with('success', 'Program kerja berhasil dihapus.');
    }

    private function heroPayload(): array
    {
        $setting = ProgramWorkHeroSetting::query()->latest('id')->first();

        if (! $setting) {
            $this->ensureDefaultHeroSetting();
            $setting = ProgramWorkHeroSetting::query()->latest('id')->first();
        }

        return [
            'id' => $setting->id,
            'badge' => $setting->badge,
            'title' => $setting->title,
            'title_highlight' => $setting->title_highlight,
            'description' => $setting->description,
            'primary_button_label' => $setting->primary_button_label,
            'primary_button_url' => $setting->primary_button_url,
            'secondary_button_label' => $setting->secondary_button_label,
            'secondary_button_url' => $setting->secondary_button_url,
            'image_one' => $setting->image_one,
            'image_one_url' => $setting->image_one_url,
            'image_one_alt' => $setting->image_one_alt,
            'image_two' => $setting->image_two,
            'image_two_url' => $setting->image_two_url,
            'image_two_alt' => $setting->image_two_alt,
            'image_three' => $setting->image_three,
            'image_three_url' => $setting->image_three_url,
            'image_three_alt' => $setting->image_three_alt,
            'image_four' => $setting->image_four,
            'image_four_url' => $setting->image_four_url,
            'image_four_alt' => $setting->image_four_alt,
            'floating_badge_top_icon' => $setting->floating_badge_top_icon,
            'floating_badge_top_title' => $setting->floating_badge_top_title,
            'floating_badge_top_subtitle' => $setting->floating_badge_top_subtitle,
            'floating_badge_bottom_icon' => $setting->floating_badge_bottom_icon,
            'floating_badge_bottom_title' => $setting->floating_badge_bottom_title,
            'floating_badge_bottom_subtitle' => $setting->floating_badge_bottom_subtitle,
            'is_active' => (bool) $setting->is_active,
        ];
    }

    private function heroRules(): array
    {
        return [
            'badge' => ['nullable', 'string', 'max:160'],
            'title' => ['nullable', 'string', 'max:180'],
            'title_highlight' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:3000'],
            'primary_button_label' => ['nullable', 'string', 'max:120'],
            'primary_button_url' => ['nullable', 'string', 'max:255'],
            'secondary_button_label' => ['nullable', 'string', 'max:120'],
            'secondary_button_url' => ['nullable', 'string', 'max:255'],
            'image_one_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_one_alt' => ['nullable', 'string', 'max:180'],
            'image_two_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_two_alt' => ['nullable', 'string', 'max:180'],
            'image_three_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_three_alt' => ['nullable', 'string', 'max:180'],
            'image_four_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_four_alt' => ['nullable', 'string', 'max:180'],
            'floating_badge_top_icon' => ['nullable', 'string', 'max:30'],
            'floating_badge_top_title' => ['nullable', 'string', 'max:120'],
            'floating_badge_top_subtitle' => ['nullable', 'string', 'max:160'],
            'floating_badge_bottom_icon' => ['nullable', 'string', 'max:30'],
            'floating_badge_bottom_title' => ['nullable', 'string', 'max:120'],
            'floating_badge_bottom_subtitle' => ['nullable', 'string', 'max:160'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    private function prepareProgramData(array $validated): array
    {
        return [
            'program_work_category_id' => $validated['program_work_category_id'],
            'title' => $validated['title'],
            'schedule_text' => $validated['schedule_text'] ?? null,
            'division_name' => $validated['division_name'] ?? null,
            'person_in_charge' => $validated['person_in_charge'] ?? null,
            'target' => $validated['target'] ?? null,
            'budget' => (int) ($validated['budget'] ?? 0),
            'excerpt' => $validated['excerpt'] ?? null,
            'description' => $validated['description'] ?? null,
            'goals' => $this->linesToArray($validated['goals_text'] ?? null),
            'benefits' => $this->linesToArray($validated['benefits_text'] ?? null),
            'funding_sources' => $this->linesToArray($validated['funding_sources_text'] ?? null),
            'is_featured' => $validated['is_featured'],
            'is_published' => $validated['is_published'],
            'sort_order' => (int) $validated['sort_order'],
        ];
    }

    private function linesToArray(?string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value))
            ->map(fn($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    private function uniqueCategorySlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (
            ProgramWorkCategory::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function uniqueProgramSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (
            ProgramWork::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function deleteImageIfExists(?string $image): void
    {
        if (
            filled($image) &&
            ! Str::startsWith($image, ['http://', 'https://', '/', 'storage/']) &&
            Storage::disk('public')->exists($image)
        ) {
            Storage::disk('public')->delete($image);
        }
    }

    private function deleteHeroImageIfExists(?string $image): void
    {
        if (
            filled($image) &&
            ! Str::startsWith($image, ['http://', 'https://', '/', 'storage/', 'data:image']) &&
            Storage::disk('public')->exists($image)
        ) {
            Storage::disk('public')->delete($image);
        }
    }

    private function ensureDefaultHeroSetting(): void
    {
        ProgramWorkHeroSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'badge' => 'Halaman Program Kerja HMPS RPL',
                'title' => 'Program Kerja',
                'title_highlight' => 'HMPS RPL',
                'description' => 'Halaman ini menampilkan seluruh rancangan program kerja HMPS Rekayasa Perangkat Lunak secara rapi, modern, dan mudah dipahami berdasarkan kategori, divisi pelaksana, penanggung jawab, serta estimasi anggaran.',
                'primary_button_label' => 'Lihat Semua Program',
                'primary_button_url' => '#program-list',
                'secondary_button_label' => 'Konsultasi Gratis',
                'secondary_button_url' => '/konsultasi',
                'image_one_alt' => 'Workshop dan program kerja mahasiswa',
                'image_two_alt' => 'Seminar dan kegiatan mahasiswa',
                'image_three_alt' => 'Upgrading dan koordinasi organisasi',
                'image_four_alt' => 'Kolaborasi organisasi mahasiswa',
                'floating_badge_top_icon' => '📌',
                'floating_badge_top_title' => 'Program Tersusun',
                'floating_badge_top_subtitle' => 'Berdasarkan kategori',
                'floating_badge_bottom_icon' => '🚀',
                'floating_badge_bottom_title' => 'Fokus Periode',
                'floating_badge_bottom_subtitle' => 'Kegiatan berkelanjutan',
                'is_active' => true,
            ]
        );
    }

    private function ensureDefaultCategories(): void
    {
        if (ProgramWorkCategory::query()->exists()) {
            return;
        }

        $categories = [
            [
                'name' => 'Unggulan',
                'slug' => 'unggulan',
                'title' => 'Program Unggulan',
                'description' => 'Program prioritas HMPS RPL yang memiliki dampak besar bagi mahasiswa.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Rutin',
                'slug' => 'rutin',
                'title' => 'Program Rutin',
                'description' => 'Program kerja yang dilaksanakan secara berkala dalam satu periode kepengurusan.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Pengabdian',
                'slug' => 'pengabdian',
                'title' => 'Program Pengabdian',
                'description' => 'Program sosial dan kontribusi nyata HMPS RPL kepada lingkungan sekitar.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Pendukung',
                'slug' => 'pendukung',
                'title' => 'Program Pendukung',
                'description' => 'Program tambahan untuk mendukung pengembangan organisasi dan mahasiswa.',
                'sort_order' => 4,
            ],
        ];

        foreach ($categories as $category) {
            ProgramWorkCategory::create($category);
        }
    }
}
