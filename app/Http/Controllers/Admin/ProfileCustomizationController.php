<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfileCabinetLogo;
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
    private const HERO_IMAGE_LIMIT = 4;

    public function index(): Response
    {
        $this->ensureDefaultProfileContent();

        return Inertia::render('admin/pages/ProfileCustomization', [
            'sections' => $this->sectionPayloads(),
            'items' => $this->itemPayloads(),
            'heroImages' => $this->heroImagePayloads(),
            'cabinetLogoImage' => $this->cabinetLogoImagePayload(),
            'sectionOptions' => $this->sectionOptions(),
            'groupOptions' => $this->groupOptions(),
        ]);
    }

    public function updateSection(Request $request, ProfileSection $profileSection): RedirectResponse
    {
        $validated = $request->validate($this->sectionRules());

        $profileSection->update([
            'badge' => $this->nullableString($validated['badge'] ?? null),
            'title' => $this->nullableString($validated['title'] ?? null),
            'title_highlight' => $this->nullableString($validated['title_highlight'] ?? null),
            'description' => $this->nullableString($validated['description'] ?? null),
            'primary_button_label' => $this->nullableString($validated['primary_button_label'] ?? null),
            'primary_button_url' => $this->nullableString($validated['primary_button_url'] ?? null),
            'meta' => $this->normalizeSectionMeta(
                $profileSection->key,
                $validated['meta'] ?? []
            ),
            'is_active' => (bool) $validated['is_active'],
            'sort_order' => (int) $validated['sort_order'],
        ]);

        return back()->with('success', 'Section profil berhasil diperbarui.');
    }

    public function storeItem(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->itemRules());

        ProfileItem::create($this->normalizeItemPayload($validated));

        return back()->with('success', 'Item profil berhasil ditambahkan.');
    }

    public function updateItem(Request $request, ProfileItem $profileItem): RedirectResponse
    {
        $validated = $request->validate($this->itemRules());

        $profileItem->update($this->normalizeItemPayload($validated));

        return back()->with('success', 'Item profil berhasil diperbarui.');
    }

    public function destroyItem(ProfileItem $profileItem): RedirectResponse
    {
        $profileItem->delete();

        return back()->with('success', 'Item profil berhasil dihapus.');
    }

    public function storeHeroImage(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->heroImageRules(requireImage: true), $this->imageValidationMessages('Foto hero'));

        $this->guardActiveHeroImageLimit((bool) $validated['is_active']);

        $path = $request->file('image_file')->store('profile/hero', 'public');

        ProfileHeroImage::create([
            'title' => $this->nullableString($validated['title'] ?? null),
            'image' => $path,
            'alt_text' => $this->nullableString($validated['alt_text'] ?? null),
            'is_active' => (bool) $validated['is_active'],
            'sort_order' => (int) $validated['sort_order'],
        ]);

        return back()->with('success', 'Foto hero profil berhasil ditambahkan.');
    }

    public function updateHeroImage(
        Request $request,
        ProfileHeroImage $profileHeroImage
    ): RedirectResponse {
        $validated = $request->validate($this->heroImageRules(requireImage: false), $this->imageValidationMessages('Foto hero'));

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
            'title' => $this->nullableString($validated['title'] ?? null),
            'image' => $imagePath,
            'alt_text' => $this->nullableString($validated['alt_text'] ?? null),
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

    public function storeCabinetLogoImage(Request $request): RedirectResponse
    {
        $this->guardCabinetLogoFeatureIsReady();

        $validated = $request->validate($this->cabinetLogoRules(requireImage: true), $this->imageValidationMessages('Logo kabinet'));

        $cabinetLogo = ProfileCabinetLogo::query()
            ->orderByDesc('is_active')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        $imagePath = $request->file('image_file')->store('profile/cabinet-logo', 'public');

        $payload = $this->cabinetLogoPayload($validated, $imagePath);

        if ($cabinetLogo) {
            $this->deleteStoredImageIfLocal($cabinetLogo->image);

            $cabinetLogo->update($payload);
        } else {
            $cabinetLogo = ProfileCabinetLogo::create($payload);
        }

        $this->syncCabinetLogoSection($payload);
        $this->deactivateOtherCabinetLogos($cabinetLogo->id, (bool) $validated['is_active']);

        return back()->with('success', 'Logo kabinet berhasil disimpan.');
    }

    public function updateCabinetLogoImage(
        Request $request,
        int|string $profileCabinetLogo
    ): RedirectResponse {
        $this->guardCabinetLogoFeatureIsReady();

        $cabinetLogo = $this->findCabinetLogoOrFail($profileCabinetLogo);

        $validated = $request->validate($this->cabinetLogoRules(requireImage: false), $this->imageValidationMessages('Logo kabinet'));

        $imagePath = $cabinetLogo->image;

        if ($request->hasFile('image_file')) {
            $this->deleteStoredImageIfLocal($cabinetLogo->image);

            $imagePath = $request->file('image_file')->store('profile/cabinet-logo', 'public');
        }

        $payload = $this->cabinetLogoPayload($validated, $imagePath);

        $cabinetLogo->update($payload);

        $this->syncCabinetLogoSection($payload);
        $this->deactivateOtherCabinetLogos($cabinetLogo->id, (bool) $validated['is_active']);

        return back()->with('success', 'Logo kabinet berhasil diperbarui.');
    }

    public function destroyCabinetLogoImage(int|string $profileCabinetLogo): RedirectResponse
    {
        $this->guardCabinetLogoFeatureIsReady();

        $cabinetLogo = $this->findCabinetLogoOrFail($profileCabinetLogo);

        $this->deleteStoredImageIfLocal($cabinetLogo->image);

        $cabinetLogo->delete();

        return back()->with('success', 'Logo kabinet berhasil dihapus.');
    }

    private function sectionPayloads(): array
    {
        return ProfileSection::query()
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
                'meta' => $this->normalizeSectionMeta($section->key, $section->meta ?? []),
                'is_active' => (bool) $section->is_active,
                'sort_order' => (int) $section->sort_order,
            ])
            ->values()
            ->all();
    }

    private function itemPayloads(): array
    {
        return ProfileItem::query()
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
    }

    private function heroImagePayloads(): array
    {
        return ProfileHeroImage::query()
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
    }

    private function cabinetLogoImagePayload(): ?array
    {
        if (! $this->cabinetLogoModelExists()) {
            return null;
        }

        $logo = ProfileCabinetLogo::query()
            ->orderByDesc('is_active')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if (! $logo) {
            return null;
        }

        return [
            'id' => $logo->id,
            'title' => $logo->title,
            'cabinet_name' => $logo->cabinet_name,
            'period' => $logo->period,
            'image' => $logo->image,
            'image_url' => $logo->image_url,
            'alt_text' => $logo->alt_text,
            'description' => $logo->description,
            'meta' => $logo->meta ?? [],
            'is_active' => (bool) $logo->is_active,
            'sort_order' => (int) $logo->sort_order,
        ];
    }

    private function sectionRules(): array
    {
        return [
            'badge' => ['nullable', 'string', 'max:160'],
            'title' => ['nullable', 'string', 'max:180'],
            'title_highlight' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'primary_button_label' => ['nullable', 'string', 'max:120'],
            'primary_button_url' => ['nullable', 'string', 'max:255'],
            'meta' => ['nullable', 'array'],
            'meta.floating_top_title' => ['nullable', 'string', 'max:160'],
            'meta.floating_top_subtitle' => ['nullable', 'string', 'max:180'],
            'meta.floating_bottom_title' => ['nullable', 'string', 'max:160'],
            'meta.floating_bottom_subtitle' => ['nullable', 'string', 'max:180'],
            'meta.paragraph_2' => ['nullable', 'string', 'max:5000'],
            'meta.paragraph_3' => ['nullable', 'string', 'max:5000'],
            'meta.function_title' => ['nullable', 'string', 'max:500'],
            'meta.vision' => ['nullable', 'string', 'max:5000'],
            'meta.missions' => ['nullable', 'array'],
            'meta.missions.*' => ['nullable', 'string', 'max:1500'],
            'meta.missions_text' => ['nullable', 'string', 'max:8000'],
            'meta.mission_text' => ['nullable', 'string', 'max:8000'],
            'meta.cabinet_name' => ['nullable', 'string', 'max:180'],
            'meta.period' => ['nullable', 'string', 'max:120'],
            'meta.short_meaning' => ['nullable', 'string', 'max:500'],
            'meta.section_title' => ['nullable', 'string', 'max:180'],
            'meta.section_highlight' => ['nullable', 'string', 'max:180'],
            'meta.logo_caption' => ['nullable', 'string', 'max:180'],
            'meta.logo_tagline' => ['nullable', 'string', 'max:180'],
            'meta.meaning_title' => ['nullable', 'string', 'max:180'],
            'meta.meaning_subtitle' => ['nullable', 'string', 'max:500'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    private function itemRules(): array
    {
        return [
            'group' => ['required', Rule::in($this->allowedGroups())],
            'label' => ['nullable', 'string', 'max:180'],
            'title' => ['nullable', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:180'],
            'name' => ['nullable', 'string', 'max:180'],
            'position' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'meta' => ['nullable', 'array'],
            'meta.ketua' => ['nullable', 'string', 'max:180'],
            'meta.wakil' => ['nullable', 'string', 'max:180'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    private function heroImageRules(bool $requireImage): array
    {
        return [
            'title' => ['nullable', 'string', 'max:160'],
            'alt_text' => ['nullable', 'string', 'max:180'],
            'image_file' => [
                $requireImage ? 'required' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    private function cabinetLogoRules(bool $requireImage): array
    {
        return [
            'title' => ['nullable', 'string', 'max:160'],
            'cabinet_name' => ['nullable', 'string', 'max:180'],
            'period' => ['nullable', 'string', 'max:120'],
            'section_title' => ['nullable', 'string', 'max:180'],
            'section_highlight' => ['nullable', 'string', 'max:180'],
            'logo_caption' => ['nullable', 'string', 'max:180'],
            'logo_tagline' => ['nullable', 'string', 'max:180'],
            'meaning_title' => ['nullable', 'string', 'max:180'],
            'meaning_subtitle' => ['nullable', 'string', 'max:500'],
            'alt_text' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image_file' => [
                $requireImage ? 'required' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,svg',
                'max:4096',
            ],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    private function imageValidationMessages(string $label): array
    {
        return [
            'image_file.required' => "{$label} wajib diupload.",
            'image_file.image' => 'File harus berupa gambar.',
            'image_file.mimes' => 'Format gambar harus JPG, JPEG, PNG, SVG, atau WEBP.',
            'image_file.max' => 'Ukuran gambar maksimal 4MB.',
        ];
    }

    private function normalizeSectionMeta(string $sectionKey, ?array $meta): array
    {
        $meta = $meta ?? [];

        return match ($sectionKey) {
            'hero' => [
                'floating_top_title' => $this->valueOrDefault($meta['floating_top_title'] ?? null, 'Identitas Organisasi'),
                'floating_top_subtitle' => $this->valueOrDefault($meta['floating_top_subtitle'] ?? null, 'Tersusun lebih jelas'),
                'floating_bottom_title' => $this->valueOrDefault($meta['floating_bottom_title'] ?? null, 'Aktif & Berkembang'),
                'floating_bottom_subtitle' => $this->valueOrDefault($meta['floating_bottom_subtitle'] ?? null, 'Organisasi mahasiswa RPL'),
            ],
            'about' => [
                'paragraph_2' => $this->nullableString($meta['paragraph_2'] ?? null),
                'paragraph_3' => $this->nullableString($meta['paragraph_3'] ?? null),
                'function_title' => $this->nullableString($meta['function_title'] ?? null),
            ],
            'vision_mission' => [
                'vision' => $this->valueOrDefault(
                    $meta['vision'] ?? null,
                    'Menjadikan HIMA RPL sebagai wadah membentuk mahasiswa RPL yang kompeten, aktif, kolaboratif, dan inovatif di bidang Teknologi Informasi dengan tetap menjunjung tinggi akhlak dan adab yang baik.'
                ),
                'missions' => $this->normalizeTextLines(
                    $meta['missions'] ?? $meta['missions_text'] ?? $meta['mission_text'] ?? $meta['misi'] ?? null
                ),
                'missions_text' => implode("\n", $this->normalizeTextLines(
                    $meta['missions'] ?? $meta['missions_text'] ?? $meta['mission_text'] ?? $meta['misi'] ?? null
                )),
            ],
            'cabinet_logo' => [
                'cabinet_name' => $this->valueOrDefault($meta['cabinet_name'] ?? null, 'Raksa Devarya'),
                'period' => $this->valueOrDefault($meta['period'] ?? null, 'Periode 2025 / 2026'),
                'short_meaning' => $this->valueOrDefault(
                    $meta['short_meaning'] ?? null,
                    'Identitas visual kabinet yang menggambarkan semangat menjaga, membangun, dan mengembangkan HMPS RPL secara aktif, kreatif, kolaboratif, dan profesional.'
                ),
                'section_title' => $this->valueOrDefault($meta['section_title'] ?? null, 'Arti Logo'),
                'section_highlight' => $this->valueOrDefault($meta['section_highlight'] ?? null, 'Raksa Devarya'),
                'logo_caption' => $this->valueOrDefault($meta['logo_caption'] ?? null, 'Identitas Visual Kabinet'),
                'logo_tagline' => $this->valueOrDefault($meta['logo_tagline'] ?? null, 'Aktif, Kreatif, Kolaboratif'),
                'meaning_title' => $this->valueOrDefault($meta['meaning_title'] ?? null, 'Makna Identitas Kabinet'),
                'meaning_subtitle' => $this->valueOrDefault(
                    $meta['meaning_subtitle'] ?? null,
                    'Logo ditampilkan sebagai simbol arah gerak, karakter, dan komitmen kepengurusan.'
                ),
            ],
            default => array_filter($meta, fn($value) => filled($value)),
        };
    }

    private function normalizeItemPayload(array $validated): array
    {
        $group = (string) $validated['group'];
        $meta = $validated['meta'] ?? [];

        return [
            'group' => $group,
            'label' => $this->nullableString($validated['label'] ?? null),
            'title' => $this->nullableString($validated['title'] ?? null),
            'subtitle' => $this->nullableString($validated['subtitle'] ?? null),
            'name' => $this->nullableString($validated['name'] ?? null),
            'position' => $this->nullableString($validated['position'] ?? null),
            'description' => $this->nullableString($validated['description'] ?? null),
            'meta' => $group === 'history'
                ? [
                    'ketua' => $this->nullableString($meta['ketua'] ?? null),
                    'wakil' => $this->nullableString($meta['wakil'] ?? null),
                ]
                : [],
            'is_active' => (bool) $validated['is_active'],
            'sort_order' => (int) $validated['sort_order'],
        ];
    }

    private function cabinetLogoPayload(array $validated, ?string $imagePath): array
    {
        $cabinetName = $this->valueOrDefault($validated['cabinet_name'] ?? null, 'Raksa Devarya');

        return [
            'title' => $this->valueOrDefault($validated['title'] ?? null, 'Logo Kabinet'),
            'cabinet_name' => $cabinetName,
            'period' => $this->nullableString($validated['period'] ?? null),
            'image' => $imagePath,
            'alt_text' => $this->valueOrDefault($validated['alt_text'] ?? null, "Logo kabinet {$cabinetName}"),
            'description' => $this->nullableString($validated['description'] ?? null),
            'meta' => [
                'section_title' => $this->valueOrDefault($validated['section_title'] ?? null, 'Arti Logo'),
                'section_highlight' => $this->valueOrDefault($validated['section_highlight'] ?? null, $cabinetName),
                'logo_caption' => $this->valueOrDefault($validated['logo_caption'] ?? null, 'Identitas Visual Kabinet'),
                'logo_tagline' => $this->valueOrDefault($validated['logo_tagline'] ?? null, 'Aktif, Kreatif, Kolaboratif'),
                'meaning_title' => $this->valueOrDefault($validated['meaning_title'] ?? null, 'Makna Identitas Kabinet'),
                'meaning_subtitle' => $this->valueOrDefault(
                    $validated['meaning_subtitle'] ?? null,
                    'Logo ditampilkan sebagai simbol arah gerak, karakter, dan komitmen kepengurusan.'
                ),
            ],
            'is_active' => (bool) $validated['is_active'],
            'sort_order' => (int) $validated['sort_order'],
        ];
    }

    private function guardActiveHeroImageLimit(bool $willBeActive, ?int $ignoreId = null): void
    {
        if (! $willBeActive) {
            return;
        }

        $activeCount = ProfileHeroImage::query()
            ->where('is_active', true)
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->count();

        if ($activeCount >= self::HERO_IMAGE_LIMIT) {
            throw ValidationException::withMessages([
                'image_file' => 'Foto hero aktif maksimal 4 gambar. Nonaktifkan atau hapus salah satu foto terlebih dahulu.',
            ]);
        }
    }

    private function deactivateOtherCabinetLogos(?int $activeId, bool $currentIsActive): void
    {
        if (! $currentIsActive || ! $activeId || ! $this->cabinetLogoModelExists()) {
            return;
        }

        ProfileCabinetLogo::query()
            ->where('id', '!=', $activeId)
            ->update(['is_active' => false]);
    }

    /**
     * Sinkronkan field Logo Kabinet ke profile_sections agar tetap terbaca
     * oleh frontend lama maupun frontend baru. Dengan ini, Judul Section,
     * Highlight, Label Kartu Makna, dan Judul Besar Kartu Makna tidak lagi
     * bergantung pada satu sumber data saja.
     */
    private function syncCabinetLogoSection(array $payload): void
    {
        $meta = $payload['meta'] ?? [];
        $sectionMeta = [
            'cabinet_name' => $this->valueOrDefault($payload['cabinet_name'] ?? null, 'Raksa Devarya'),
            'period' => $this->valueOrDefault($payload['period'] ?? null, 'Periode 2025 / 2026'),
            'short_meaning' => $this->valueOrDefault(
                $payload['description'] ?? null,
                'Identitas visual kabinet yang menggambarkan semangat menjaga, membangun, dan mengembangkan HMPS RPL secara aktif, kreatif, kolaboratif, dan profesional.'
            ),
            'section_title' => $this->valueOrDefault($meta['section_title'] ?? null, 'Arti Logo'),
            'section_highlight' => $this->valueOrDefault($meta['section_highlight'] ?? null, $payload['cabinet_name'] ?? 'Raksa Devarya'),
            'logo_caption' => $this->valueOrDefault($meta['logo_caption'] ?? null, 'Identitas Visual Kabinet'),
            'logo_tagline' => $this->valueOrDefault($meta['logo_tagline'] ?? null, 'Aktif, Kreatif, Kolaboratif'),
            'meaning_title' => $this->valueOrDefault($meta['meaning_title'] ?? null, 'Makna Identitas Kabinet'),
            'meaning_subtitle' => $this->valueOrDefault(
                $meta['meaning_subtitle'] ?? null,
                'Logo ditampilkan sebagai simbol arah gerak, karakter, dan komitmen kepengurusan.'
            ),
        ];

        $section = ProfileSection::query()->firstOrCreate(
            ['key' => 'cabinet_logo'],
            [
                'badge' => 'Logo Kabinet',
                'title' => 'Arti Logo',
                'title_highlight' => $sectionMeta['cabinet_name'],
                'description' => $payload['description'] ?? null,
                'primary_button_label' => null,
                'primary_button_url' => null,
                'meta' => $sectionMeta,
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        $section->update([
            'title' => $sectionMeta['section_title'],
            'title_highlight' => $sectionMeta['section_highlight'],
            'description' => $this->nullableString($payload['description'] ?? null) ?: $section->description,
            'meta' => $sectionMeta,
            'is_active' => (bool) ($payload['is_active'] ?? true),
        ]);
    }

    private function guardCabinetLogoFeatureIsReady(): void
    {
        if ($this->cabinetLogoModelExists()) {
            return;
        }

        throw ValidationException::withMessages([
            'image_file' => 'Model ProfileCabinetLogo belum tersedia. Buat model dan migration profile_cabinet_logos terlebih dahulu.',
        ]);
    }

    private function cabinetLogoModelExists(): bool
    {
        return class_exists(ProfileCabinetLogo::class);
    }

    private function findCabinetLogoOrFail(int|string $id)
    {
        return ProfileCabinetLogo::query()->findOrFail($id);
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
            ['key' => 'cabinet_logo', 'label' => 'Logo Kabinet'],
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
            'cabinet_logo' => [
                'badge' => 'Logo Kabinet',
                'title' => 'Arti Logo',
                'title_highlight' => 'Raksa Devarya',
                'description' => 'Logo Raksa Devarya menjadi identitas visual kabinet yang melambangkan semangat menjaga, membangun, dan mengembangkan HMPS RPL secara aktif, kreatif, kolaboratif, adaptif, dan profesional.',
                'primary_button_label' => 'Lihat Visi Misi',
                'primary_button_url' => '#visi-misi',
                'meta' => [
                    'cabinet_name' => 'Raksa Devarya',
                    'period' => 'Periode 2025 / 2026',
                    'short_meaning' => 'Identitas visual kabinet yang menggambarkan semangat menjaga, membangun, dan mengembangkan HMPS RPL secara aktif, kreatif, kolaboratif, dan profesional.',
                    'section_title' => 'Arti Logo',
                    'section_highlight' => 'Raksa Devarya',
                    'logo_caption' => 'Identitas Visual Kabinet',
                    'logo_tagline' => 'Aktif, Kreatif, Kolaboratif',
                    'meaning_title' => 'Makna Identitas Kabinet',
                    'meaning_subtitle' => 'Logo ditampilkan sebagai simbol arah gerak, karakter, dan komitmen kepengurusan.',
                ],
                'sort_order' => 3,
            ],
            'history' => [
                'badge' => 'Sejarah HMPS',
                'title' => 'Perjalanan organisasi',
                'title_highlight' => 'per periode',
                'description' => 'Gambaran perkembangan HMPS RPL dari masa perintisan hingga periode terkini.',
                'sort_order' => 4,
            ],
            'vision_mission' => [
                'badge' => 'Visi & Misi',
                'title' => 'Arah gerak',
                'title_highlight' => 'HMPS RPL',
                'description' => 'Visi dan misi menjadi fondasi utama dalam menjalankan program kerja dan budaya organisasi HMPS RPL.',
                'meta' => [
                    'vision' => 'Menjadikan HIMA RPL sebagai wadah membentuk mahasiswa RPL yang kompeten, aktif, kolaboratif, dan inovatif di bidang Teknologi Informasi dengan tetap menjunjung tinggi akhlak dan adab yang baik.',
                    'missions' => [
                        'Mengembangkan potensi mahasiswa RPL melalui berbagai kegiatan akademik maupun non-akademik.',
                        'Membangun kolaborasi dengan industri maupun alumni untuk membuka peluang pengembangan mahasiswa.',
                        'Menciptakan lingkungan yang penuh kekeluargaan serta menumbuhkan tanggung jawab dan kepemimpinan.',
                        'Mengoptimalkan media sosial sebagai wadah dokumentasi, komunikasi, dan promosi HMPS RPL.',
                    ],
                    'missions_text' => "Mengembangkan potensi mahasiswa RPL melalui berbagai kegiatan akademik maupun non-akademik.\nMembangun kolaborasi dengan industri maupun alumni untuk membuka peluang pengembangan mahasiswa.\nMenciptakan lingkungan yang penuh kekeluargaan serta menumbuhkan tanggung jawab dan kepemimpinan.\nMengoptimalkan media sosial sebagai wadah dokumentasi, komunikasi, dan promosi HMPS RPL.",
                ],
                'sort_order' => 5,
            ],
            'identity' => [
                'badge' => 'Identitas Organisasi',
                'title' => 'Nilai utama',
                'title_highlight' => 'HMPS RPL',
                'description' => 'Nilai yang menjadi identitas dan budaya organisasi HMPS RPL dalam menjalankan peran dan kontribusinya.',
                'sort_order' => 6,
            ],
            'function' => [
                'badge' => 'Fungsi Utama',
                'title' => 'Peran strategis',
                'title_highlight' => 'HMPS RPL',
                'description' => 'HMPS RPL berperan sebagai penghubung, penggerak, dan wadah pengembangan mahasiswa.',
                'sort_order' => 7,
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

        $this->ensureDefaultCabinetLogo();
        $this->ensureDefaultItems();
    }

    private function ensureDefaultCabinetLogo(): void
    {
        if (! $this->cabinetLogoModelExists()) {
            return;
        }

        if (ProfileCabinetLogo::query()->exists()) {
            return;
        }

        ProfileCabinetLogo::create([
            'title' => 'Logo Kabinet',
            'cabinet_name' => 'Raksa Devarya',
            'period' => 'Periode 2025 / 2026',
            'image' => '/Images/logo/hmps-rpl-logo.png',
            'alt_text' => 'Logo Kabinet Raksa Devarya',
            'description' => 'Logo kabinet dapat diganti melalui panel admin. Gunakan deskripsi ini untuk menjelaskan arti logo secara singkat, jelas, dan profesional.',
            'meta' => [],
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    private function ensureDefaultItems(): void
    {
        $defaultsByGroup = [
            'ticker' => [
                ['group' => 'ticker', 'label' => 'Profil HMPS RPL', 'sort_order' => 1],
                ['group' => 'ticker', 'label' => 'Logo Kabinet', 'sort_order' => 2],
                ['group' => 'ticker', 'label' => 'Sejarah Organisasi', 'sort_order' => 3],
                ['group' => 'ticker', 'label' => 'Visi & Misi', 'sort_order' => 4],
                ['group' => 'ticker', 'label' => 'Kolaboratif', 'sort_order' => 5],
                ['group' => 'ticker', 'label' => 'Profesional', 'sort_order' => 6],
                ['group' => 'ticker', 'label' => 'Inovatif', 'sort_order' => 7],
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
                [
                    'group' => 'mission',
                    'description' => 'Menciptakan lingkungan yang penuh kekeluargaan serta menumbuhkan tanggung jawab dan kepemimpinan.',
                    'sort_order' => 3,
                ],
                [
                    'group' => 'mission',
                    'description' => 'Mengoptimalkan media sosial sebagai wadah dokumentasi, komunikasi, dan promosi HMPS RPL.',
                    'sort_order' => 4,
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
                [
                    'group' => 'identity',
                    'title' => 'Budaya Kolaboratif',
                    'description' => 'Membangun kebersamaan, komunikasi sehat, dan kerja tim antar mahasiswa RPL.',
                    'sort_order' => 3,
                ],
                [
                    'group' => 'identity',
                    'title' => 'Kontribusi Nyata',
                    'description' => 'Menghadirkan kegiatan yang relevan, bermanfaat, dan berdampak bagi lingkungan kampus.',
                    'sort_order' => 4,
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
                [
                    'group' => 'function',
                    'description' => 'Mengembangkan hard skill dan soft skill mahasiswa.',
                    'sort_order' => 3,
                ],
                [
                    'group' => 'function',
                    'description' => 'Membangun budaya organisasi yang kolaboratif dan profesional.',
                    'sort_order' => 4,
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

    private function normalizeTextLines(mixed $value): array
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn($item) => trim((string) $item))
                ->filter()
                ->values()
                ->all();
        }

        return collect(preg_split('/\r\n|\r|\n|\|/', (string) ($value ?? '')) ?: [])
            ->map(fn($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }

    private function valueOrDefault(mixed $value, string $default): string
    {
        return $this->nullableString($value) ?? $default;
    }
}
