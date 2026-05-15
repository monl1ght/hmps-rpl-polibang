<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ClientTestimonial;
use App\Models\ServiceCatalog;
use App\Models\ServicePackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LayananJasaController extends Controller
{
    private const SOURCE_PAGE = 'layanan_jasa';

    private const DEFAULT_SERVICE_TITLES = [
        'Jasa Website',
        'Instalasi Software & OS',
        'Jasa Desain & Video',
    ];

    public function index(): Response
    {
        $serviceModels = ServiceCatalog::query()
            ->active()
            ->with([
                'packages' => fn($query) => $query
                    ->active()
                    ->ordered(),
            ])
            ->ordered()
            ->get();

        $services = $serviceModels
            ->map(fn(ServiceCatalog $service) => $this->servicePayload($service))
            ->values()
            ->all();

        $servicePackages = ServicePackage::query()
            ->active()
            ->with('service:id,title,slug')
            ->ordered()
            ->get()
            ->map(fn(ServicePackage $package) => $this->packagePayload($package))
            ->values();

        $websiteServiceIds = $serviceModels
            ->filter(fn(ServiceCatalog $service) => $this->isWebsiteService($service))
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->values();

        $websitePackages = $servicePackages
            ->filter(
                fn(array $package) => $websiteServiceIds->contains(
                    (int) ($package['service_catalog_id'] ?? 0)
                )
            )
            ->values()
            ->all();

        $testimonials = ClientTestimonial::query()
            ->approved()
            ->where('source_page', self::SOURCE_PAGE)
            ->latest('created_at')
            ->latest('id')
            ->limit(30)
            ->get()
            ->map(fn(ClientTestimonial $testimonial) => $this->testimonialPayload($testimonial))
            ->values()
            ->all();

        return Inertia::render('user/pages/LayananJasa', [
            'services' => $services,
            'servicePackages' => $servicePackages->values()->all(),
            'websitePackages' => $websitePackages,
            'testimonials' => $testimonials,
        ]);
    }

    public function storeTestimonial(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            $this->testimonialRules(),
            $this->testimonialMessages(),
            $this->testimonialAttributes()
        );

        ClientTestimonial::create([
            'name' => $this->cleanSingleLine($validated['name']),
            'business_name' => $this->nullableSingleLine($validated['business_name'] ?? null),
            'service_type' => $this->cleanSingleLine($validated['service_type']),
            'source_page' => self::SOURCE_PAGE,
            'rating' => $this->normalizeRating($validated['rating']),
            'message' => $this->cleanText($validated['message']),
            'is_approved' => false,
            'sort_order' => 0,
        ]);

        return back()->with(
            'success',
            'Terima kasih. Testimoni Anda berhasil dikirim dan menunggu review admin.'
        );
    }

    private function testimonialRules(): array
    {
        return [
            'name' => ['bail', 'required', 'string', 'min:2', 'max:100'],
            'business_name' => ['nullable', 'string', 'max:120'],
            'service_type' => [
                'bail',
                'required',
                'string',
                'max:180',
                Rule::in($this->activeServiceTitles()),
            ],
            'rating' => ['bail', 'required', 'integer', 'between:1,5'],
            'message' => ['bail', 'required', 'string', 'min:15', 'max:1000'],
        ];
    }

    private function testimonialMessages(): array
    {
        return [
            'name.required' => 'Nama client wajib diisi.',
            'name.min' => 'Nama client minimal harus terdiri dari 2 karakter.',
            'name.max' => 'Nama client maksimal 100 karakter.',

            'business_name.max' => 'Nama usaha atau instansi maksimal 120 karakter.',

            'service_type.required' => 'Jenis layanan wajib dipilih.',
            'service_type.in' => 'Jenis layanan yang dipilih tidak valid.',

            'rating.required' => 'Rating wajib dipilih.',
            'rating.integer' => 'Rating harus berupa angka.',
            'rating.between' => 'Rating harus berada di antara 1 sampai 5 bintang.',

            'message.required' => 'Isi testimoni wajib diisi.',
            'message.min' => 'Isi testimoni minimal 15 karakter.',
            'message.max' => 'Isi testimoni maksimal 1000 karakter.',
        ];
    }

    private function testimonialAttributes(): array
    {
        return [
            'name' => 'nama client',
            'business_name' => 'nama usaha atau instansi',
            'service_type' => 'jenis layanan',
            'rating' => 'rating',
            'message' => 'isi testimoni',
        ];
    }

    private function servicePayload(ServiceCatalog $service): array
    {
        return [
            'id' => $service->id,
            'title' => $service->title,
            'slug' => $service->slug,
            'badge' => $service->badge,
            'icon' => $service->icon,
            'summary' => $service->summary,
            'features' => $this->normalizeArray($service->features),
            'cta' => $service->cta,
            'whatsapp_text' => $service->whatsapp_text,
            'packages' => $service->packages
                ->map(
                    fn(ServicePackage $package) => $this->packagePayload(
                        $package,
                        $service
                    )
                )
                ->values()
                ->all(),
        ];
    }

    private function packagePayload(
        ServicePackage $package,
        ?ServiceCatalog $service = null
    ): array {
        $relatedService = $service ?: $package->service;

        return [
            'id' => $package->id,
            'service_catalog_id' => $package->service_catalog_id,
            'service_title' => $relatedService?->title,
            'service_slug' => $relatedService?->slug,
            'title' => $package->title,
            'subtitle' => $package->subtitle,
            'rows' => $this->normalizePackageRows($package->rows),
        ];
    }

    private function testimonialPayload(ClientTestimonial $testimonial): array
    {
        return [
            'id' => $testimonial->id,
            'name' => $testimonial->name ?: 'Client Layanan Jasa',
            'business_name' => $testimonial->business_name,
            'service_type' => $testimonial->service_type ?: 'Layanan Jasa',
            'source_page' => $testimonial->source_page ?: self::SOURCE_PAGE,
            'rating' => $this->normalizeRating($testimonial->rating),
            'message' => $testimonial->message ?: 'Testimoni belum memiliki isi.',
            'created_at' => optional($testimonial->created_at)->format('d M Y'),
        ];
    }

    private function activeServiceTitles(): array
    {
        $titles = ServiceCatalog::query()
            ->active()
            ->ordered()
            ->pluck('title')
            ->filter()
            ->map(fn($title) => $this->cleanSingleLine($title))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return count($titles) ? $titles : self::DEFAULT_SERVICE_TITLES;
    }

    private function isWebsiteService(ServiceCatalog $service): bool
    {
        $keywords = [
            $service->slug,
            $service->title,
            $service->badge,
            $service->summary,
            $service->icon,
        ];

        $text = collect($keywords)
            ->filter()
            ->map(fn($value) => mb_strtolower((string) $value))
            ->implode(' ');

        return str_contains($text, 'website')
            || str_contains($text, 'web')
            || str_contains($text, 'landing')
            || str_contains($text, 'company profile')
            || str_contains($text, 'company')
            || $service->icon === 'globe';
    }

    private function normalizeArray(mixed $value): array
    {
        if ($value instanceof Collection) {
            $value = $value->values()->all();
        }

        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn($item) => $this->cleanSingleLine($item))
            ->filter()
            ->values()
            ->all();
    }

    private function normalizePackageRows(mixed $rows): array
    {
        if ($rows instanceof Collection) {
            $rows = $rows->values()->all();
        }

        if (! is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->filter(fn($row) => is_array($row))
            ->map(fn(array $row) => [
                'label' => $this->cleanSingleLine($row['label'] ?? ''),
                'value' => $this->cleanSingleLine($row['value'] ?? ''),
            ])
            ->filter(fn(array $row) => filled($row['label']) || filled($row['value']))
            ->values()
            ->all();
    }

    private function normalizeRating(mixed $rating): int
    {
        $rating = (int) $rating;

        if ($rating < 1 || $rating > 5) {
            return 5;
        }

        return $rating;
    }

    private function cleanSingleLine(mixed $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/\s+/u', ' ', $value) ?: '';

        return trim($value);
    }

    private function cleanText(mixed $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace("/\r\n|\r/u", "\n", $value) ?: '';
        $value = preg_replace('/[ \t]+/u', ' ', $value) ?: '';

        return trim($value);
    }

    private function nullableSingleLine(mixed $value): ?string
    {
        $value = $this->cleanSingleLine($value);

        return $value !== '' ? $value : null;
    }
}
