<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfileHeroImage;
use App\Models\ProfileItem;
use App\Models\ProfileSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProfileCustomizationController extends Controller
{
    public function index(): Response
    {
        $this->ensureDefaultProfileContent();

        $sections = ProfileSection::query()
            ->ordered()
            ->get()
            ->map(fn(ProfileSection $section) => [
                'id' => $section->id,
                'key' => $section->key,
                'badge' => $section->badge,
                'title' => $section->title,
                'title_highlight' => $section->title_highlight,
                'description' => $section->description,
                'primary_button_label' => $section->primary_button_label,
                'primary_button_url' => $section->primary_button_url,
                'meta' => $section->meta ?? [],
                'is_active' => (bool) $section->is_active,
                'sort_order' => (int) $section->sort_order,
            ])
            ->values()
            ->all();

        $items = ProfileItem::query()
            ->ordered()
            ->get()
            ->map(fn(ProfileItem $item) => [
                'id' => $item->id,
                'group' => $item->group,
                'label' => $item->label,
                'title' => $item->title,
                'subtitle' => $item->subtitle,
                'name' => $item->name,
                'position' => $item->position,
                'description' => $item->description,
                'meta' => $item->meta ?? [],
                'is_active' => (bool) $item->is_active,
                'sort_order' => (int) $item->sort_order,
            ])
            ->values()
            ->all();

        $heroImages = ProfileHeroImage::query()
            ->ordered()
            ->get()
            ->map(fn(ProfileHeroImage $image) => [
                'id' => $image->id,
                'title' => $image->title,
                'image' => $image->image,
                'image_url' => $image->image_url,
                'alt_text' => $image->alt_text,
                'is_active' => (bool) $image->is_active,
                'sort_order' => (int) $image->sort_order,
            ])
            ->values()
            ->all();

        return Inertia::render('admin/pages/ProfileCustomization', [
            'sections' => $sections,
            'items' => $items,
            'heroImages' => $heroImages,
            'sectionOptions' => $this->sectionOptions(),
            'groupOptions' => $this->groupOptions(),
        ]);
    }

    public function updateSection(Request $request, ProfileSection $profileSection): RedirectResponse
    {
        $validated = $request->validate([
            'badge' => ['nullable', 'string', 'max:160'],
            'title' => ['nullable', 'string', 'max:180'],
            'title_highlight' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'primary_button_label' => ['nullable', 'string', 'max:120'],
            'primary_button_url' => ['nullable', 'string', 'max:255'],
            'meta' => ['nullable', 'array'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $profileSection->update($validated);

        return back()->with('success', 'Section profil berhasil diperbarui.');
    }

    public function storeItem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'group' => ['required', Rule::in($this->allowedGroups())],
            'label' => ['nullable', 'string', 'max:180'],
            'title' => ['nullable', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:180'],
            'name' => ['nullable', 'string', 'max:180'],
            'position' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'meta' => ['nullable', 'array'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        ProfileItem::create($validated);

        return back()->with('success', 'Item profil berhasil ditambahkan.');
    }

    public function updateItem(Request $request, ProfileItem $profileItem): RedirectResponse
    {
        $validated = $request->validate([
            'group' => ['required', Rule::in($this->allowedGroups())],
            'label' => ['nullable', 'string', 'max:180'],
            'title' => ['nullable', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:180'],
            'name' => ['nullable', 'string', 'max:180'],
            'position' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'meta' => ['nullable', 'array'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $profileItem->update($validated);

        return back()->with('success', 'Item profil berhasil diperbarui.');
    }

    public function destroyItem(ProfileItem $profileItem): RedirectResponse
    {
        $profileItem->delete();

        return back()->with('success', 'Item profil berhasil dihapus.');
    }

    public function storeHeroImage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:160'],
            'alt_text' => ['nullable', 'string', 'max:180'],
            'image_file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ], [
            'image_file.required' => 'Foto hero wajib diupload.',
            'image_file.image' => 'File harus berupa gambar.',
            'image_file.mimes' => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'image_file.max' => 'Ukuran gambar maksimal 4MB.',
        ]);

        $this->guardActiveHeroImageLimit((bool) $validated['is_active']);

        $path = $request->file('image_file')->store('profile/hero', 'public');

        ProfileHeroImage::create([
            'title' => $validated['title'] ?? null,
            'image' => $path,
            'alt_text' => $validated['alt_text'] ?? null,
            'is_active' => (bool) $validated['is_active'],
            'sort_order' => (int) $validated['sort_order'],
        ]);

        return back()->with('success', 'Foto hero profil berhasil ditambahkan.');
    }

    public function updateHeroImage(
        Request $request,
        ProfileHeroImage $profileHeroImage
    ): RedirectResponse {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:160'],
            'alt_text' => ['nullable', 'string', 'max:180'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ], [
            'image_file.image' => 'File harus berupa gambar.',
            'image_file.mimes' => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'image_file.max' => 'Ukuran gambar maksimal 4MB.',
        ]);

        $this->guardActiveHeroImageLimit(
            (bool) $validated['is_active'],
            $profileHeroImage->id
        );

        $imagePath = $profileHeroImage->image;

        if ($request->hasFile('image_file')) {
            $this->deleteStoredImageIfLocal($profileHeroImage->image);

            $imagePath = $request->file('image_file')->store('profile/hero', 'public');
        }

        $profileHeroImage->update([
            'title' => $validated['title'] ?? null,
            'image' => $imagePath,
            'alt_text' => $validated['alt_text'] ?? null,
            'is_active' => (bool) $validated['is_active'],
            'sort_order' => (int) $validated['sort_order'],
        ]);

        return back()->with('success', 'Foto hero profil berhasil diperbarui.');
    }

    public function destroyHeroImage(ProfileHeroImage $profileHeroImage): RedirectResponse
    {
        $this->deleteStoredImageIfLocal($profileHeroImage->image);

        $profileHeroImage->delete();

        return back()->with('success', 'Foto hero profil berhasil dihapus.');
    }

    private function guardActiveHeroImageLimit(bool $willBeActive, ?int $ignoreId = null): void
    {
        if (! $willBeActive) {
            return;
        }

        $activeCount = ProfileHeroImage::query()
            ->where('is_active', true)
            ->when($ignoreId, fn($query) => $query->whereKeyNot($ignoreId))
            ->count();

        if ($activeCount >= 4) {
            throw ValidationException::withMessages([
                'image_file' => 'Foto hero aktif maksimal 4 gambar. Nonaktifkan atau hapus salah satu foto terlebih dahulu.',
            ]);
        }
    }

    private function deleteStoredImageIfLocal(?string $path): void
    {
        if (! $this->isLocalPublicStoragePath($path)) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function isLocalPublicStoragePath(?string $path): bool
    {
        if (blank($path)) {
            return false;
        }

        return ! str_starts_with($path, 'http://')
            && ! str_starts_with($path, 'https://')
            && ! str_starts_with($path, '/')
            && ! str_starts_with($path, 'storage/');
    }

    private function sectionOptions(): array
    {
        return [
            ['key' => 'hero', 'label' => 'Hero Profil'],
            ['key' => 'about', 'label' => 'Tentang HMPS'],
            ['key' => 'history', 'label' => 'Sejarah HMPS'],
            ['key' => 'vision_mission', 'label' => 'Visi & Misi'],
            ['key' => 'identity', 'label' => 'Identitas Organisasi'],
            ['key' => 'function', 'label' => 'Fungsi Organisasi'],
        ];
    }

    private function groupOptions(): array
    {
        return [
            ['value' => 'ticker', 'label' => 'Ticker Berjalan'],
            ['value' => 'history', 'label' => 'Sejarah Kepemimpinan'],
            ['value' => 'mission', 'label' => 'Misi Organisasi'],
            ['value' => 'identity', 'label' => 'Identitas Organisasi'],
            ['value' => 'function', 'label' => 'Fungsi Organisasi'],
        ];
    }

    private function allowedGroups(): array
    {
        return collect($this->groupOptions())
            ->pluck('value')
            ->all();
    }

    private function ensureDefaultProfileContent(): void
    {
        $sections = [
            'hero' => [
                'badge' => 'Profil HMPS Rekayasa Perangkat Lunak',
                'title' => 'Mengenal lebih dekat',
                'title_highlight' => 'HMPS RPL',
                'description' => 'Halaman ini memuat profil organisasi, sejarah kepemimpinan, visi dan misi, serta nilai-nilai utama yang menjadi arah gerak HMPS Rekayasa Perangkat Lunak.',
                'meta' => [
                    'floating_top_title' => 'Identitas Organisasi',
                    'floating_top_subtitle' => 'Tersusun lebih jelas',
                    'floating_bottom_title' => 'Aktif & Berkembang',
                    'floating_bottom_subtitle' => 'Organisasi mahasiswa RPL',
                ],
                'sort_order' => 1,
            ],
            'about' => [
                'badge' => 'Tentang Kami',
                'title' => 'Sekilas tentang',
                'title_highlight' => 'HMPS RPL',
                'description' => 'HMPS Rekayasa Perangkat Lunak merupakan organisasi mahasiswa yang menjadi wadah aspirasi, pengembangan potensi, penguatan kebersamaan, serta ruang pembelajaran organisasi bagi mahasiswa RPL.',
                'primary_button_label' => 'Lihat Kepengurusan',
                'primary_button_url' => '/kepengurusan',
                'meta' => [
                    'paragraph_2' => 'HMPS RPL tidak hanya berperan sebagai pelaksana program kerja, tetapi juga menjadi jembatan komunikasi antara mahasiswa dengan program studi, serta ruang pembinaan kepemimpinan, tanggung jawab, dan budaya kerja sama.',
                    'paragraph_3' => 'Melalui kegiatan akademik, sosial, dan kolaboratif, HMPS RPL berupaya menciptakan lingkungan mahasiswa yang aktif, suportif, inovatif, dan berorientasi pada kemajuan bersama.',
                    'function_title' => 'HMPS hadir sebagai ruang aspirasi, pengembangan, dan kontribusi nyata mahasiswa RPL.',
                ],
                'sort_order' => 2,
            ],
            'history' => [
                'badge' => 'Sejarah HMPS',
                'title' => 'Perjalanan organisasi',
                'title_highlight' => 'per periode',
                'description' => 'Gambaran perkembangan HMPS RPL dari masa perintisan hingga periode terkini.',
                'sort_order' => 3,
            ],
            'vision_mission' => [
                'badge' => 'Visi & Misi',
                'title' => 'Arah gerak',
                'title_highlight' => 'HMPS RPL',
                'description' => 'Visi dan misi menjadi fondasi utama dalam menjalankan program kerja dan budaya organisasi HMPS RPL.',
                'meta' => [
                    'vision' => 'Menjadikan HIMA RPL sebagai wadah membentuk mahasiswa RPL yang kompeten, aktif, kolaboratif, dan inovatif di bidang Teknologi Informasi dengan tetap menjunjung tinggi akhlak dan adab yang baik.',
                ],
                'sort_order' => 4,
            ],
            'identity' => [
                'badge' => 'Identitas Organisasi',
                'title' => 'Nilai utama',
                'title_highlight' => 'HMPS RPL',
                'description' => 'Nilai yang menjadi identitas dan budaya organisasi HMPS RPL dalam menjalankan peran dan kontribusinya.',
                'sort_order' => 5,
            ],
            'function' => [
                'badge' => 'Fungsi Utama',
                'title' => 'Peran strategis',
                'title_highlight' => 'HMPS RPL',
                'description' => 'HMPS RPL berperan sebagai penghubung, penggerak, dan wadah pengembangan mahasiswa.',
                'sort_order' => 6,
            ],
        ];

        foreach ($sections as $key => $section) {
            ProfileSection::firstOrCreate(
                ['key' => $key],
                [
                    ...$section,
                    'is_active' => true,
                ]
            );
        }

        $this->ensureDefaultItems();
    }

    private function ensureDefaultItems(): void
    {
        $defaultsByGroup = [
            'ticker' => [
                ['group' => 'ticker', 'label' => 'Profil HMPS RPL', 'sort_order' => 1],
                ['group' => 'ticker', 'label' => 'Sejarah Organisasi', 'sort_order' => 2],
                ['group' => 'ticker', 'label' => 'Visi & Misi', 'sort_order' => 3],
                ['group' => 'ticker', 'label' => 'Kolaboratif', 'sort_order' => 4],
                ['group' => 'ticker', 'label' => 'Profesional', 'sort_order' => 5],
                ['group' => 'ticker', 'label' => 'Inovatif', 'sort_order' => 6],
            ],

            'history' => [
                [
                    'group' => 'history',
                    'label' => '2025 / 2026',
                    'title' => 'Akselerasi Inovasi dan Profesionalisme',
                    'description' => 'Fokus kepengurusan diarahkan pada inovasi program, peningkatan kualitas pelaksanaan kegiatan, serta pembentukan budaya organisasi yang aktif, adaptif, profesional, dan berdampak bagi mahasiswa Rekayasa Perangkat Lunak.',
                    'meta' => [
                        'ketua' => 'Rizky Maulana',
                        'wakil' => 'Aisyah Maharani',
                    ],
                    'sort_order' => 1,
                ],
            ],

            'mission' => [
                [
                    'group' => 'mission',
                    'description' => 'Mengembangkan potensi mahasiswa RPL melalui berbagai kegiatan akademik maupun non-akademik.',
                    'sort_order' => 1,
                ],
                [
                    'group' => 'mission',
                    'description' => 'Membangun kolaborasi dengan industri maupun alumni untuk membuka peluang pengembangan mahasiswa.',
                    'sort_order' => 2,
                ],
            ],

            'identity' => [
                [
                    'group' => 'identity',
                    'title' => 'Wadah Aspirasi',
                    'description' => 'Menjadi ruang mahasiswa RPL untuk menyampaikan gagasan, kebutuhan, dan ide secara terbuka dan terarah.',
                    'sort_order' => 1,
                ],
                [
                    'group' => 'identity',
                    'title' => 'Pengembangan Potensi',
                    'description' => 'Mendorong peningkatan kemampuan akademik, organisasi, komunikasi, dan kepemimpinan mahasiswa.',
                    'sort_order' => 2,
                ],
            ],

            'function' => [
                [
                    'group' => 'function',
                    'description' => 'Menjadi wadah aspirasi mahasiswa RPL.',
                    'sort_order' => 1,
                ],
                [
                    'group' => 'function',
                    'description' => 'Menyelenggarakan kegiatan yang relevan dan bermanfaat.',
                    'sort_order' => 2,
                ],
            ],
        ];

        foreach ($defaultsByGroup as $group => $items) {
            if (ProfileItem::where('group', $group)->exists()) {
                continue;
            }

            foreach ($items as $item) {
                ProfileItem::create([
                    ...$item,
                    'is_active' => true,
                ]);
            }
        }
    }
}
