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
            ->latest()
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
        $serviceTitles = $this->activeServiceTitles();

        $validated = $request->validate(
            [
                'name' => ['bail', 'required', 'string', 'min:2', 'max:100'],
                'business_name' => ['nullable', 'string', 'max:120'],
                'service_type' => [
                    'bail',
                    'required',
                    'string',
                    'max:180',
                    Rule::in($serviceTitles),
                ],
                'rating' => ['bail', 'required', 'integer', 'between:1,5'],
                'message' => ['bail', 'required', 'string', 'min:15', 'max:1000'],
            ],
            [
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
            ],
            [
                'name' => 'nama client',
                'business_name' => 'nama usaha atau instansi',
                'service_type' => 'jenis layanan',
                'rating' => 'rating',
                'message' => 'isi testimoni',
            ]
        );

        ClientTestimonial::create([
            'name' => $this->cleanString($validated['name']),
            'business_name' => $this->nullableString($validated['business_name'] ?? null),
            'service_type' => $this->cleanString($validated['service_type']),
            'rating' => (int) $validated['rating'],
            'message' => $this->cleanString($validated['message']),
            'is_approved' => false,
        ]);

        return back()->with(
            'success',
            'Terima kasih. Testimoni Anda berhasil dikirim dan menunggu review admin.'
        );
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
            'name' => $testimonial->name,
            'business_name' => $testimonial->business_name,
            'service_type' => $testimonial->service_type,
            'rating' => min(max((int) $testimonial->rating, 1), 5),
            'message' => $testimonial->message,
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
            ->map(fn($title) => $this->cleanString($title))
            ->unique()
            ->values()
            ->all();

        if (count($titles)) {
            return $titles;
        }

        return [
            'Jasa Website',
            'Instalasi Software & OS',
            'Jasa Desain & Video',
        ];
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
            return $value->values()->all();
        }

        if (is_array($value)) {
            return array_values(
                array_filter(
                    $value,
                    fn($item) => filled($item)
                )
            );
        }

        return [];
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
            ->map(function (array $row) {
                return [
                    'label' => $this->cleanString($row['label'] ?? ''),
                    'value' => $this->cleanString($row['value'] ?? ''),
                ];
            })
            ->filter(fn(array $row) => filled($row['label']) || filled($row['value']))
            ->values()
            ->all();
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
