<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterContactItem;
use App\Models\FooterLegalLink;
use App\Models\FooterNavItem;
use App\Models\FooterSetting;
use App\Models\FooterSocialLink;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FooterCustomizationController extends Controller
{
    public function index(): Response
    {
        $this->ensureDefaultFooterData();

        $setting = FooterSetting::query()
            ->latest('id')
            ->first();

        return Inertia::render('admin/pages/FooterCustomization', [
            'setting' => [
                'id' => $setting?->id,
                'logo_path' => $setting?->logo_path,
                'logo_alt' => $setting?->logo_alt,
                'brand_title' => $setting?->brand_title,
                'brand_subtitle' => $setting?->brand_subtitle,
                'description' => $setting?->description,
                'identity_label' => $setting?->identity_label,
                'identity_text' => $setting?->identity_text,
                'navigation_title' => $setting?->navigation_title,
                'contact_title' => $setting?->contact_title,
                'contact_cta_label' => $setting?->contact_cta_label,
                'contact_cta_url' => $setting?->contact_cta_url,
                'copyright_text' => $setting?->copyright_text,
                'is_active' => (bool) $setting?->is_active,
            ],

            'navItems' => FooterNavItem::query()
                ->ordered()
                ->get()
                ->map(fn(FooterNavItem $item) => [
                    'id' => $item->id,
                    'label' => $item->label,
                    'href' => $item->href,
                    'icon_path' => $item->icon_path,
                    'target' => $item->target,
                    'is_active' => (bool) $item->is_active,
                    'sort_order' => (int) $item->sort_order,
                ])
                ->values()
                ->all(),

            'socialLinks' => FooterSocialLink::query()
                ->ordered()
                ->get()
                ->map(fn(FooterSocialLink $social) => [
                    'id' => $social->id,
                    'name' => $social->name,
                    'href' => $social->href,
                    'icon_path' => $social->icon_path,
                    'target' => $social->target,
                    'is_active' => (bool) $social->is_active,
                    'sort_order' => (int) $social->sort_order,
                ])
                ->values()
                ->all(),

            'contactItems' => FooterContactItem::query()
                ->ordered()
                ->get()
                ->map(fn(FooterContactItem $item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'value' => $item->value,
                    'helper' => $item->helper,
                    'href' => $item->href,
                    'type' => $item->type,
                    'icon_path' => $item->icon_path,
                    'target' => $item->target,
                    'is_active' => (bool) $item->is_active,
                    'sort_order' => (int) $item->sort_order,
                ])
                ->values()
                ->all(),

            'legalLinks' => FooterLegalLink::query()
                ->ordered()
                ->get()
                ->map(fn(FooterLegalLink $link) => [
                    'id' => $link->id,
                    'label' => $link->label,
                    'href' => $link->href,
                    'target' => $link->target,
                    'is_active' => (bool) $link->is_active,
                    'sort_order' => (int) $link->sort_order,
                ])
                ->values()
                ->all(),

            'targetOptions' => [
                ['value' => '_self', 'label' => 'Halaman yang sama'],
                ['value' => '_blank', 'label' => 'Tab baru'],
            ],

            'contactTypeOptions' => [
                ['value' => 'location', 'label' => 'Alamat / Lokasi'],
                ['value' => 'whatsapp', 'label' => 'WhatsApp'],
                ['value' => 'instagram', 'label' => 'Instagram'],
                ['value' => 'email', 'label' => 'Email'],
                ['value' => 'info', 'label' => 'Informasi Umum'],
            ],
        ]);
    }

    public function updateSetting(Request $request): RedirectResponse
    {
        $setting = FooterSetting::query()->latest('id')->first();

        if (! $setting) {
            $setting = FooterSetting::create($this->defaultFooterSetting());
        }

        $validated = $request->validate([
            'logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'logo_alt' => ['required', 'string', 'max:160'],
            'brand_title' => ['required', 'string', 'max:120'],
            'brand_subtitle' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:3000'],
            'identity_label' => ['nullable', 'string', 'max:160'],
            'identity_text' => ['nullable', 'string', 'max:2000'],
            'navigation_title' => ['required', 'string', 'max:120'],
            'contact_title' => ['required', 'string', 'max:120'],
            'contact_cta_label' => ['nullable', 'string', 'max:120'],
            'contact_cta_url' => $this->safeHrefRules(required: false),
            'copyright_text' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        $logoPath = $validated['logo_path'] ?: $setting->logo_path;

        if ($request->hasFile('logo_file')) {
            $this->deletePublicStorageFile($setting->logo_path);

            $path = $request->file('logo_file')->store('footer', 'public');
            $logoPath = Storage::url($path);
        }

        $setting->update([
            'logo_path' => $logoPath ?: '/Images/logo/hmps-rpl-logo.png',
            'logo_alt' => $validated['logo_alt'],
            'brand_title' => $validated['brand_title'],
            'brand_subtitle' => $validated['brand_subtitle'] ?? null,
            'description' => $validated['description'] ?? null,
            'identity_label' => $validated['identity_label'] ?? null,
            'identity_text' => $validated['identity_text'] ?? null,
            'navigation_title' => $validated['navigation_title'],
            'contact_title' => $validated['contact_title'],
            'contact_cta_label' => $validated['contact_cta_label'] ?? null,
            'contact_cta_url' => $validated['contact_cta_url'] ?? null,
            'copyright_text' => $validated['copyright_text'] ?? null,
            'is_active' => $validated['is_active'],
        ]);

        return back()->with('success', 'Pengaturan footer berhasil diperbarui.');
    }

    public function storeNavItem(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->navItemRules());

        FooterNavItem::create($validated);

        return back()->with('success', 'Menu footer berhasil ditambahkan.');
    }

    public function updateNavItem(Request $request, FooterNavItem $footerNavItem): RedirectResponse
    {
        $validated = $request->validate($this->navItemRules());

        $footerNavItem->update($validated);

        return back()->with('success', 'Menu footer berhasil diperbarui.');
    }

    public function destroyNavItem(FooterNavItem $footerNavItem): RedirectResponse
    {
        $footerNavItem->delete();

        return back()->with('success', 'Menu footer berhasil dihapus.');
    }

    public function storeSocialLink(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->socialRules());

        FooterSocialLink::create($validated);

        return back()->with('success', 'Social link footer berhasil ditambahkan.');
    }

    public function updateSocialLink(Request $request, FooterSocialLink $footerSocialLink): RedirectResponse
    {
        $validated = $request->validate($this->socialRules());

        $footerSocialLink->update($validated);

        return back()->with('success', 'Social link footer berhasil diperbarui.');
    }

    public function destroySocialLink(FooterSocialLink $footerSocialLink): RedirectResponse
    {
        $footerSocialLink->delete();

        return back()->with('success', 'Social link footer berhasil dihapus.');
    }

    public function storeContactItem(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->contactRules());
        $validated = $this->normalizeContactPayload($validated);

        FooterContactItem::create($validated);

        return back()->with('success', 'Kontak footer berhasil ditambahkan.');
    }

    public function updateContactItem(
        Request $request,
        FooterContactItem $footerContactItem
    ): RedirectResponse {
        $validated = $request->validate($this->contactRules());
        $validated = $this->normalizeContactPayload($validated);

        $footerContactItem->update($validated);

        return back()->with('success', 'Kontak footer berhasil diperbarui.');
    }

    public function destroyContactItem(FooterContactItem $footerContactItem): RedirectResponse
    {
        $footerContactItem->delete();

        return back()->with('success', 'Kontak footer berhasil dihapus.');
    }

    public function storeLegalLink(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->legalRules());

        FooterLegalLink::create($validated);

        return back()->with('success', 'Legal link footer berhasil ditambahkan.');
    }

    public function updateLegalLink(
        Request $request,
        FooterLegalLink $footerLegalLink
    ): RedirectResponse {
        $validated = $request->validate($this->legalRules());

        $footerLegalLink->update($validated);

        return back()->with('success', 'Legal link footer berhasil diperbarui.');
    }

    public function destroyLegalLink(FooterLegalLink $footerLegalLink): RedirectResponse
    {
        $footerLegalLink->delete();

        return back()->with('success', 'Legal link footer berhasil dihapus.');
    }

    private function navItemRules(): array
    {
        return [
            'label' => ['required', 'string', 'max:120'],
            'href' => $this->safeHrefRules(required: true),
            'icon_path' => ['nullable', 'string', 'max:5000'],
            'target' => ['required', Rule::in(['_self', '_blank'])],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    private function socialRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'href' => $this->safeHrefRules(required: true),
            'icon_path' => ['required', 'string', 'max:8000'],
            'target' => ['required', Rule::in(['_self', '_blank'])],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    private function contactRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'value' => ['nullable', 'string', 'max:2000'],
            'helper' => ['nullable', 'string', 'max:180'],
            'href' => $this->safeHrefRules(required: false),
            'type' => ['required', Rule::in(['location', 'whatsapp', 'instagram', 'email', 'info'])],
            'icon_path' => ['nullable', 'string', 'max:8000'],
            'target' => ['required', Rule::in(['_self', '_blank'])],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    private function legalRules(): array
    {
        return [
            'label' => ['required', 'string', 'max:120'],
            'href' => $this->safeHrefRules(required: false),
            'target' => ['required', Rule::in(['_self', '_blank'])],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    private function safeHrefRules(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'max:500',
            function (string $attribute, mixed $value, Closure $fail): void {
                if (blank($value)) {
                    return;
                }

                $href = trim((string) $value);
                $lowerHref = strtolower($href);

                $allowedPrefixes = [
                    '/',
                    '#',
                    'http://',
                    'https://',
                    'mailto:',
                    'tel:',
                ];

                foreach ($allowedPrefixes as $prefix) {
                    if (str_starts_with($lowerHref, $prefix)) {
                        return;
                    }
                }

                $fail('Format link tidak valid. Gunakan link internal, https, mailto, tel, atau anchor.');
            },
        ];
    }

    private function normalizeContactPayload(array $payload): array
    {
        $type = $payload['type'] ?? 'info';
        $value = trim((string) ($payload['value'] ?? ''));
        $href = trim((string) ($payload['href'] ?? ''));

        if ($type === 'email' && blank($href) && filled($value)) {
            $payload['href'] = 'mailto:' . $value;
            $payload['target'] = '_blank';
        }

        if ($type === 'whatsapp' && blank($href)) {
            $numberSource = $value ?: ($payload['title'] ?? '');
            $normalizedNumber = $this->normalizeWhatsappNumber($numberSource);

            if (filled($normalizedNumber)) {
                $payload['href'] = 'https://wa.me/' . $normalizedNumber;
                $payload['target'] = '_blank';
            }
        }

        if (blank($payload['icon_path'] ?? null)) {
            $payload['icon_path'] = $this->defaultIconPath($type);
        }

        return $payload;
    }

    private function normalizeWhatsappNumber(?string $number): string
    {
        $clean = preg_replace('/[^0-9]/', '', (string) $number);

        if (blank($clean)) {
            return '';
        }

        if (str_starts_with($clean, '0')) {
            return '62' . substr($clean, 1);
        }

        if (str_starts_with($clean, '8')) {
            return '62' . $clean;
        }

        return $clean;
    }

    private function defaultIconPath(string $type): string
    {
        return match ($type) {
            'location' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z M15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0z',
            'whatsapp' => 'M20.52 3.48A11.78 11.78 0 0 0 12.13 0C5.56 0 .22 5.34.22 11.91c0 2.1.55 4.16 1.6 5.97L.12 24l6.27-1.64a11.9 11.9 0 0 0 5.74 1.46h.01c6.57 0 11.91-5.34 11.91-11.91a11.83 11.83 0 0 0-3.53-8.43Zm-8.39 18.33h-.01a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.72.98.99-3.63-.23-.37a9.86 9.86 0 0 1-1.51-5.29c0-5.46 4.44-9.9 9.9-9.9a9.84 9.84 0 0 1 7 2.9 9.85 9.85 0 0 1 2.9 7c-.01 5.46-4.45 9.9-9.92 9.9Zm5.43-7.41c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.47-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.05 1.02-1.05 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.21 5.09 4.5.71.31 1.27.49 1.7.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z',
            'instagram' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z',
            'email' => 'M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2Zm0 4-8 5-8-5V6l8 5 8-5v2Z',
            default => 'M13 16h-1v-4h-1m1-4h.01',
        };
    }

    private function ensureDefaultFooterData(): void
    {
        if (! FooterSetting::query()->exists()) {
            FooterSetting::create($this->defaultFooterSetting());
        }

        $navItems = [
            [
                'label' => 'Beranda',
                'href' => '/',
                'icon_path' => 'M3 10.5 12 3l9 7.5M5 10v10h14V10M9 20v-6h6v6',
                'sort_order' => 1,
            ],
            [
                'label' => 'Profil',
                'href' => '/profil',
                'icon_path' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4 21a8 8 0 0 1 16 0',
                'sort_order' => 2,
            ],
            [
                'label' => 'Kepengurusan',
                'href' => '/kepengurusan',
                'icon_path' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM5 21a7 7 0 0 1 14 0',
                'sort_order' => 3,
            ],
            [
                'label' => 'Program Kerja',
                'href' => '/program-kerja',
                'icon_path' => 'M10 6V5a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v1m-8 0h12a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Zm0 6h12',
                'sort_order' => 4,
            ],
            [
                'label' => 'Layanan Jasa',
                'href' => '/layananJasa',
                'icon_path' => 'M12 3l2.2 4.46 4.92.72-3.56 3.47.84 4.9L12 14.23l-4.4 2.32.84-4.9-3.56-3.47 4.92-.72L12 3Z',
                'sort_order' => 5,
            ],
            [
                'label' => 'Konsultasi',
                'href' => '/konsultasi',
                'icon_path' => 'M7 8h10M7 12h6m8 0a8 8 0 1 1-3.1-6.32L21 4v5h-5',
                'sort_order' => 6,
            ],
            [
                'label' => 'Dokumentasi',
                'href' => '/dokumentasi',
                'icon_path' => 'M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 10 3-3 2 2 3-4 4 5M8 8h.01',
                'sort_order' => 7,
            ],
        ];

        foreach ($navItems as $item) {
            FooterNavItem::firstOrCreate(
                ['href' => $item['href']],
                [
                    ...$item,
                    'target' => '_self',
                    'is_active' => true,
                ]
            );
        }

        FooterSocialLink::firstOrCreate(
            ['name' => 'Instagram'],
            [
                'href' => 'https://www.instagram.com/himapro.rpl?igsh=MXRzYmFxYjJuOWs5bg==',
                'icon_path' => $this->defaultIconPath('instagram'),
                'target' => '_blank',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $contacts = [
            [
                'type' => 'location',
                'title' => 'Alamat Sekretariat',
                'value' => 'Balekambang, RT.02/RW.07, Kepel, Gemiring Lor, Kec. Nalumsari, Kabupaten Jepara, Jawa Tengah 59465',
                'helper' => 'Politeknik Balekambang Jepara',
                'href' => null,
                'icon_path' => $this->defaultIconPath('location'),
                'target' => '_self',
                'sort_order' => 1,
            ],
            [
                'type' => 'whatsapp',
                'title' => '+62 857-1232-4386',
                'value' => '6285712324386',
                'helper' => 'WhatsApp HMPS RPL',
                'href' => 'https://wa.me/6285712324386?text=Halo%20Kak%2C%20saya%20ingin%20menghubungi%20HMPS%20RPL.',
                'icon_path' => $this->defaultIconPath('whatsapp'),
                'target' => '_blank',
                'sort_order' => 2,
            ],
            [
                'type' => 'instagram',
                'title' => '@himapro.rpl',
                'value' => 'Instagram HMPS RPL',
                'helper' => 'Media sosial resmi',
                'href' => 'https://www.instagram.com/himapro.rpl?igsh=MXRzYmFxYjJuOWs5bg==',
                'icon_path' => $this->defaultIconPath('instagram'),
                'target' => '_blank',
                'sort_order' => 3,
            ],
            [
                'type' => 'email',
                'title' => 'Email',
                'value' => 'muhammadbahaudinnajib@gmail.com',
                'helper' => 'Kirim email resmi HMPS RPL',
                'href' => 'mailto:muhammadbahaudinnajib@gmail.com',
                'icon_path' => $this->defaultIconPath('email'),
                'target' => '_blank',
                'sort_order' => 4,
            ],
        ];

        foreach ($contacts as $contact) {
            FooterContactItem::firstOrCreate(
                ['type' => $contact['type']],
                [
                    ...$contact,
                    'is_active' => true,
                ]
            );
        }

        FooterLegalLink::firstOrCreate(
            ['label' => 'Kebijakan Privasi'],
            [
                'href' => null,
                'target' => '_self',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        FooterLegalLink::firstOrCreate(
            ['label' => 'Syarat & Ketentuan'],
            [
                'href' => null,
                'target' => '_self',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );
    }

    private function defaultFooterSetting(): array
    {
        return [
            'logo_path' => '/Images/logo/hmps-rpl-logo.png',
            'logo_alt' => 'Logo HMPS RPL',
            'brand_title' => 'HMPS RPL',
            'brand_subtitle' => 'Rekayasa Perangkat Lunak',
            'description' => 'Wadah aspirasi, kolaborasi, dan kreativitas mahasiswa Rekayasa Perangkat Lunak untuk membangun ekosistem teknologi yang aktif, profesional, dan berdampak.',
            'identity_label' => 'Identitas Organisasi',
            'identity_text' => 'Himpunan Mahasiswa Program Studi Rekayasa Perangkat Lunak.',
            'navigation_title' => 'Navigasi',
            'contact_title' => 'Kontak',
            'contact_cta_label' => 'Hubungi via WhatsApp',
            'contact_cta_url' => 'https://wa.me/6285712324386?text=Halo%20Kak%2C%20saya%20ingin%20menghubungi%20HMPS%20RPL.',
            'copyright_text' => '© {year} HMPS Rekayasa Perangkat Lunak. All rights reserved.',
            'is_active' => true,
        ];
    }

    private function deletePublicStorageFile(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        $path = trim($path);

        if (str_starts_with($path, '/storage/')) {
            $path = substr($path, strlen('/storage/'));
        } elseif (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        } else {
            return;
        }

        if (filled($path) && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
