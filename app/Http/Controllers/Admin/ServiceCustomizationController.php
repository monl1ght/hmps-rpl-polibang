<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientTestimonial;
use App\Models\ServiceCatalog;
use App\Models\ServicePackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ServiceCustomizationController extends Controller
{
    public function index(): Response
    {
        $this->ensureBaseServices();
        $this->attachLegacyPackagesToWebsite();

        $services = ServiceCatalog::query()
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
                'features' => $service->features ?? [],
                'features_text' => implode("\n", $service->features ?? []),
                'cta' => $service->cta,
                'whatsapp_text' => $service->whatsapp_text,
                'is_active' => (bool) $service->is_active,
                'sort_order' => (int) $service->sort_order,
                'packages_count' => (int) $service->packages_count,
            ])
            ->values()
            ->all();

        $packages = ServicePackage::query()
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
                'rows' => $package->rows ?? [],
                'rows_text' => $this->rowsToText($package->rows ?? []),
                'is_active' => (bool) $package->is_active,
                'sort_order' => (int) $package->sort_order,
            ])
            ->values()
            ->all();

        $testimonials = ClientTestimonial::query()
            ->latest()
            ->get()
            ->map(fn(ClientTestimonial $testimonial) => [
                'id' => $testimonial->id,
                'name' => $testimonial->name,
                'business_name' => $testimonial->business_name,
                'service_type' => $testimonial->service_type,
                'rating' => (int) $testimonial->rating,
                'message' => $testimonial->message,
                'is_approved' => (bool) $testimonial->is_approved,
                'created_at' => optional($testimonial->created_at)?->format('d M Y'),
            ])
            ->values()
            ->all();

        return Inertia::render('admin/pages/ServiceCustomization', [
            'services' => $services,
            'packages' => $packages,
            'testimonials' => $testimonials,
            'iconOptions' => [
                ['value' => 'globe', 'label' => 'Globe / Website'],
                ['value' => 'monitor', 'label' => 'Monitor / Instalasi'],
                ['value' => 'sparkles', 'label' => 'Sparkles / Desain Video'],
            ],
        ]);
    }

    public function storeService(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180', 'unique:service_catalogs,slug'],
            'badge' => ['nullable', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:80'],
            'summary' => ['nullable', 'string', 'max:3000'],
            'features_text' => ['nullable', 'string', 'max:5000'],
            'cta' => ['nullable', 'string', 'max:160'],
            'whatsapp_text' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        ServiceCatalog::create([
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?: $this->uniqueServiceSlug($validated['title']),
            'badge' => $validated['badge'] ?? null,
            'icon' => $validated['icon'] ?? 'globe',
            'summary' => $validated['summary'] ?? null,
            'features' => $this->linesToArray($validated['features_text'] ?? null),
            'cta' => $validated['cta'] ?? null,
            'whatsapp_text' => $validated['whatsapp_text'] ?? null,
            'is_active' => $validated['is_active'],
            'sort_order' => $validated['sort_order'],
        ]);

        return back()->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function updateService(Request $request, ServiceCatalog $serviceCatalog): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180', 'unique:service_catalogs,slug,' . $serviceCatalog->id],
            'badge' => ['nullable', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:80'],
            'summary' => ['nullable', 'string', 'max:3000'],
            'features_text' => ['nullable', 'string', 'max:5000'],
            'cta' => ['nullable', 'string', 'max:160'],
            'whatsapp_text' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $serviceCatalog->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?: $this->uniqueServiceSlug($validated['title'], $serviceCatalog->id),
            'badge' => $validated['badge'] ?? null,
            'icon' => $validated['icon'] ?? 'globe',
            'summary' => $validated['summary'] ?? null,
            'features' => $this->linesToArray($validated['features_text'] ?? null),
            'cta' => $validated['cta'] ?? null,
            'whatsapp_text' => $validated['whatsapp_text'] ?? null,
            'is_active' => $validated['is_active'],
            'sort_order' => $validated['sort_order'],
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
        $validated = $request->validate([
            'service_catalog_id' => ['required', 'exists:service_catalogs,id'],
            'title' => ['required', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:180'],
            'rows_text' => ['nullable', 'string', 'max:8000'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        ServicePackage::create([
            'service_catalog_id' => $validated['service_catalog_id'],
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'rows' => $this->textToRows($validated['rows_text'] ?? null),
            'is_active' => $validated['is_active'],
            'sort_order' => $validated['sort_order'],
        ]);

        return back()->with('success', 'Paket layanan berhasil ditambahkan.');
    }

    public function updatePackage(Request $request, ServicePackage $servicePackage): RedirectResponse
    {
        $validated = $request->validate([
            'service_catalog_id' => ['required', 'exists:service_catalogs,id'],
            'title' => ['required', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:180'],
            'rows_text' => ['nullable', 'string', 'max:8000'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $servicePackage->update([
            'service_catalog_id' => $validated['service_catalog_id'],
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'rows' => $this->textToRows($validated['rows_text'] ?? null),
            'is_active' => $validated['is_active'],
            'sort_order' => $validated['sort_order'],
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
        $validated = $request->validate([
            'is_approved' => ['required', 'boolean'],
        ]);

        $clientTestimonial->update([
            'is_approved' => $validated['is_approved'],
        ]);

        return back()->with('success', 'Status testimoni berhasil diperbarui.');
    }

    public function destroyTestimonial(ClientTestimonial $clientTestimonial): RedirectResponse
    {
        $clientTestimonial->delete();

        return back()->with('success', 'Testimoni berhasil dihapus.');
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
            ServiceCatalog::firstOrCreate(
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
            $service = ServiceCatalog::where('slug', $serviceSlug)->first();

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
        $website = ServiceCatalog::where('slug', 'website')->first();

        if (! $website) {
            return;
        }

        ServicePackage::whereNull('service_catalog_id')->update([
            'service_catalog_id' => $website->id,
        ]);
    }

    private function linesToArray(?string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value))
            ->map(fn($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    private function textToRows(?string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value))
            ->map(fn($line) => trim($line))
            ->filter()
            ->map(function ($line) {
                $parts = explode('|', $line, 2);

                return [
                    'label' => trim($parts[0] ?? ''),
                    'value' => trim($parts[1] ?? ''),
                ];
            })
            ->filter(fn($row) => filled($row['label']) && filled($row['value']))
            ->values()
            ->all();
    }

    private function rowsToText(array $rows): string
    {
        return collect($rows)
            ->map(fn($row) => ($row['label'] ?? '') . ' | ' . ($row['value'] ?? ''))
            ->implode("\n");
    }

    private function uniqueServiceSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug ?: 'layanan';
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
}
