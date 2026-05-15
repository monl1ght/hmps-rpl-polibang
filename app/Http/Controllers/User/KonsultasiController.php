<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ClientTestimonial;
use App\Models\ConsultationAdmin;
use App\Models\ConsultationSection;
use App\Models\ConsultationStep;
use App\Models\ConsultationTicker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KonsultasiController extends Controller
{
    public function index(): Response
    {
        $this->ensureDefaultConsultationData();

        $hero = ConsultationSection::query()
            ->active()
            ->where('key', 'hero')
            ->first();

        return Inertia::render('user/pages/Konsultasi', [
            'consultationHero' => $hero ? [
                'id' => $hero->id,
                'badge' => $hero->badge,
                'title' => $hero->title,
                'titleHighlight' => $hero->title_highlight,
                'description' => $hero->description,
                'primaryButtonLabel' => $hero->primary_button_label,
                'primaryButtonUrl' => $hero->primary_button_url,
                'secondaryButtonLabel' => $hero->secondary_button_label,
                'secondaryButtonUrl' => $hero->secondary_button_url,
                'meta' => $this->heroMeta($hero),
            ] : null,

            'admins' => ConsultationAdmin::query()
                ->active()
                ->ordered()
                ->get()
                ->map(fn(ConsultationAdmin $admin) => [
                    'id' => $admin->id,
                    'label' => $admin->label,
                    'name' => $admin->name,
                    'phoneDisplay' => $admin->phone_display ?: $admin->phone_wa,
                    'phoneWa' => $this->normalizeWhatsappNumber($admin->phone_wa),
                    'role' => $admin->role,
                    'emoji' => $admin->emoji ?: '💬',
                    'skills' => $this->normalizeSkills($admin->skills, $admin->role),
                    'sortOrder' => (int) $admin->sort_order,
                ])
                ->values()
                ->all(),

            'ticks' => ConsultationTicker::query()
                ->active()
                ->ordered()
                ->pluck('label')
                ->values()
                ->all(),

            'steps' => ConsultationStep::query()
                ->active()
                ->ordered()
                ->get()
                ->map(fn(ConsultationStep $step) => [
                    'id' => $step->id,
                    'number' => $step->number,
                    'title' => $step->title,
                    'description' => $step->description,
                ])
                ->values()
                ->all(),

            'testimonials' => $this->consultationTestimonials(),
        ]);
    }

    public function storeTestimonial(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'role' => ['nullable', 'string', 'max:160'],
            'service_type' => ['required', 'string', 'max:160'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'message' => ['required', 'string', 'min:10', 'max:1200'],
            'emoji' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 160 karakter.',
            'service_type.required' => 'Jenis konsultasi wajib dipilih.',
            'rating.required' => 'Rating wajib dipilih.',
            'rating.integer' => 'Rating harus berupa angka.',
            'rating.min' => 'Rating minimal 1 bintang.',
            'rating.max' => 'Rating maksimal 5 bintang.',
            'message.required' => 'Isi testimoni wajib diisi.',
            'message.min' => 'Testimoni minimal 10 karakter.',
            'message.max' => 'Testimoni maksimal 1200 karakter.',
        ]);

        $name = trim((string) $validated['name']);
        $role = trim((string) ($validated['role'] ?? ''));
        $serviceType = trim((string) $validated['service_type']);
        $message = trim((string) $validated['message']);
        $emoji = trim((string) ($validated['emoji'] ?? ''));

        ClientTestimonial::create([
            'name' => $name,
            'role' => $role !== '' ? $role : 'Pengguna Layanan',
            'service_type' => $serviceType !== '' ? $serviceType : 'Konsultasi HMPS RPL',
            'source_page' => 'konsultasi',
            'rating' => $this->normalizeRating($validated['rating']),
            'message' => $message,
            'emoji' => $emoji !== '' ? $emoji : '💬',

            // Penting:
            // Testimoni dari user tidak langsung tampil.
            // Admin harus approve melalui Custom Konsultasi.
            'is_approved' => false,

            'sort_order' => 0,
        ]);

        return back()->with(
            'success',
            'Terima kasih! Testimoni Anda berhasil dikirim dan akan ditinjau oleh admin sebelum ditampilkan.'
        );
    }

    private function heroMeta(ConsultationSection $hero): array
    {
        $meta = $hero->meta ?? [];

        return [
            'status_card_title' => $meta['status_card_title'] ?? 'Status Layanan',
            'status_card_subtitle' => $meta['status_card_subtitle'] ?? 'Layanan konsultasi dibuka sesuai jadwal operasional.',
            'schedule_card_title' => $meta['schedule_card_title'] ?? 'Jam Operasional',
            'schedule_card_subtitle' => $meta['schedule_card_subtitle'] ?? 'Senin - Jumat, 08.00 - 20.00 WIB',

            'service_days' => $this->normalizeServiceDays(
                $meta['service_days'] ?? ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']
            ),
            'service_day_label' => $meta['service_day_label'] ?? 'Senin - Jumat',
            'service_start_time' => $this->normalizeTime($meta['service_start_time'] ?? '08:00', '08:00'),
            'service_end_time' => $this->normalizeTime($meta['service_end_time'] ?? '20:00', '20:00'),

            'admin_section_badge' => $meta['admin_section_badge'] ?? 'Pilih Admin',
            'admin_section_title' => $meta['admin_section_title'] ?? 'Pilih admin berdasarkan skill',
            'admin_section_highlight' => $meta['admin_section_highlight'] ?? 'yang kamu butuhkan',
            'admin_section_description' => $meta['admin_section_description'] ?? 'Setiap admin memiliki bidang keahlian berbeda. Pilih admin yang paling sesuai dengan kebutuhan konsultasi Anda.',

            'whatsapp_template' => $meta['whatsapp_template'] ?? $this->defaultWhatsappTemplate(),
        ];
    }

    private function consultationTestimonials(): array
    {
        return ClientTestimonial::query()
            ->approved()
            ->consultation()
            ->ordered()
            ->limit(30)
            ->get()
            ->map(fn(ClientTestimonial $testimonial) => [
                'id' => $testimonial->id,
                'name' => $testimonial->name ?: 'Pengguna Konsultasi',
                'role' => $testimonial->role ?: 'Pengguna Layanan',
                'service' => $testimonial->service_type ?: 'Konsultasi HMPS RPL',
                'rating' => $this->normalizeRating($testimonial->rating),
                'message' => $testimonial->message ?: 'Testimoni belum memiliki isi.',
                'createdAt' => optional($testimonial->created_at)->diffForHumans() ?: 'Baru saja',
                'emoji' => $testimonial->emoji ?: '💬',
                'is_approved' => (bool) $testimonial->is_approved,
            ])
            ->values()
            ->all();
    }

    private function normalizeSkills(?array $skills, ?string $role = null): array
    {
        $cleanSkills = collect($skills ?? [])
            ->map(fn($skill) => trim((string) $skill))
            ->filter()
            ->reject(fn($skill) => preg_match('/^hari\s+/i', $skill))
            ->values()
            ->all();

        if ($cleanSkills) {
            return $cleanSkills;
        }

        $roleText = trim((string) $role);
        $roleText = preg_replace('/^admin\s+konsultasi\s*/i', '', $roleText) ?: '';

        $fromRole = collect(preg_split('/[,;|\n]/', $roleText))
            ->map(fn($skill) => trim((string) $skill))
            ->filter()
            ->reject(fn($skill) => preg_match('/^hari\s+/i', $skill))
            ->values()
            ->all();

        return $fromRole ?: ['Konsultasi Umum'];
    }

    private function normalizeServiceDays(mixed $days): array
    {
        $allowedDays = [
            'Senin',
            'Selasa',
            'Rabu',
            'Kamis',
            'Jumat',
            'Sabtu',
            'Minggu',
        ];

        if (is_array($days)) {
            $filtered = collect($days)
                ->map(fn($day) => trim((string) $day))
                ->filter(fn($day) => in_array($day, $allowedDays, true))
                ->values()
                ->all();

            return $filtered ?: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        }

        $filtered = collect(preg_split('/[,;|]/', (string) $days))
            ->map(fn($day) => trim((string) $day))
            ->filter(fn($day) => in_array($day, $allowedDays, true))
            ->values()
            ->all();

        return $filtered ?: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
    }

    private function normalizeTime(?string $time, string $fallback = '08:00'): string
    {
        $time = trim((string) $time);

        if (preg_match('/^\d{2}:\d{2}/', $time)) {
            return substr($time, 0, 5);
        }

        if (preg_match('/^\d{2}\.\d{2}/', $time)) {
            return str_replace('.', ':', substr($time, 0, 5));
        }

        return $fallback;
    }

    private function normalizeWhatsappNumber(?string $phone): string
    {
        $number = preg_replace('/[^0-9]/', '', (string) $phone) ?: '';

        if (str_starts_with($number, '0')) {
            return '62' . substr($number, 1);
        }

        if (str_starts_with($number, '8')) {
            return '62' . $number;
        }

        return $number;
    }

    private function normalizeRating(mixed $rating): int
    {
        $rating = (int) $rating;

        if ($rating < 1) {
            return 5;
        }

        if ($rating > 5) {
            return 5;
        }

        return $rating;
    }

    private function defaultWhatsappTemplate(): string
    {
        return "Halo {admin_name}, saya ingin konsultasi melalui layanan HMPS RPL.\n\nSaya memilih konsultasi dengan:\nNama Admin: {admin_name}\nBidang/Skill: {admin_skills}\n\nMohon informasinya ya. Terima kasih.";
    }

    private function ensureDefaultConsultationData(): void
    {
        $this->ensureDefaultSection();
        $this->ensureDefaultAdmins();
        $this->ensureDefaultSteps();
        $this->ensureDefaultTickers();
    }

    private function ensureDefaultSection(): void
    {
        ConsultationSection::query()->firstOrCreate(
            ['key' => 'hero'],
            [
                'badge' => 'Layanan Konsultasi HMPS RPL',
                'title' => 'Pilih admin konsultasi',
                'title_highlight' => 'sesuai kebutuhanmu',
                'description' => 'Pilih admin HMPS RPL berdasarkan bidang keahlian seperti website, desain grafis, editing video, instalasi software, atau kebutuhan konsultasi lainnya. Layanan konsultasi tersedia pada hari kerja sesuai jam operasional.',
                'primary_button_label' => 'Pilih Admin Konsultasi',
                'primary_button_url' => '#pilih-admin',
                'secondary_button_label' => 'Konsultasi via WhatsApp',
                'secondary_button_url' => 'whatsapp-selected',
                'meta' => [
                    'status_card_title' => 'Status Layanan',
                    'status_card_subtitle' => 'Layanan konsultasi dibuka sesuai jadwal operasional.',
                    'schedule_card_title' => 'Jam Operasional',
                    'schedule_card_subtitle' => 'Senin - Jumat, 08.00 - 20.00 WIB',
                    'service_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
                    'service_day_label' => 'Senin - Jumat',
                    'service_start_time' => '08:00',
                    'service_end_time' => '20:00',
                    'admin_section_badge' => 'Pilih Admin',
                    'admin_section_title' => 'Pilih admin berdasarkan skill',
                    'admin_section_highlight' => 'yang kamu butuhkan',
                    'admin_section_description' => 'Setiap admin memiliki bidang keahlian berbeda. Pilih admin yang paling sesuai dengan kebutuhan konsultasi Anda.',
                    'whatsapp_template' => $this->defaultWhatsappTemplate(),
                ],
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
    }

    private function ensureDefaultAdmins(): void
    {
        if (ConsultationAdmin::query()->exists()) {
            return;
        }

        $admins = [
            [
                'label' => 'Admin 1',
                'name' => 'Robby Bagas Rifandi',
                'phone_display' => '082151155503',
                'phone_wa' => '6282151155503',
                'role' => 'Admin Konsultasi',
                'emoji' => '🌐',
                'skills' => ['Website', 'Teknis'],
                'sort_order' => 1,
            ],
            [
                'label' => 'Admin 2',
                'name' => 'Muhammad Bahauddin Najib',
                'phone_display' => '087879175646',
                'phone_wa' => '6287879175646',
                'role' => 'Admin Konsultasi',
                'emoji' => '🛠️',
                'skills' => ['Website', 'Desain Grafis', 'Editing Video'],
                'sort_order' => 2,
            ],
            [
                'label' => 'Admin 3',
                'name' => "Muhammad Zidni Ma'ruf",
                'phone_display' => '085865181534',
                'phone_wa' => '6285865181534',
                'role' => 'Admin Konsultasi',
                'emoji' => '⚙️',
                'skills' => ['Teknis', 'Website'],
                'sort_order' => 3,
            ],
            [
                'label' => 'Admin 4',
                'name' => 'Rihmatul Wildania',
                'phone_display' => '081390139460',
                'phone_wa' => '6281390139460',
                'role' => 'Admin Konsultasi',
                'emoji' => '🎨',
                'skills' => ['Desain Grafis'],
                'sort_order' => 4,
            ],
            [
                'label' => 'Admin 5',
                'name' => 'Ikhwan Maulana Riski',
                'phone_display' => '085290531268',
                'phone_wa' => '6285290531268',
                'role' => 'Admin Konsultasi',
                'emoji' => '💬',
                'skills' => ['Desain Grafis', 'Konsultasi Umum'],
                'sort_order' => 5,
            ],
        ];

        foreach ($admins as $admin) {
            ConsultationAdmin::create([
                ...$admin,
                'day' => 'Senin',
                'start_time' => '08:00',
                'end_time' => '20:00',
                'badge' => null,
                'is_active' => true,
            ]);
        }
    }

    private function ensureDefaultSteps(): void
    {
        if (ConsultationStep::query()->exists()) {
            return;
        }

        $steps = [
            [
                'number' => '01',
                'title' => 'Lihat skill admin',
                'description' => 'Pengguna dapat melihat bidang keahlian setiap admin, misalnya Website, Desain Grafis, Editor Video, Instalasi Software, atau bidang lainnya.',
                'sort_order' => 1,
            ],
            [
                'number' => '02',
                'title' => 'Pilih admin konsultasi',
                'description' => 'Pengguna dapat memilih admin yang paling sesuai dengan kebutuhan konsultasi sebelum diarahkan ke WhatsApp.',
                'sort_order' => 2,
            ],
            [
                'number' => '03',
                'title' => 'Konsultasi sesuai jam layanan',
                'description' => 'Layanan konsultasi dibuka Senin sampai Jumat pukul 08.00 WIB sampai 20.00 WIB dan dapat diubah melalui menu Custom Konsultasi.',
                'sort_order' => 3,
            ],
        ];

        foreach ($steps as $step) {
            ConsultationStep::create([
                ...$step,
                'is_active' => true,
            ]);
        }
    }

    private function ensureDefaultTickers(): void
    {
        if (ConsultationTicker::query()->exists()) {
            return;
        }

        $tickers = [
            'KONSULTASI HMPS RPL',
            'PILIH ADMIN',
            'BERDASARKAN SKILL',
            'SENIN - JUMAT',
            '08.00 - 20.00 WIB',
            'WEBSITE',
            'DESAIN GRAFIS',
            'EDITOR VIDEO',
            'LANGSUNG WHATSAPP',
        ];

        foreach ($tickers as $index => $label) {
            ConsultationTicker::create([
                'label' => $label,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }
}
