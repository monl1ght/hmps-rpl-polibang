<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ProfileHeroImage;
use App\Models\ProfileItem;
use App\Models\ProfileSection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ProfileController extends Controller
{
    private const HERO_IMAGE_LIMIT = 4;

    private const SECTION_HERO = 'hero';
    private const SECTION_ABOUT = 'about';
    private const SECTION_CABINET_LOGO = 'cabinet_logo';
    private const SECTION_HISTORY = 'history';
    private const SECTION_VISION_MISSION = 'vision_mission';
    private const SECTION_IDENTITY = 'identity';
    private const SECTION_FUNCTION = 'function';

    private const GROUP_TICKER = 'ticker';
    private const GROUP_HISTORY = 'history';
    private const GROUP_MISSION = 'mission';
    private const GROUP_IDENTITY = 'identity';
    private const GROUP_FUNCTION = 'function';

    private const CABINET_LOGO_MODEL = 'App\\Models\\ProfileCabinetLogo';
    private const CABINET_LOGO_FALLBACK_IMAGE = '/Images/logo/hmps-rpl-logo.png';

    public function index(): Response
    {
        $this->ensureDefaultProfileContent();

        $sections = ProfileSection::query()
            ->active()
            ->ordered()
            ->get()
            ->keyBy('key');

        $itemsByGroup = ProfileItem::query()
            ->active()
            ->ordered()
            ->get()
            ->groupBy('group');

        return Inertia::render('user/pages/Profil', [
            'profileHero' => $this->sectionPayload($sections, self::SECTION_HERO),
            'profileAbout' => $this->sectionPayload($sections, self::SECTION_ABOUT),
            'profileCabinetLogo' => $this->sectionPayload($sections, self::SECTION_CABINET_LOGO),
            'profileHistory' => $this->sectionPayload($sections, self::SECTION_HISTORY),
            'profileVisionMission' => $this->sectionPayload($sections, self::SECTION_VISION_MISSION),
            'profileIdentity' => $this->sectionPayload($sections, self::SECTION_IDENTITY),
            'profileFunction' => $this->sectionPayload($sections, self::SECTION_FUNCTION),

            'profileHeroImages' => $this->profileHeroImages(),
            'cabinetLogoImage' => $this->cabinetLogoImage(),

            'ticks' => $this->ticksPayload($itemsByGroup),
            'periods' => $this->periodsPayload($itemsByGroup),
            'missions' => $this->missionsPayload(
                $itemsByGroup,
                $sections->get(self::SECTION_VISION_MISSION)
            ),
            'identityCards' => $this->identityCardsPayload($itemsByGroup),
            'functionPoints' => $this->functionPointsPayload($itemsByGroup),
        ]);
    }

    private function sectionPayload(EloquentCollection $sections, string $key): array
    {
        /** @var ProfileSection|null $section */
        $section = $sections->get($key);

        if (! $section) {
            return [];
        }

        return [
            'id' => $section->id,
            'key' => $section->key,
            'badge' => $this->cleanNullableString($section->badge),
            'title' => $this->cleanNullableString($section->title),
            'titleHighlight' => $this->cleanNullableString($section->title_highlight),
            'description' => $this->cleanNullableString($section->description),
            'primaryButtonLabel' => $this->cleanNullableString($section->primary_button_label),
            'primaryButtonUrl' => $this->cleanNullableString($section->primary_button_url),
            'meta' => $this->normalizeMeta($section->meta),
        ];
    }

    private function profileHeroImages(): array
    {
        return ProfileHeroImage::query()
            ->active()
            ->ordered()
            ->take(self::HERO_IMAGE_LIMIT)
            ->get()
            ->map(fn(ProfileHeroImage $image) => [
                'id' => $image->id,
                'title' => $this->cleanNullableString($image->title),
                'image' => $this->resolveImageUrl($image->image_url ?: $image->image),
                'image_url' => $this->resolveImageUrl($image->image_url ?: $image->image),
                'alt' => $this->cleanNullableString($image->alt_text)
                    ?: ($this->cleanNullableString($image->title) ?: 'Foto profil HMPS RPL'),
                'alt_text' => $this->cleanNullableString($image->alt_text),
                'is_active' => (bool) $image->is_active,
                'sort_order' => (int) $image->sort_order,
            ])
            ->values()
            ->all();
    }

    private function cabinetLogoImage(): array
    {
        $fallback = $this->fallbackCabinetLogoImage();

        if (! class_exists(self::CABINET_LOGO_MODEL)) {
            return $fallback;
        }

        try {
            $model = self::CABINET_LOGO_MODEL;

            $logo = $model::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->first();

            if (! $logo) {
                return $fallback;
            }

            $imagePath = data_get($logo, 'image_url') ?: data_get($logo, 'image');
            $title = $this->cleanNullableString(data_get($logo, 'title')) ?: 'Logo Kabinet HMPS RPL';
            $cabinetName = $this->cleanNullableString(data_get($logo, 'cabinet_name')) ?: 'Raksa Devarya';
            $period = $this->cleanNullableString(data_get($logo, 'period')) ?: 'Periode 2025 / 2026';
            $description = $this->cleanNullableString(data_get($logo, 'description')) ?: $fallback['description'];
            $meta = [
                ...($fallback['meta'] ?? []),
                ...$this->cabinetSectionMeta(),
                ...$this->normalizeMeta(data_get($logo, 'meta')),
            ];

            return [
                'id' => data_get($logo, 'id'),
                'title' => $title,
                'cabinetName' => $cabinetName,
                'cabinet_name' => $cabinetName,
                'period' => $period,
                'image' => $this->resolveImageUrl($imagePath) ?: $fallback['image'],
                'image_url' => $this->resolveImageUrl($imagePath) ?: $fallback['image_url'],
                'alt' => $this->cleanNullableString(data_get($logo, 'alt_text')) ?: $title,
                'alt_text' => $this->cleanNullableString(data_get($logo, 'alt_text')) ?: $title,
                'description' => $description,
                'meaning' => $description,
                'meaning_title' => $this->cleanNullableString($meta['meaning_title'] ?? null) ?: 'Makna Identitas Kabinet',
                'meaning_subtitle' => $this->cleanNullableString($meta['meaning_subtitle'] ?? null) ?: 'Logo ditampilkan sebagai simbol arah gerak, karakter, dan komitmen kepengurusan.',
                'section_title' => $this->cleanNullableString($meta['section_title'] ?? null) ?: 'Arti Logo',
                'section_highlight' => $this->cleanNullableString($meta['section_highlight'] ?? null) ?: $cabinetName,
                'logo_caption' => $this->cleanNullableString($meta['logo_caption'] ?? null) ?: 'Identitas Visual Kabinet',
                'logo_tagline' => $this->cleanNullableString($meta['logo_tagline'] ?? null) ?: 'Aktif, Kreatif, Kolaboratif',
                'meta' => $meta,
                'is_active' => (bool) data_get($logo, 'is_active', true),
                'sort_order' => (int) data_get($logo, 'sort_order', 0),
                'isFallback' => false,
            ];
        } catch (Throwable) {
            return $fallback;
        }
    }

    private function fallbackCabinetLogoImage(): array
    {
        return [
            'id' => null,
            'title' => 'Logo Kabinet HMPS RPL',
            'cabinetName' => 'Raksa Devarya',
            'cabinet_name' => 'Raksa Devarya',
            'period' => 'Periode 2025 / 2026',
            'image' => self::CABINET_LOGO_FALLBACK_IMAGE,
            'image_url' => self::CABINET_LOGO_FALLBACK_IMAGE,
            'alt' => 'Logo Kabinet Raksa Devarya HMPS RPL',
            'alt_text' => 'Logo Kabinet Raksa Devarya HMPS RPL',
            'description' => 'Logo kabinet menjadi identitas visual yang merepresentasikan semangat HMPS RPL dalam membangun organisasi yang aktif, kreatif, kolaboratif, profesional, dan adaptif terhadap perkembangan teknologi.',
            'meaning' => 'Logo kabinet menjadi identitas visual yang merepresentasikan semangat HMPS RPL dalam membangun organisasi yang aktif, kreatif, kolaboratif, profesional, dan adaptif terhadap perkembangan teknologi.',
            'section_title' => 'Arti Logo',
            'section_highlight' => 'Raksa Devarya',
            'logo_caption' => 'Identitas Visual Kabinet',
            'logo_tagline' => 'Aktif, Kreatif, Kolaboratif',
            'meaning_title' => 'Makna Identitas Kabinet',
            'meaning_subtitle' => 'Logo ditampilkan sebagai simbol arah gerak, karakter, dan komitmen kepengurusan.',
            'meta' => [
                'section_title' => 'Arti Logo',
                'section_highlight' => 'Raksa Devarya',
                'logo_caption' => 'Identitas Visual Kabinet',
                'logo_tagline' => 'Aktif, Kreatif, Kolaboratif',
                'meaning_title' => 'Makna Identitas Kabinet',
                'meaning_subtitle' => 'Logo ditampilkan sebagai simbol arah gerak, karakter, dan komitmen kepengurusan.',
            ],
            'is_active' => true,
            'sort_order' => 0,
            'isFallback' => true,
        ];
    }

    private function cabinetSectionMeta(): array
    {
        try {
            $section = ProfileSection::query()
                ->where('key', 'cabinet_logo')
                ->first();

            return $section ? $this->normalizeMeta($section->meta) : [];
        } catch (Throwable) {
            return [];
        }
    }

    private function ticksPayload($itemsByGroup): array
    {
        return $this->itemsForGroup($itemsByGroup, self::GROUP_TICKER)
            ->map(fn(ProfileItem $item) => $this->cleanNullableString($item->label))
            ->filter()
            ->values()
            ->all();
    }

    private function periodsPayload($itemsByGroup): array
    {
        return $this->itemsForGroup($itemsByGroup, self::GROUP_HISTORY)
            ->map(fn(ProfileItem $item) => [
                'id' => $item->id,
                'periode' => $this->cleanNullableString($item->label),
                'title' => $this->cleanNullableString($item->title),
                'ketua' => $this->cleanNullableString(data_get($item->meta, 'ketua')),
                'wakil' => $this->cleanNullableString(data_get($item->meta, 'wakil')),
                'description' => $this->cleanNullableString($item->description),
                'sort_order' => (int) $item->sort_order,
            ])
            ->values()
            ->all();
    }

    private function missionsPayload($itemsByGroup, ?ProfileSection $visionSection = null): array
    {
        $sectionMissions = $this->normalizeTextLines(
            data_get($visionSection?->meta, 'missions')
                ?? data_get($visionSection?->meta, 'missions_text')
                ?? data_get($visionSection?->meta, 'mission_text')
                ?? data_get($visionSection?->meta, 'misi')
        );

        if (count($sectionMissions)) {
            return $sectionMissions;
        }

        return $this->itemsForGroup($itemsByGroup, self::GROUP_MISSION)
            ->map(fn(ProfileItem $item) => $this->cleanNullableString($item->description))
            ->filter()
            ->values()
            ->all();
    }

    private function identityCardsPayload($itemsByGroup): array
    {
        return $this->itemsForGroup($itemsByGroup, self::GROUP_IDENTITY)
            ->map(fn(ProfileItem $item) => [
                'id' => $item->id,
                'title' => $this->cleanNullableString($item->title),
                'desc' => $this->cleanNullableString($item->description),
                'description' => $this->cleanNullableString($item->description),
                'sort_order' => (int) $item->sort_order,
            ])
            ->values()
            ->all();
    }

    private function functionPointsPayload($itemsByGroup): array
    {
        return $this->itemsForGroup($itemsByGroup, self::GROUP_FUNCTION)
            ->map(fn(ProfileItem $item) => $this->cleanNullableString($item->description))
            ->filter()
            ->values()
            ->all();
    }

    private function itemsForGroup($itemsByGroup, string $group)
    {
        return $itemsByGroup->get($group, collect());
    }

    private function ensureDefaultProfileContent(): void
    {
        $sections = $this->defaultSections();

        foreach ($sections as $key => $section) {
            ProfileSection::query()->firstOrCreate(
                ['key' => $key],
                [
                    ...$section,
                    'is_active' => true,
                ]
            );
        }

        $this->ensureDefaultItems();
    }

    private function defaultSections(): array
    {
        return [
            self::SECTION_HERO => [
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

            self::SECTION_ABOUT => [
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

            self::SECTION_CABINET_LOGO => [
                'badge' => 'Logo Kabinet',
                'title' => 'Arti Logo',
                'title_highlight' => 'Raksa Devarya',
                'description' => 'Logo Raksa Devarya melambangkan semangat mahasiswa RPL dalam membangun organisasi yang aktif, kreatif, kolaboratif, profesional, dan adaptif terhadap perkembangan teknologi. Identitas visual ini menjadi simbol arah gerak kabinet untuk menjaga nilai kebersamaan, keberanian berkarya, serta kontribusi nyata bagi mahasiswa Rekayasa Perangkat Lunak.',
                'primary_button_label' => 'Lihat Visi Misi',
                'primary_button_url' => '#visi-misi',
                'meta' => [
                    'cabinet_name' => 'Raksa Devarya',
                    'period' => 'Periode 2025 / 2026',
                    'short_meaning' => 'Identitas visual kabinet yang menggambarkan semangat kolaborasi, kreativitas, dan profesionalisme mahasiswa RPL.',
                    'section_title' => 'Arti Logo',
                    'section_highlight' => 'Raksa Devarya',
                    'logo_caption' => 'Identitas Visual Kabinet',
                    'logo_tagline' => 'Aktif, Kreatif, Kolaboratif',
                    'meaning_title' => 'Makna Identitas Kabinet',
                    'meaning_subtitle' => 'Logo ditampilkan sebagai simbol arah gerak, karakter, dan komitmen kepengurusan.',
                ],
                'sort_order' => 3,
            ],

            self::SECTION_HISTORY => [
                'badge' => 'Sejarah HMPS',
                'title' => 'Perjalanan organisasi',
                'title_highlight' => 'per periode',
                'description' => 'Gambaran perkembangan HMPS RPL dari masa perintisan hingga periode terkini.',
                'sort_order' => 4,
            ],

            self::SECTION_VISION_MISSION => [
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

            self::SECTION_IDENTITY => [
                'badge' => 'Identitas Organisasi',
                'title' => 'Nilai utama',
                'title_highlight' => 'HMPS RPL',
                'description' => 'Nilai yang menjadi identitas dan budaya organisasi HMPS RPL dalam menjalankan peran dan kontribusinya.',
                'sort_order' => 6,
            ],

            self::SECTION_FUNCTION => [
                'badge' => 'Fungsi Utama',
                'title' => 'Peran strategis',
                'title_highlight' => 'HMPS RPL',
                'description' => 'HMPS RPL berperan sebagai penghubung, penggerak, dan wadah pengembangan mahasiswa.',
                'sort_order' => 7,
            ],
        ];
    }

    private function ensureDefaultItems(): void
    {
        foreach ($this->defaultItemsByGroup() as $group => $items) {
            if (ProfileItem::query()->where('group', $group)->exists()) {
                continue;
            }

            foreach ($items as $item) {
                ProfileItem::query()->create([
                    ...$item,
                    'is_active' => true,
                ]);
            }
        }
    }

    private function defaultItemsByGroup(): array
    {
        return [
            self::GROUP_TICKER => [
                ['group' => self::GROUP_TICKER, 'label' => 'Profil HMPS RPL', 'sort_order' => 1],
                ['group' => self::GROUP_TICKER, 'label' => 'Logo Kabinet', 'sort_order' => 2],
                ['group' => self::GROUP_TICKER, 'label' => 'Sejarah Organisasi', 'sort_order' => 3],
                ['group' => self::GROUP_TICKER, 'label' => 'Visi & Misi', 'sort_order' => 4],
                ['group' => self::GROUP_TICKER, 'label' => 'Kolaboratif', 'sort_order' => 5],
                ['group' => self::GROUP_TICKER, 'label' => 'Profesional', 'sort_order' => 6],
                ['group' => self::GROUP_TICKER, 'label' => 'Inovatif', 'sort_order' => 7],
            ],

            self::GROUP_HISTORY => [
                [
                    'group' => self::GROUP_HISTORY,
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

            self::GROUP_MISSION => [
                ['group' => self::GROUP_MISSION, 'description' => 'Mengembangkan potensi mahasiswa RPL melalui berbagai kegiatan akademik maupun non-akademik.', 'sort_order' => 1],
                ['group' => self::GROUP_MISSION, 'description' => 'Membangun kolaborasi dengan industri maupun alumni untuk membuka peluang pengembangan mahasiswa.', 'sort_order' => 2],
                ['group' => self::GROUP_MISSION, 'description' => 'Menciptakan lingkungan yang penuh kekeluargaan serta menumbuhkan tanggung jawab dan kepemimpinan.', 'sort_order' => 3],
                ['group' => self::GROUP_MISSION, 'description' => 'Mengoptimalkan media sosial sebagai wadah dokumentasi, komunikasi, dan promosi HMPS RPL.', 'sort_order' => 4],
            ],

            self::GROUP_IDENTITY => [
                ['group' => self::GROUP_IDENTITY, 'title' => 'Wadah Aspirasi', 'description' => 'Menjadi ruang mahasiswa RPL untuk menyampaikan gagasan, kebutuhan, dan ide secara terbuka dan terarah.', 'sort_order' => 1],
                ['group' => self::GROUP_IDENTITY, 'title' => 'Pengembangan Potensi', 'description' => 'Mendorong peningkatan kemampuan akademik, organisasi, komunikasi, dan kepemimpinan mahasiswa.', 'sort_order' => 2],
                ['group' => self::GROUP_IDENTITY, 'title' => 'Budaya Kolaboratif', 'description' => 'Membangun kebersamaan, komunikasi sehat, dan kerja tim antar mahasiswa RPL.', 'sort_order' => 3],
                ['group' => self::GROUP_IDENTITY, 'title' => 'Kontribusi Nyata', 'description' => 'Menghadirkan kegiatan yang relevan, bermanfaat, dan berdampak bagi lingkungan kampus.', 'sort_order' => 4],
            ],

            self::GROUP_FUNCTION => [
                ['group' => self::GROUP_FUNCTION, 'description' => 'Menjadi wadah aspirasi mahasiswa RPL.', 'sort_order' => 1],
                ['group' => self::GROUP_FUNCTION, 'description' => 'Menyelenggarakan kegiatan yang relevan dan bermanfaat.', 'sort_order' => 2],
                ['group' => self::GROUP_FUNCTION, 'description' => 'Mengembangkan hard skill dan soft skill mahasiswa.', 'sort_order' => 3],
                ['group' => self::GROUP_FUNCTION, 'description' => 'Membangun budaya organisasi yang kolaboratif dan profesional.', 'sort_order' => 4],
            ],
        ];
    }

    private function normalizeTextLines(mixed $value): array
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn($item) => $this->cleanNullableString($item))
                ->filter()
                ->values()
                ->all();
        }

        return collect(preg_split('/\r\n|\r|\n|\|/', (string) ($value ?? '')) ?: [])
            ->map(fn($item) => $this->cleanNullableString($item))
            ->filter()
            ->values()
            ->all();
    }

    private function resolveImageUrl(?string $value): string
    {
        $value = $this->cleanNullableString($value);

        if (! $value) {
            return '';
        }

        if (Str::startsWith($value, ['http://', 'https://', '/'])) {
            return $value;
        }

        if (Str::startsWith($value, ['storage/', 'images/', 'Images/', 'uploads/'])) {
            return asset($value);
        }

        return Storage::url($value);
    }

    private function normalizeMeta(mixed $meta): array
    {
        if (! is_array($meta)) {
            return [];
        }

        return collect($meta)
            ->map(fn($value) => is_string($value) ? trim($value) : $value)
            ->filter(fn($value) => $value !== null && $value !== '')
            ->all();
    }

    private function cleanNullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
