<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ProfileHeroImage;
use App\Models\ProfileItem;
use App\Models\ProfileSection;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function index(): Response
    {
        $this->ensureDefaultProfileContent();

        $sections = ProfileSection::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->keyBy('key');

        return Inertia::render('user/pages/Profil', [
            'profileHero' => $this->section($sections, 'hero'),
            'profileAbout' => $this->section($sections, 'about'),
            'profileHistory' => $this->section($sections, 'history'),
            'profileVisionMission' => $this->section($sections, 'vision_mission'),
            'profileIdentity' => $this->section($sections, 'identity'),
            'profileFunction' => $this->section($sections, 'function'),

            'profileHeroImages' => $this->profileHeroImages(),

            'ticks' => $this->profileItemsByGroup('ticker')
                ->pluck('label')
                ->filter()
                ->values()
                ->all(),

            'periods' => $this->profileItemsByGroup('history')
                ->map(fn(ProfileItem $item) => [
                    'id' => $item->id,
                    'periode' => $item->label,
                    'title' => $item->title,
                    'ketua' => data_get($item->meta, 'ketua'),
                    'wakil' => data_get($item->meta, 'wakil'),
                    'description' => $item->description,
                ])
                ->values()
                ->all(),

            'missions' => $this->profileItemsByGroup('mission')
                ->pluck('description')
                ->filter()
                ->values()
                ->all(),

            'identityCards' => $this->profileItemsByGroup('identity')
                ->map(fn(ProfileItem $item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'desc' => $item->description,
                ])
                ->values()
                ->all(),

            'functionPoints' => $this->profileItemsByGroup('function')
                ->pluck('description')
                ->filter()
                ->values()
                ->all(),
        ]);
    }

    private function section($sections, string $key): array
    {
        $section = $sections->get($key);

        if (! $section) {
            return [];
        }

        return [
            'id' => $section->id,
            'key' => $section->key,
            'badge' => $section->badge,
            'title' => $section->title,
            'titleHighlight' => $section->title_highlight,
            'description' => $section->description,
            'primaryButtonLabel' => $section->primary_button_label,
            'primaryButtonUrl' => $section->primary_button_url,
            'meta' => $section->meta ?? [],
        ];
    }

    private function profileHeroImages(): array
    {
        return ProfileHeroImage::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->take(4)
            ->get()
            ->map(fn(ProfileHeroImage $image) => [
                'id' => $image->id,
                'title' => $image->title,
                'image' => $image->image_url,
                'alt' => $image->alt_text ?: ($image->title ?: 'Foto profil HMPS RPL'),
                'is_active' => (bool) $image->is_active,
                'sort_order' => (int) $image->sort_order,
            ])
            ->values()
            ->all();
    }

    private function profileItemsByGroup(string $group)
    {
        return ProfileItem::query()
            ->where('is_active', true)
            ->where('group', $group)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
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

    private function ensureDefaultItems(): void
    {
        $defaultsByGroup = [
            'ticker' => [
                [
                    'group' => 'ticker',
                    'label' => 'Profil HMPS RPL',
                    'sort_order' => 1,
                ],
                [
                    'group' => 'ticker',
                    'label' => 'Sejarah Organisasi',
                    'sort_order' => 2,
                ],
                [
                    'group' => 'ticker',
                    'label' => 'Visi & Misi',
                    'sort_order' => 3,
                ],
                [
                    'group' => 'ticker',
                    'label' => 'Kolaboratif',
                    'sort_order' => 4,
                ],
                [
                    'group' => 'ticker',
                    'label' => 'Profesional',
                    'sort_order' => 5,
                ],
                [
                    'group' => 'ticker',
                    'label' => 'Inovatif',
                    'sort_order' => 6,
                ],
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
}
