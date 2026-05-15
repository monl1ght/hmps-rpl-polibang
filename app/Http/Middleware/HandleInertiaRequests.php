<?php

namespace App\Http\Middleware;

use App\Models\FooterContactItem;
use App\Models\FooterLegalLink;
use App\Models\FooterNavItem;
use App\Models\FooterSetting;
use App\Models\FooterSocialLink;
use App\Models\NavbarSetting;
use App\Models\NavigationItem;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Throwable;

class HandleInertiaRequests extends Middleware
{
    /**
     * Root template yang digunakan Inertia saat first page visit.
     */
    protected $rootView = 'app';

    /**
     * Menentukan asset version Inertia.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Shared props untuk semua halaman Inertia.
     *
     * Data di sini bisa dibaca di Vue melalui:
     * page.props.auth
     * page.props.flash
     * page.props.navbar
     * page.props.footer
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            'auth' => [
                'user' => $request->user()
                    ? $request->user()->only('id', 'name', 'email')
                    : null,
            ],

            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
                'warning' => fn() => $request->session()->get('warning'),
                'info' => fn() => $request->session()->get('info'),
            ],

            'navbar' => fn(): array => $this->navbarPayload(),

            'footer' => fn(): array => $this->footerPayload(),
        ];
    }

    /**
     * Payload navbar dari database.
     */
    private function navbarPayload(): array
    {
        try {
            $setting = NavbarSetting::query()
                ->active()
                ->latest('id')
                ->first();

            $items = NavigationItem::query()
                ->active()
                ->ordered()
                ->get()
                ->map(fn(NavigationItem $item) => [
                    'id' => $item->id,
                    'label' => $item->label,
                    'href' => $item->href,
                    'path' => $item->path ?: $item->href,
                    'iconPath' => $item->icon_path,
                    'target' => $item->target ?: '_self',
                ])
                ->values()
                ->all();

            return [
                'logoPath' => $setting?->logo_path ?: '/Images/logo/hmps-rpl-logo.png',
                'logoAlt' => $setting?->logo_alt ?: 'Logo HMPS RPL',
                'brandTitle' => $setting?->brand_title ?: 'HMPS RPL',
                'brandSubtitle' => $setting?->brand_subtitle ?: 'Rekayasa Perangkat Lunak',

                'ctaLabel' => $setting?->cta_label ?: 'Hubungi Kami',
                'ctaContactName' => $setting?->cta_contact_name ?: 'Admin',
                'ctaWhatsappNumber' => $this->normalizeWhatsappNumber(
                    $setting?->cta_whatsapp_number ?: '6285712324386'
                ),
                'ctaMessage' => $setting?->cta_message ?: 'Halo Kak, saya ingin menghubungi HMPS RPL.',
                'ctaIsActive' => (bool) ($setting?->cta_is_active ?? true),

                'items' => count($items) ? $items : $this->fallbackNavbarItems(),
            ];
        } catch (Throwable) {
            return $this->fallbackNavbarPayload();
        }
    }

    /**
     * Payload footer dari database.
     */
    private function footerPayload(): array
    {
        try {
            $setting = FooterSetting::query()
                ->active()
                ->latest('id')
                ->first();

            $navLinks = FooterNavItem::query()
                ->active()
                ->ordered()
                ->get()
                ->map(fn(FooterNavItem $item) => [
                    'id' => $item->id,
                    'label' => $item->label,
                    'href' => $item->href,
                    'icon' => $item->icon_path,
                    'target' => $item->target ?: '_self',
                ])
                ->values()
                ->all();

            $socials = FooterSocialLink::query()
                ->active()
                ->ordered()
                ->get()
                ->map(fn(FooterSocialLink $social) => [
                    'id' => $social->id,
                    'name' => $social->name,
                    'href' => $social->href,
                    'icon' => $social->icon_path,
                    'target' => $social->target ?: '_blank',
                ])
                ->values()
                ->all();

            $contactItems = FooterContactItem::query()
                ->active()
                ->ordered()
                ->get()
                ->map(fn(FooterContactItem $item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'value' => $item->value,
                    'helper' => $item->helper,
                    'href' => $item->href,
                    'type' => $item->type ?: 'info',
                    'icon' => $item->icon_path,
                    'target' => $item->target ?: '_self',
                ])
                ->values()
                ->all();

            $legalLinks = FooterLegalLink::query()
                ->active()
                ->ordered()
                ->get()
                ->map(fn(FooterLegalLink $link) => [
                    'id' => $link->id,
                    'label' => $link->label,
                    'href' => $link->href,
                    'target' => $link->target ?: '_self',
                ])
                ->values()
                ->all();

            return [
                'logoPath' => $setting?->logo_path ?: '/Images/logo/hmps-rpl-logo.png',
                'logoAlt' => $setting?->logo_alt ?: 'Logo HMPS RPL',
                'brandTitle' => $setting?->brand_title ?: 'HMPS RPL',
                'brandSubtitle' => $setting?->brand_subtitle ?: 'Rekayasa Perangkat Lunak',
                'description' => $setting?->description ?: 'Wadah aspirasi, kolaborasi, dan kreativitas mahasiswa Rekayasa Perangkat Lunak untuk membangun ekosistem teknologi yang aktif, profesional, dan berdampak.',
                'identityLabel' => $setting?->identity_label ?: 'Identitas Organisasi',
                'identityText' => $setting?->identity_text ?: 'Himpunan Mahasiswa Program Studi Rekayasa Perangkat Lunak.',
                'navigationTitle' => $setting?->navigation_title ?: 'Navigasi',
                'contactTitle' => $setting?->contact_title ?: 'Kontak',
                'contactCtaLabel' => $setting?->contact_cta_label ?: 'Hubungi via WhatsApp',
                'contactCtaUrl' => $setting?->contact_cta_url ?: 'https://wa.me/6285712324386?text=Halo%20Kak%2C%20saya%20ingin%20menghubungi%20HMPS%20RPL.',
                'copyrightText' => $setting?->copyright_text ?: '© {year} HMPS Rekayasa Perangkat Lunak. All rights reserved.',

                'navLinks' => count($navLinks) ? $navLinks : $this->fallbackFooterNavLinks(),
                'socials' => count($socials) ? $socials : $this->fallbackFooterSocials(),
                'contactItems' => count($contactItems) ? $contactItems : $this->fallbackFooterContactItems(),
                'legalLinks' => count($legalLinks) ? $legalLinks : $this->fallbackFooterLegalLinks(),
            ];
        } catch (Throwable) {
            return $this->fallbackFooterPayload();
        }
    }

    private function fallbackNavbarPayload(): array
    {
        return [
            'logoPath' => '/Images/logo/hmps-rpl-logo.png',
            'logoAlt' => 'Logo HMPS RPL',
            'brandTitle' => 'HMPS RPL',
            'brandSubtitle' => 'Rekayasa Perangkat Lunak',
            'ctaLabel' => 'Hubungi Kami',
            'ctaContactName' => 'Admin',
            'ctaWhatsappNumber' => '6285712324386',
            'ctaMessage' => 'Halo Kak, saya ingin menghubungi HMPS RPL.',
            'ctaIsActive' => true,
            'items' => $this->fallbackNavbarItems(),
        ];
    }

    private function fallbackFooterPayload(): array
    {
        return [
            'logoPath' => '/Images/logo/hmps-rpl-logo.png',
            'logoAlt' => 'Logo HMPS RPL',
            'brandTitle' => 'HMPS RPL',
            'brandSubtitle' => 'Rekayasa Perangkat Lunak',
            'description' => 'Wadah aspirasi, kolaborasi, dan kreativitas mahasiswa Rekayasa Perangkat Lunak untuk membangun ekosistem teknologi yang aktif, profesional, dan berdampak.',
            'identityLabel' => 'Identitas Organisasi',
            'identityText' => 'Himpunan Mahasiswa Program Studi Rekayasa Perangkat Lunak.',
            'navigationTitle' => 'Navigasi',
            'contactTitle' => 'Kontak',
            'contactCtaLabel' => 'Hubungi via WhatsApp',
            'contactCtaUrl' => 'https://wa.me/6285712324386?text=Halo%20Kak%2C%20saya%20ingin%20menghubungi%20HMPS%20RPL.',
            'copyrightText' => '© {year} HMPS Rekayasa Perangkat Lunak. All rights reserved.',
            'navLinks' => $this->fallbackFooterNavLinks(),
            'socials' => $this->fallbackFooterSocials(),
            'contactItems' => $this->fallbackFooterContactItems(),
            'legalLinks' => $this->fallbackFooterLegalLinks(),
        ];
    }

    private function fallbackNavbarItems(): array
    {
        return [
            [
                'id' => null,
                'label' => 'Beranda',
                'href' => '/',
                'path' => '/',
                'iconPath' => 'M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5Z',
                'target' => '_self',
            ],
            [
                'id' => null,
                'label' => 'Profil',
                'href' => '/profil',
                'path' => '/profil',
                'iconPath' => 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z',
                'target' => '_self',
            ],
            [
                'id' => null,
                'label' => 'Kepengurusan',
                'href' => '/kepengurusan',
                'path' => '/kepengurusan',
                'iconPath' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM5 21a7 7 0 0 1 14 0M18 8h3M19.5 6.5v3M3 8h3M4.5 6.5v3',
                'target' => '_self',
            ],
            [
                'id' => null,
                'label' => 'Program Kerja',
                'href' => '/program-kerja',
                'path' => '/program-kerja',
                'iconPath' => 'M10 6V5a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v1m-8 0h12a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Zm0 6h12',
                'target' => '_self',
            ],
            [
                'id' => null,
                'label' => 'Layanan Jasa',
                'href' => '/layananJasa',
                'path' => '/layananJasa',
                'iconPath' => 'M12 3l2.2 4.46 4.92.72-3.56 3.47.84 4.9L12 14.23l-4.4 2.32.84-4.9-3.56-3.47 4.92-.72L12 3Z',
                'target' => '_self',
            ],
            [
                'id' => null,
                'label' => 'Konsultasi',
                'href' => '/konsultasi',
                'path' => '/konsultasi',
                'iconPath' => 'M7 8h10M7 12h6m8 0a8 8 0 1 1-3.1-6.32L21 4v5h-5',
                'target' => '_self',
            ],
            [
                'id' => null,
                'label' => 'Dokumentasi',
                'href' => '/dokumentasi',
                'path' => '/dokumentasi',
                'iconPath' => 'M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 10 3-3 2 2 3-4 4 5M8 8h.01',
                'target' => '_self',
            ],
        ];
    }

    private function fallbackFooterNavLinks(): array
    {
        return [
            [
                'id' => null,
                'label' => 'Beranda',
                'href' => '/',
                'icon' => 'M3 10.5 12 3l9 7.5M5 10v10h14V10M9 20v-6h6v6',
                'target' => '_self',
            ],
            [
                'id' => null,
                'label' => 'Profil',
                'href' => '/profil',
                'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4 21a8 8 0 0 1 16 0',
                'target' => '_self',
            ],
            [
                'id' => null,
                'label' => 'Kepengurusan',
                'href' => '/kepengurusan',
                'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM5 21a7 7 0 0 1 14 0',
                'target' => '_self',
            ],
            [
                'id' => null,
                'label' => 'Program Kerja',
                'href' => '/program-kerja',
                'icon' => 'M10 6V5a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v1m-8 0h12a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Zm0 6h12',
                'target' => '_self',
            ],
            [
                'id' => null,
                'label' => 'Layanan Jasa',
                'href' => '/layananJasa',
                'icon' => 'M12 3l2.2 4.46 4.92.72-3.56 3.47.84 4.9L12 14.23l-4.4 2.32.84-4.9-3.56-3.47 4.92-.72L12 3Z',
                'target' => '_self',
            ],
            [
                'id' => null,
                'label' => 'Konsultasi',
                'href' => '/konsultasi',
                'icon' => 'M7 8h10M7 12h6m8 0a8 8 0 1 1-3.1-6.32L21 4v5h-5',
                'target' => '_self',
            ],
            [
                'id' => null,
                'label' => 'Dokumentasi',
                'href' => '/dokumentasi',
                'icon' => 'M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 10 3-3 2 2 3-4 4 5M8 8h.01',
                'target' => '_self',
            ],
        ];
    }

    private function fallbackFooterSocials(): array
    {
        return [
            [
                'id' => null,
                'name' => 'Instagram',
                'href' => 'https://www.instagram.com/himapro.rpl?igsh=MXRzYmFxYjJuOWs5bg==',
                'icon' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z',
                'target' => '_blank',
            ],
            [
                'id' => null,
                'name' => 'WhatsApp',
                'href' => 'https://wa.me/6285712324386?text=Halo%20Kak%2C%20saya%20ingin%20menghubungi%20HMPS%20RPL.',
                'icon' => 'M20.52 3.48A11.78 11.78 0 0 0 12.13 0C5.56 0 .22 5.34.22 11.91c0 2.1.55 4.16 1.6 5.97L.12 24l6.27-1.64a11.9 11.9 0 0 0 5.74 1.46h.01c6.57 0 11.91-5.34 11.91-11.91a11.83 11.83 0 0 0-3.53-8.43Zm-8.39 18.33h-.01a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.72.98.99-3.63-.23-.37a9.86 9.86 0 0 1-1.51-5.29c0-5.46 4.44-9.9 9.9-9.9a9.84 9.84 0 0 1 7 2.9 9.85 9.85 0 0 1 2.9 7c-.01 5.46-4.45 9.9-9.92 9.9Zm5.43-7.41c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.47-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.05 1.02-1.05 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.21 5.09 4.5.71.31 1.27.49 1.7.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z',
                'target' => '_blank',
            ],
        ];
    }

    private function fallbackFooterContactItems(): array
    {
        return [
            [
                'id' => null,
                'title' => 'Alamat Sekretariat',
                'value' => 'Balekambang, RT.02/RW.07, Kepel, Gemiring Lor, Kec. Nalumsari, Kabupaten Jepara, Jawa Tengah 59465',
                'helper' => 'Politeknik Balekambang Jepara',
                'href' => null,
                'type' => 'location',
                'icon' => null,
                'target' => '_self',
            ],
            [
                'id' => null,
                'title' => '+62 857-1232-4386',
                'value' => 'Ketua HMPS Sekarang',
                'helper' => 'WhatsApp HMPS RPL',
                'href' => 'https://wa.me/6285712324386?text=Halo%20Kak%2C%20saya%20ingin%20menghubungi%20HMPS%20RPL.',
                'type' => 'whatsapp',
                'icon' => null,
                'target' => '_blank',
            ],
            [
                'id' => null,
                'title' => '@himapro.rpl',
                'value' => 'Instagram HMPS RPL',
                'helper' => 'Media sosial resmi',
                'href' => 'https://www.instagram.com/himapro.rpl?igsh=MXRzYmFxYjJuOWs5bg==',
                'type' => 'instagram',
                'icon' => null,
                'target' => '_blank',
            ],
        ];
    }

    private function fallbackFooterLegalLinks(): array
    {
        return [
            [
                'id' => null,
                'label' => 'Kebijakan Privasi',
                'href' => null,
                'target' => '_self',
            ],
            [
                'id' => null,
                'label' => 'Syarat & Ketentuan',
                'href' => null,
                'target' => '_self',
            ],
        ];
    }

    private function normalizeWhatsappNumber(?string $number): string
    {
        $clean = preg_replace('/[^0-9]/', '', (string) $number) ?: '';

        if (str_starts_with($clean, '0')) {
            return '62' . substr($clean, 1);
        }

        if (str_starts_with($clean, '8')) {
            return '62' . $clean;
        }

        return $clean;
    }
}
