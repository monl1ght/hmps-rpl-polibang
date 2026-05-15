<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientTestimonial;
use App\Models\ConsultationAdmin;
use App\Models\ConsultationSection;
use App\Models\ConsultationStep;
use App\Models\ConsultationTicker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ConsultationCustomizationController extends Controller
{
    public function index(): Response
    {
        $this->ensureDefaultConsultationData();

        return Inertia::render('admin/pages/ConsultationCustomization', [
            'sections' => ConsultationSection::query()
                ->ordered()
                ->get()
                ->map(fn(ConsultationSection $section) => [
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
                    'meta' => $this->normalizedSectionMeta($section),
                    'is_active' => (bool) $section->is_active,
                    'sort_order' => (int) $section->sort_order,
                ])
                ->values()
                ->all(),

            'admins' => ConsultationAdmin::query()
                ->ordered()
                ->get()
                ->map(fn(ConsultationAdmin $admin) => [
                    'id' => $admin->id,
                    'label' => $admin->label,
                    'name' => $admin->name,
                    'phone_display' => $admin->phone_display,
                    'phone_wa' => $admin->phone_wa,
                    'role' => $admin->role,
                    'emoji' => $admin->emoji,
                    'badge' => $admin->badge,
                    'skills' => $admin->skills ?? [],
                    'skills_text' => implode("\n", $admin->skills ?? []),

                    // Legacy field: tetap dikirim agar aman dengan struktur lama,
                    // tetapi jadwal per admin tidak dipakai lagi di halaman user.
                    'day' => $admin->day,
                    'start_time' => substr((string) $admin->start_time, 0, 5),
                    'end_time' => substr((string) $admin->end_time, 0, 5),

                    'is_active' => (bool) $admin->is_active,
                    'sort_order' => (int) $admin->sort_order,
                ])
                ->values()
                ->all(),

            'steps' => ConsultationStep::query()
                ->ordered()
                ->get()
                ->map(fn(ConsultationStep $step) => [
                    'id' => $step->id,
                    'number' => $step->number,
                    'title' => $step->title,
                    'description' => $step->description,
                    'is_active' => (bool) $step->is_active,
                    'sort_order' => (int) $step->sort_order,
                ])
                ->values()
                ->all(),

            'tickers' => ConsultationTicker::query()
                ->ordered()
                ->get()
                ->map(fn(ConsultationTicker $ticker) => [
                    'id' => $ticker->id,
                    'label' => $ticker->label,
                    'is_active' => (bool) $ticker->is_active,
                    'sort_order' => (int) $ticker->sort_order,
                ])
                ->values()
                ->all(),

            'testimonials' => $this->testimonialPayload(),

            'serviceDayOptions' => $this->dayOptions(),
        ]);
    }

    public function updateSection(Request $request, ConsultationSection $consultationSection): RedirectResponse
    {
        $validated = $request->validate($this->sectionRules());

        $consultationSection->update([
            'badge' => $validated['badge'] ?? null,
            'title' => $validated['title'] ?? null,
            'title_highlight' => $validated['title_highlight'] ?? null,
            'description' => $validated['description'] ?? null,
            'primary_button_label' => $validated['primary_button_label'] ?? null,
            'primary_button_url' => $validated['primary_button_url'] ?? null,
            'secondary_button_label' => $validated['secondary_button_label'] ?? null,
            'secondary_button_url' => $validated['secondary_button_url'] ?? null,
            'meta' => $this->normalizeSectionMeta($validated['meta'] ?? []),
            'is_active' => $validated['is_active'],
            'sort_order' => $validated['sort_order'],
        ]);

        return back()->with('success', 'Section konsultasi berhasil diperbarui.');
    }

    public function storeAdmin(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->adminRules());

        $phoneWa = $this->normalizeWhatsappNumber($validated['phone_wa']);
        $phoneDisplay = trim((string) ($validated['phone_display'] ?? ''));

        ConsultationAdmin::create([
            'label' => $validated['label'] ?? null,
            'name' => $validated['name'],
            'day' => $validated['day'] ?? 'Senin',
            'start_time' => $validated['start_time'] ?? '08:00',
            'end_time' => $validated['end_time'] ?? '20:00',
            'phone_display' => $phoneDisplay !== '' ? $phoneDisplay : $phoneWa,
            'phone_wa' => $phoneWa,
            'role' => $validated['role'] ?? 'Admin Konsultasi',
            'emoji' => $validated['emoji'] ?? '💬',
            'badge' => $validated['badge'] ?? null,
            'skills' => $this->linesToArray($validated['skills_text'] ?? null),
            'is_active' => $validated['is_active'],
            'sort_order' => $validated['sort_order'],
        ]);

        return back()->with('success', 'Admin konsultasi berhasil ditambahkan.');
    }

    public function updateAdmin(Request $request, ConsultationAdmin $consultationAdmin): RedirectResponse
    {
        $validated = $request->validate($this->adminRules());

        $phoneWa = $this->normalizeWhatsappNumber($validated['phone_wa']);
        $phoneDisplay = trim((string) ($validated['phone_display'] ?? ''));

        $consultationAdmin->update([
            'label' => $validated['label'] ?? null,
            'name' => $validated['name'],
            'day' => $validated['day'] ?? $consultationAdmin->day ?? 'Senin',
            'start_time' => $validated['start_time'] ?? $consultationAdmin->start_time ?? '08:00',
            'end_time' => $validated['end_time'] ?? $consultationAdmin->end_time ?? '20:00',
            'phone_display' => $phoneDisplay !== '' ? $phoneDisplay : $phoneWa,
            'phone_wa' => $phoneWa,
            'role' => $validated['role'] ?? 'Admin Konsultasi',
            'emoji' => $validated['emoji'] ?? '💬',
            'badge' => $validated['badge'] ?? null,
            'skills' => $this->linesToArray($validated['skills_text'] ?? null),
            'is_active' => $validated['is_active'],
            'sort_order' => $validated['sort_order'],
        ]);

        return back()->with('success', 'Admin konsultasi berhasil diperbarui.');
    }

    public function destroyAdmin(ConsultationAdmin $consultationAdmin): RedirectResponse
    {
        $consultationAdmin->delete();

        return back()->with('success', 'Admin konsultasi berhasil dihapus.');
    }

    public function storeStep(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->stepRules());

        ConsultationStep::create($validated);

        return back()->with('success', 'Step konsultasi berhasil ditambahkan.');
    }

    public function updateStep(Request $request, ConsultationStep $consultationStep): RedirectResponse
    {
        $validated = $request->validate($this->stepRules());

        $consultationStep->update($validated);

        return back()->with('success', 'Step konsultasi berhasil diperbarui.');
    }

    public function destroyStep(ConsultationStep $consultationStep): RedirectResponse
    {
        $consultationStep->delete();

        return back()->with('success', 'Step konsultasi berhasil dihapus.');
    }

    public function storeTicker(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->tickerRules());

        ConsultationTicker::create($validated);

        return back()->with('success', 'Ticker konsultasi berhasil ditambahkan.');
    }

    public function updateTicker(Request $request, ConsultationTicker $consultationTicker): RedirectResponse
    {
        $validated = $request->validate($this->tickerRules());

        $consultationTicker->update($validated);

        return back()->with('success', 'Ticker konsultasi berhasil diperbarui.');
    }

    public function destroyTicker(ConsultationTicker $consultationTicker): RedirectResponse
    {
        $consultationTicker->delete();

        return back()->with('success', 'Ticker konsultasi berhasil dihapus.');
    }

    public function storeTestimonial(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->testimonialRules());

        ClientTestimonial::create([
            'name' => $validated['name'],
            'role' => $validated['role'] ?? 'Pengguna Layanan',
            'service_type' => $validated['service_type'],
            'source_page' => 'konsultasi',
            'rating' => (int) $validated['rating'],
            'message' => $validated['message'],
            'emoji' => $validated['emoji'] ?? '💬',
            'is_approved' => $validated['is_approved'],
            'sort_order' => $validated['sort_order'],
        ]);

        return back()->with('success', 'Testimoni konsultasi berhasil ditambahkan.');
    }

    public function updateTestimonial(Request $request, ClientTestimonial $clientTestimonial): RedirectResponse
    {
        $this->ensureConsultationTestimonial($clientTestimonial);

        $validated = $request->validate($this->testimonialRules());

        $clientTestimonial->update([
            'name' => $validated['name'],
            'role' => $validated['role'] ?? 'Pengguna Layanan',
            'service_type' => $validated['service_type'],
            'source_page' => 'konsultasi',
            'rating' => (int) $validated['rating'],
            'message' => $validated['message'],
            'emoji' => $validated['emoji'] ?? '💬',
            'is_approved' => $validated['is_approved'],
            'sort_order' => $validated['sort_order'],
        ]);

        return back()->with('success', 'Testimoni konsultasi berhasil diperbarui.');
    }
    public function approveTestimonial(ClientTestimonial $clientTestimonial): RedirectResponse
    {
        $this->ensureConsultationTestimonial($clientTestimonial);

        $clientTestimonial->update([
            'is_approved' => true,
        ]);

        return back()->with('success', 'Testimoni berhasil disetujui dan ditampilkan di halaman user.');
    }

    public function unapproveTestimonial(ClientTestimonial $clientTestimonial): RedirectResponse
    {
        $this->ensureConsultationTestimonial($clientTestimonial);

        $clientTestimonial->update([
            'is_approved' => false,
        ]);

        return back()->with('success', 'Testimoni berhasil disembunyikan dari halaman user.');
    }

    public function destroyTestimonial(ClientTestimonial $clientTestimonial): RedirectResponse
    {
        $this->ensureConsultationTestimonial($clientTestimonial);

        $clientTestimonial->delete();

        return back()->with('success', 'Testimoni konsultasi berhasil dihapus.');
    }

    private function sectionRules(): array
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

            'meta' => ['nullable', 'array'],
            'meta.status_card_title' => ['nullable', 'string', 'max:160'],
            'meta.status_card_subtitle' => ['nullable', 'string', 'max:300'],
            'meta.schedule_card_title' => ['nullable', 'string', 'max:160'],
            'meta.schedule_card_subtitle' => ['nullable', 'string', 'max:300'],
            'meta.service_days' => ['nullable', 'array'],
            'meta.service_days.*' => ['nullable', Rule::in($this->dayOptions())],
            'meta.service_day_label' => ['nullable', 'string', 'max:120'],
            'meta.service_start_time' => ['nullable', 'date_format:H:i'],
            'meta.service_end_time' => ['nullable', 'date_format:H:i', 'after:meta.service_start_time'],
            'meta.admin_section_badge' => ['nullable', 'string', 'max:120'],
            'meta.admin_section_title' => ['nullable', 'string', 'max:180'],
            'meta.admin_section_highlight' => ['nullable', 'string', 'max:180'],
            'meta.admin_section_description' => ['nullable', 'string', 'max:1000'],
            'meta.whatsapp_template' => ['nullable', 'string', 'max:3000'],

            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    private function adminRules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:160'],

            // Legacy field. Tidak dipakai lagi di halaman user,
            // tetapi tetap diterima agar aman dengan database lama.
            'day' => ['nullable', Rule::in($this->dayOptions())],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],

            'phone_display' => ['nullable', 'string', 'max:30'],
            'phone_wa' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'role' => ['nullable', 'string', 'max:160'],
            'emoji' => ['nullable', 'string', 'max:20'],
            'badge' => ['nullable', 'string', 'max:120'],
            'skills_text' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    private function stepRules(): array
    {
        return [
            'number' => ['nullable', 'string', 'max:20'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    private function tickerRules(): array
    {
        return [
            'label' => ['required', 'string', 'max:160'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    private function testimonialRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'role' => ['nullable', 'string', 'max:160'],
            'service_type' => ['required', 'string', 'max:160'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'message' => ['required', 'string', 'max:2000'],
            'emoji' => ['nullable', 'string', 'max:20'],
            'is_approved' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    private function normalizedSectionMeta(ConsultationSection $section): array
    {
        return $this->normalizeSectionMeta($section->meta ?? []);
    }

    private function normalizeSectionMeta(?array $meta): array
    {
        $meta = $meta ?? [];

        return [
            'status_card_title' => $meta['status_card_title'] ?? 'Status Layanan',
            'status_card_subtitle' => $meta['status_card_subtitle'] ?? 'Layanan konsultasi dibuka sesuai jadwal operasional.',
            'schedule_card_title' => $meta['schedule_card_title'] ?? 'Jam Operasional',
            'schedule_card_subtitle' => $meta['schedule_card_subtitle'] ?? 'Senin - Jumat, 08.00 - 20.00 WIB',
            'service_days' => $this->normalizeServiceDays(
                $meta['service_days'] ?? ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']
            ),
            'service_day_label' => $meta['service_day_label'] ?? 'Senin - Jumat',
            'service_start_time' => $this->normalizeTime($meta['service_start_time'] ?? '08:00'),
            'service_end_time' => $this->normalizeTime($meta['service_end_time'] ?? '20:00'),
            'admin_section_badge' => $meta['admin_section_badge'] ?? 'Pilih Admin',
            'admin_section_title' => $meta['admin_section_title'] ?? 'Pilih admin berdasarkan skill',
            'admin_section_highlight' => $meta['admin_section_highlight'] ?? 'yang kamu butuhkan',
            'admin_section_description' => $meta['admin_section_description'] ?? 'Setiap admin memiliki bidang keahlian berbeda. Pilih admin yang paling sesuai dengan kebutuhan konsultasi Anda.',
            'whatsapp_template' => $meta['whatsapp_template'] ?? $this->defaultWhatsappTemplate(),
        ];
    }

    private function testimonialPayload(): array
    {
        return ClientTestimonial::query()
            ->where('source_page', 'konsultasi')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn(ClientTestimonial $testimonial) => [
                'id' => $testimonial->id,
                'name' => $testimonial->name,
                'role' => $testimonial->role ?: 'Pengguna Layanan',
                'service_type' => $testimonial->service_type ?: 'Konsultasi HMPS RPL',
                'rating' => $this->normalizeRating($testimonial->rating),
                'message' => $testimonial->message,
                'emoji' => $testimonial->emoji ?: '💬',
                'is_approved' => (bool) $testimonial->is_approved,
                'sort_order' => (int) $testimonial->sort_order,
                'created_at' => optional($testimonial->created_at)->format('d M Y H:i'),
            ])
            ->values()
            ->all();
    }

    private function normalizeServiceDays(mixed $days): array
    {
        if (! is_array($days)) {
            $days = preg_split('/[,;|]/', (string) $days) ?: [];
        }

        $filtered = collect($days)
            ->map(fn($day) => trim((string) $day))
            ->filter(fn($day) => in_array($day, $this->dayOptions(), true))
            ->values()
            ->all();

        return $filtered ?: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
    }

    private function normalizeTime(?string $time): string
    {
        $time = trim((string) $time);

        if (preg_match('/^\d{2}:\d{2}/', $time)) {
            return substr($time, 0, 5);
        }

        if (preg_match('/^\d{2}\.\d{2}/', $time)) {
            return str_replace('.', ':', substr($time, 0, 5));
        }

        return '08:00';
    }

    private function linesToArray(?string $value): array
    {
        return collect(preg_split('/[\n,;|]/', (string) $value))
            ->map(fn($item) => trim((string) $item))
            ->filter()
            ->reject(fn($item) => preg_match('/^hari\s+/i', $item))
            ->unique()
            ->values()
            ->all();
    }

    private function dayOptions(): array
    {
        return [
            'Senin',
            'Selasa',
            'Rabu',
            'Kamis',
            'Jumat',
            'Sabtu',
            'Minggu',
        ];
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

    private function ensureConsultationTestimonial(ClientTestimonial $clientTestimonial): void
    {
        abort_if($clientTestimonial->source_page !== 'konsultasi', 404);
    }

    private function defaultWhatsappTemplate(): string
    {
        return "Halo {admin_name}, saya ingin konsultasi melalui layanan HMPS RPL.\n\nSaya memilih konsultasi dengan:\nNama Admin: {admin_name}\nBidang/Skill: {admin_skills}\n\nMohon informasinya ya. Terima kasih.";
    }

    private function ensureDefaultConsultationData(): void
    {
        $this->ensureDefaultSections();
        $this->ensureDefaultAdmins();
        $this->ensureDefaultSteps();
        $this->ensureDefaultTickers();
    }

    private function ensureDefaultSections(): void
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
