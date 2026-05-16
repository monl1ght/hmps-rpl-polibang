<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientTestimonial;
use App\Models\ServiceCatalog;
use App\Models\ServicePackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ServiceCustomizationController extends Controller
{
    private const TESTIMONIAL_SOURCE_PAGE = 'layanan_jasa';

    private const ICON_OPTIONS = [
        ['value' => 'globe', 'label' => 'Globe / Website'],
        ['value' => 'monitor', 'label' => 'Monitor / Instalasi'],
        ['value' => 'sparkles', 'label' => 'Sparkles / Desain Video'],
    ];

    public function index(): Response
    {
        $this->ensureBaseServices();
        $this->attachLegacyPackagesToWebsite();

        return Inertia::render('admin/pages/ServiceCustomization', [
            'services' => $this->servicePayloads(),
            'packages' => $this->packagePayloads(),
            'testimonials' => $this->testimonialPayloads(),
            'iconOptions' => self::ICON_OPTIONS,
        ]);
    }

    public function storeService(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            $this->serviceRules(),
            $this->serviceMessages(),
            $this->serviceAttributes()
        );

        ServiceCatalog::create([
            'title' => $this->cleanString($validated['title']),
            'slug' => $this->resolveServiceSlug($validated['title'], $validated['slug'] ?? null),
            'badge' => $this->nullableString($validated['badge'] ?? null),
            'icon' => $this->validIcon($validated['icon'] ?? null),
            'summary' => $this->nullableString($validated['summary'] ?? null),
            'features' => $this->linesToArray($validated['features_text'] ?? null),
            'cta' => $this->nullableString($validated['cta'] ?? null),
            'whatsapp_text' => $this->nullableString($validated['whatsapp_text'] ?? null),
            'is_active' => (bool) $validated['is_active'],
            'sort_order' => (int) $validated['sort_order'],
        ]);

        return back()->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function updateService(Request $request, ServiceCatalog $serviceCatalog): RedirectResponse
    {
        $validated = $request->validate(
            $this->serviceRules($serviceCatalog),
            $this->serviceMessages(),
            $this->serviceAttributes()
        );

        $serviceCatalog->update([
            'title' => $this->cleanString($validated['title']),
            'slug' => $this->resolveServiceSlug(
                $validated['title'],
                $validated['slug'] ?? null,
                (int) $serviceCatalog->id
            ),
            'badge' => $this->nullableString($validated['badge'] ?? null),
            'icon' => $this->validIcon($validated['icon'] ?? null),
            'summary' => $this->nullableString($validated['summary'] ?? null),
            'features' => $this->linesToArray($validated['features_text'] ?? null),
            'cta' => $this->nullableString($validated['cta'] ?? null),
            'whatsapp_text' => $this->nullableString($validated['whatsapp_text'] ?? null),
            'is_active' => (bool) $validated['is_active'],
            'sort_order' => (int) $validated['sort_order'],
        ]);

        return back()->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroyService(ServiceCatalog $serviceCatalog): RedirectResponse
    {
        if ($serviceCatalog->packages()->exists()) {
            return back()->with('error', 'Layanan tidak bisa dihapus karena masih memiliki paket layanan.');
        }

        $serviceCatalog->delete();

        return back()->with('success', 'Layanan berhasil dihapus.');
    }

    public function storePackage(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            $this->packageRules(),
            $this->packageMessages(),
            $this->packageAttributes()
        );

        ServicePackage::create([
            'service_catalog_id' => (int) $validated['service_catalog_id'],
            'title' => $this->cleanString($validated['title']),
            'subtitle' => $this->nullableString($validated['subtitle'] ?? null),
            'rows' => $this->textToRows($validated['rows_text'] ?? null),
            'is_active' => (bool) $validated['is_active'],
            'sort_order' => (int) $validated['sort_order'],
        ]);

        return back()->with('success', 'Paket layanan berhasil ditambahkan.');
    }

    public function updatePackage(Request $request, ServicePackage $servicePackage): RedirectResponse
    {
        $validated = $request->validate(
            $this->packageRules(),
            $this->packageMessages(),
            $this->packageAttributes()
        );

        $servicePackage->update([
            'service_catalog_id' => (int) $validated['service_catalog_id'],
            'title' => $this->cleanString($validated['title']),
            'subtitle' => $this->nullableString($validated['subtitle'] ?? null),
            'rows' => $this->textToRows($validated['rows_text'] ?? null),
            'is_active' => (bool) $validated['is_active'],
            'sort_order' => (int) $validated['sort_order'],
        ]);

        return back()->with('success', 'Paket layanan berhasil diperbarui.');
    }

    public function destroyPackage(ServicePackage $servicePackage): RedirectResponse
    {
        $servicePackage->delete();

        return back()->with('success', 'Paket layanan berhasil dihapus.');
    }

    public function updateTestimonial(Request $request, ClientTestimonial $clientTestimonial): RedirectResponse
    {
        $this->ensureServiceTestimonial($clientTestimonial);

        $validated = $request->validate([
            'is_approved' => ['required', 'boolean'],
        ]);

        $clientTestimonial->update([
            'is_approved' => (bool) $validated['is_approved'],
        ]);

        return back()->with('success', 'Status testimoni berhasil diperbarui.');
    }

    public function destroyTestimonial(ClientTestimonial $clientTestimonial): RedirectResponse
    {
        $this->ensureServiceTestimonial($clientTestimonial);

        $clientTestimonial->delete();

        return back()->with('success', 'Testimoni berhasil dihapus.');
    }

    private function servicePayloads(): array
    {
        return ServiceCatalog::query()
            ->withCount('packages')
            ->ordered()
            ->get()
            ->map(fn(ServiceCatalog $service) => [
                'id' => $service->id,
                'title' => $service->title,
                'slug' => $service->slug,
                'badge' => $service->badge,
                'icon' => $service->icon,
                'summary' => $service->summary,
                'features' => $this->normalizeArray($service->features),
                'features_text' => implode("\n", $this->normalizeArray($service->features)),
                'cta' => $service->cta,
                'whatsapp_text' => $service->whatsapp_text,
                'is_active' => (bool) $service->is_active,
                'sort_order' => (int) $service->sort_order,
                'packages_count' => (int) $service->packages_count,
            ])
            ->values()
            ->all();
    }

    private function packagePayloads(): array
    {
        return ServicePackage::query()
            ->with('service:id,title,slug')
            ->ordered()
            ->get()
            ->map(fn(ServicePackage $package) => [
                'id' => $package->id,
                'service_catalog_id' => $package->service_catalog_id,
                'service_title' => $package->service?->title,
                'service_slug' => $package->service?->slug,
                'title' => $package->title,
                'subtitle' => $package->subtitle,
                'rows' => $this->normalizeRows($package->rows),
                'rows_text' => $this->rowsToText($package->rows),
                'is_active' => (bool) $package->is_active,
                'sort_order' => (int) $package->sort_order,
            ])
            ->values()
            ->all();
    }

    private function testimonialPayloads(): array
    {
        return ClientTestimonial::query()
            ->where('source_page', self::TESTIMONIAL_SOURCE_PAGE)
            ->latest()
            ->get()
            ->map(fn(ClientTestimonial $testimonial) => [
                'id' => $testimonial->id,
                'name' => $testimonial->name,
                'business_name' => $testimonial->business_name,
                'service_type' => $testimonial->service_type,
                'rating' => $this->normalizeRating($testimonial->rating),
                'message' => $testimonial->message,
                'is_approved' => (bool) $testimonial->is_approved,
                'created_at' => optional($testimonial->created_at)?->format('d M Y'),
            ])
            ->values()
            ->all();
    }

    private function ensureBaseServices(): void
    {
        $services = [
            'website' => [
                'title' => 'Jasa Website',
                'badge' => 'Layanan Utama',
                'icon' => 'globe',
                'summary' => 'Pembuatan website profesional untuk personal brand, UMKM, company profile, dan landing page promosi.',
                'features' => [
                    'Landing page modern dan responsif',
                    'Company profile profesional',
                    'Desain rapi, cepat, dan mobile-friendly',
                    'Optimasi struktur halaman dan CTA',
                ],
                'cta' => 'Konsultasikan website Anda',
                'whatsapp_text' => 'Halo, saya ingin konsultasi pembuatan website.',
                'sort_order' => 1,
            ],
            'install' => [
                'title' => 'Instalasi Software & OS',
                'badge' => 'Teknis',
                'icon' => 'monitor',
                'summary' => 'Layanan instalasi sistem operasi, software produktivitas, driver, tools development, dan setup perangkat.',
                'features' => [
                    'Instalasi Windows / Linux',
                    'Instalasi Office, browser, tools kampus, dan software kerja',
                    'Setup driver, printer, dan utilitas dasar',
                    'Optimasi awal agar perangkat siap dipakai',
                ],
                'cta' => 'Pesan layanan instalasi',
                'whatsapp_text' => 'Halo, saya ingin pesan layanan instalasi software atau OS.',
                'sort_order' => 2,
            ],
            'design-video' => [
                'title' => 'Jasa Desain & Video',
                'badge' => 'Kreatif',
                'icon' => 'sparkles',
                'summary' => 'Pembuatan desain visual dan video untuk promosi, media sosial, dokumentasi kegiatan, dan branding.',
                'features' => [
                    'Desain poster, feed, banner, dan publikasi',
                    'Editing video reels, profil, dan dokumentasi',
                    'Visual branding yang konsisten dan menarik',
                    'Cocok untuk organisasi, UMKM, dan event',
                ],
                'cta' => 'Diskusikan kebutuhan kreatif',
                'whatsapp_text' => 'Halo, saya ingin konsultasi jasa desain dan video.',
                'sort_order' => 3,
            ],
        ];

        foreach ($services as $slug => $service) {
            ServiceCatalog::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    ...$service,
                    'is_active' => true,
                ]
            );
        }

        $this->ensureDefaultPackages();
    }

    private function ensureDefaultPackages(): void
    {
        $defaults = [
            'website' => [
                [
                    'title' => 'Website Paling Dasar',
                    'subtitle' => 'Informasi / Personal / Landing Page',
                    'rows' => [
                        ['label' => 'Tujuan', 'value' => 'Profil usaha kecil, personal brand, kampanye singkat'],
                        ['label' => 'Jenis Halaman', 'value' => 'Landing page, portofolio, profil sederhana'],
                        ['label' => 'Kisaran Biaya', 'value' => 'Rp800.000 – Rp3.000.000'],
                        ['label' => 'Cocok Untuk', 'value' => 'UMKM, personal, organisasi kecil'],
                    ],
                    'sort_order' => 1,
                ],
                [
                    'title' => 'Website Company Profile',
                    'subtitle' => 'Bisnis Menengah',
                    'rows' => [
                        ['label' => 'Tujuan', 'value' => 'Membangun citra bisnis yang lebih profesional'],
                        ['label' => 'Struktur Halaman', 'value' => 'Home, Tentang, Layanan, Kontak, dan halaman pendukung'],
                        ['label' => 'Biaya Freelancer', 'value' => 'Rp3.000.000 – Rp7.000.000'],
                        ['label' => 'Cocok Untuk', 'value' => 'Bisnis yang butuh pondasi digital rapi'],
                    ],
                    'sort_order' => 2,
                ],
            ],
            'install' => [
                [
                    'title' => 'Instalasi Software',
                    'subtitle' => 'Aplikasi Produktivitas',
                    'rows' => [
                        ['label' => 'Kebutuhan', 'value' => 'Office, browser, tools kampus, aplikasi kerja'],
                        ['label' => 'Kisaran Biaya', 'value' => 'Mulai dari Rp20.000'],
                        ['label' => 'Cocok Untuk', 'value' => 'Laptop mahasiswa, perangkat kerja, perangkat organisasi'],
                    ],
                    'sort_order' => 1,
                ],
                [
                    'title' => 'Instalasi / Reinstall OS',
                    'subtitle' => 'Windows / Linux',
                    'rows' => [
                        ['label' => 'Kebutuhan', 'value' => 'Install ulang sistem operasi dan driver dasar'],
                        ['label' => 'Kisaran Biaya', 'value' => 'Mulai dari Rp50.000'],
                        ['label' => 'Cocok Untuk', 'value' => 'Laptop lambat, error, atau butuh sistem baru'],
                    ],
                    'sort_order' => 2,
                ],
            ],
            'design-video' => [
                [
                    'title' => 'Desain Poster / Feed',
                    'subtitle' => 'Media Sosial dan Publikasi',
                    'rows' => [
                        ['label' => 'Kebutuhan', 'value' => 'Poster, feed Instagram, banner, publikasi event'],
                        ['label' => 'Kisaran Biaya', 'value' => 'Mulai dari Rp25.000'],
                        ['label' => 'Cocok Untuk', 'value' => 'Organisasi, UMKM, event, promosi'],
                    ],
                    'sort_order' => 1,
                ],
                [
                    'title' => 'Editing Video',
                    'subtitle' => 'Reels / Dokumentasi / Promosi',
                    'rows' => [
                        ['label' => 'Kebutuhan', 'value' => 'Video promosi, reels, dokumentasi kegiatan'],
                        ['label' => 'Kisaran Biaya', 'value' => 'Mulai dari Rp100.000'],
                        ['label' => 'Cocok Untuk', 'value' => 'Konten media sosial dan dokumentasi organisasi'],
                    ],
                    'sort_order' => 2,
                ],
            ],
        ];

        foreach ($defaults as $serviceSlug => $packages) {
            $service = ServiceCatalog::query()->where('slug', $serviceSlug)->first();

            if (! $service || $service->packages()->exists()) {
                continue;
            }

            foreach ($packages as $package) {
                ServicePackage::create([
                    'service_catalog_id' => $service->id,
                    'title' => $package['title'],
                    'subtitle' => $package['subtitle'],
                    'rows' => $package['rows'],
                    'is_active' => true,
                    'sort_order' => $package['sort_order'],
                ]);
            }
        }
    }

    private function attachLegacyPackagesToWebsite(): void
    {
        $website = ServiceCatalog::query()->where('slug', 'website')->first();

        if (! $website) {
            return;
        }

        ServicePackage::query()
            ->whereNull('service_catalog_id')
            ->update([
                'service_catalog_id' => $website->id,
            ]);
    }

    private function ensureServiceTestimonial(ClientTestimonial $clientTestimonial): void
    {
        abort_if(
            $clientTestimonial->source_page !== self::TESTIMONIAL_SOURCE_PAGE,
            404
        );
    }

    private function serviceRules(?ServiceCatalog $serviceCatalog = null): array
    {
        $ignoreId = $serviceCatalog?->id;

        return [
            'title' => ['required', 'string', 'max:180'],
            'slug' => [
                'nullable',
                'string',
                'max:180',
                Rule::unique('service_catalogs', 'slug')->ignore($ignoreId),
            ],
            'badge' => ['nullable', 'string', 'max:120'],
            'icon' => ['nullable', 'string', Rule::in($this->iconValues())],
            'summary' => ['nullable', 'string', 'max:3000'],
            'features_text' => ['nullable', 'string', 'max:5000'],
            'cta' => ['nullable', 'string', 'max:160'],
            'whatsapp_text' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    private function packageRules(): array
    {
        return [
            'service_catalog_id' => ['required', 'integer', 'exists:service_catalogs,id'],
            'title' => ['required', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:180'],
            'rows_text' => ['nullable', 'string', 'max:8000'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    private function serviceMessages(): array
    {
        return [
            'title.required' => 'Nama layanan wajib diisi.',
            'title.max' => 'Nama layanan maksimal 180 karakter.',
            'slug.unique' => 'Slug layanan sudah digunakan.',
            'icon.in' => 'Ikon layanan yang dipilih tidak valid.',
            'summary.max' => 'Ringkasan layanan maksimal 3000 karakter.',
            'features_text.max' => 'Fitur layanan maksimal 5000 karakter.',
            'cta.max' => 'Teks tombol maksimal 160 karakter.',
            'whatsapp_text.max' => 'Teks WhatsApp maksimal 1000 karakter.',
            'is_active.required' => 'Status layanan wajib dipilih.',
            'sort_order.required' => 'Urutan layanan wajib diisi.',
            'sort_order.min' => 'Urutan layanan tidak boleh negatif.',
        ];
    }

    private function packageMessages(): array
    {
        return [
            'service_catalog_id.required' => 'Layanan induk wajib dipilih.',
            'service_catalog_id.exists' => 'Layanan induk tidak valid.',
            'title.required' => 'Nama paket wajib diisi.',
            'title.max' => 'Nama paket maksimal 180 karakter.',
            'subtitle.max' => 'Subjudul paket maksimal 180 karakter.',
            'rows_text.max' => 'Detail paket maksimal 8000 karakter.',
            'is_active.required' => 'Status paket wajib dipilih.',
            'sort_order.required' => 'Urutan paket wajib diisi.',
            'sort_order.min' => 'Urutan paket tidak boleh negatif.',
        ];
    }

    private function serviceAttributes(): array
    {
        return [
            'title' => 'nama layanan',
            'slug' => 'slug layanan',
            'badge' => 'badge layanan',
            'icon' => 'ikon layanan',
            'summary' => 'ringkasan layanan',
            'features_text' => 'fitur layanan',
            'cta' => 'teks tombol',
            'whatsapp_text' => 'teks WhatsApp',
            'is_active' => 'status layanan',
            'sort_order' => 'urutan layanan',
        ];
    }

    private function packageAttributes(): array
    {
        return [
            'service_catalog_id' => 'layanan induk',
            'title' => 'nama paket',
            'subtitle' => 'subjudul paket',
            'rows_text' => 'detail paket',
            'is_active' => 'status paket',
            'sort_order' => 'urutan paket',
        ];
    }

    private function linesToArray(?string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value) ?: [])
            ->map(fn($item) => $this->cleanString($item))
            ->filter()
            ->values()
            ->all();
    }

    private function textToRows(?string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value) ?: [])
            ->map(fn($line) => $this->cleanString($line))
            ->filter()
            ->map(fn(string $line) => $this->parsePackageDetailLine($line))
            ->filter(fn(array $row) => filled($row['label']) && filled($row['value']))
            ->values()
            ->all();
    }

    private function parsePackageDetailLine(string $line): array
    {
        $line = $this->cleanString($line);

        if ($line === '') {
            return ['label' => '', 'value' => ''];
        }

        $patterns = [
            '/\s*\|\s*/u',
            '/\s*=>\s*/u',
            '/\s*:\s*/u',
            '/\s+[–—-]\s+/u',
        ];

        foreach ($patterns as $pattern) {
            $parts = preg_split($pattern, $line, 2);

            if (is_array($parts) && count($parts) === 2) {
                return [
                    'label' => $this->cleanString($parts[0] ?? 'Keterangan'),
                    'value' => $this->cleanString($parts[1] ?? ''),
                ];
            }
        }

        return [
            'label' => 'Keterangan',
            'value' => $line,
        ];
    }

    private function rowsToText(mixed $rows): string
    {
        return collect($this->normalizeRows($rows))
            ->map(function (array $row) {
                $label = $this->cleanString($row['label'] ?? '');
                $value = $this->cleanString($row['value'] ?? '');

                if ($label === '' && $value === '') {
                    return '';
                }

                if ($label === '') {
                    return 'Keterangan | ' . $value;
                }

                if ($value === '') {
                    return $label;
                }

                return $label . ' | ' . $value;
            })
            ->filter()
            ->implode("\n");
    }

    private function normalizeRows(mixed $rows): array
    {
        if ($rows instanceof \Illuminate\Support\Collection) {
            $rows = $rows->values()->all();
        }

        if (is_string($rows)) {
            $decoded = json_decode($rows, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $rows = $decoded;
            } else {
                return $this->textToRows($rows);
            }
        }

        if (! is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->map(function ($row) {
                if (is_string($row)) {
                    return $this->parsePackageDetailLine($row);
                }

                if (! is_array($row)) {
                    return ['label' => '', 'value' => ''];
                }

                return [
                    'label' => $this->cleanString($row['label'] ?? ''),
                    'value' => $this->cleanString($row['value'] ?? ''),
                ];
            })
            ->filter(fn(array $row) => filled($row['label']) || filled($row['value']))
            ->values()
            ->all();
    }

    private function normalizeArray(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn($item) => $this->cleanString($item))
            ->filter()
            ->values()
            ->all();
    }

    private function resolveServiceSlug(string $title, ?string $requestedSlug = null, ?int $ignoreId = null): string
    {
        $slug = Str::slug($requestedSlug ?: $title);

        return $this->uniqueServiceSlug($slug ?: 'layanan', $ignoreId);
    }

    private function uniqueServiceSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($baseSlug) ?: 'layanan';
        $slug = $baseSlug;
        $counter = 1;

        while (
            ServiceCatalog::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function validIcon(?string $icon): string
    {
        $icon = $this->cleanString($icon ?: 'globe');

        return in_array($icon, $this->iconValues(), true) ? $icon : 'globe';
    }

    private function iconValues(): array
    {
        return collect(self::ICON_OPTIONS)
            ->pluck('value')
            ->values()
            ->all();
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

    private function cleanString(mixed $value): string
    {
        return trim((string) $value);
    }

    private function nullableString(mixed $value): ?string
    {
        $value = $this->cleanString($value);

        return $value !== '' ? $value : null;
    }
}
