<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavbarSetting;
use App\Models\NavigationItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class NavbarCustomizationController extends Controller
{
    public function index(): Response
    {
        $this->ensureDefaultNavbarData();

        $setting = NavbarSetting::query()
            ->latest('id')
            ->first();

        return Inertia::render('admin/pages/NavbarCustomization', [
            'setting' => [
                'id' => $setting?->id,
                'logo_path' => $setting?->logo_path,
                'logo_alt' => $setting?->logo_alt,
                'brand_title' => $setting?->brand_title,
                'brand_subtitle' => $setting?->brand_subtitle,
                'cta_label' => $setting?->cta_label,
                'cta_contact_name' => $setting?->cta_contact_name,
                'cta_whatsapp_number' => $setting?->cta_whatsapp_number,
                'cta_message' => $setting?->cta_message,
                'cta_is_active' => (bool) $setting?->cta_is_active,
                'is_active' => (bool) $setting?->is_active,
            ],

            'items' => NavigationItem::query()
                ->ordered()
                ->get()
                ->map(fn(NavigationItem $item) => [
                    'id' => $item->id,
                    'label' => $item->label,
                    'href' => $item->href,
                    'path' => $item->path,
                    'icon_path' => $item->icon_path,
                    'target' => $item->target,
                    'is_active' => (bool) $item->is_active,
                    'sort_order' => (int) $item->sort_order,
                ])
                ->values()
                ->all(),

            'targetOptions' => [
                ['value' => '_self', 'label' => 'Halaman yang sama'],
                ['value' => '_blank', 'label' => 'Tab baru'],
            ],
        ]);
    }

    public function updateSetting(Request $request): RedirectResponse
    {
        $setting = NavbarSetting::query()->latest('id')->first();

        if (! $setting) {
            $setting = NavbarSetting::create($this->defaultNavbarSetting());
        }

        $validated = $request->validate([
            'logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'logo_alt' => ['required', 'string', 'max:160'],
            'brand_title' => ['required', 'string', 'max:120'],
            'brand_subtitle' => ['nullable', 'string', 'max:160'],
            'cta_label' => ['nullable', 'string', 'max:120'],
            'cta_contact_name' => ['nullable', 'string', 'max:160'],
            'cta_whatsapp_number' => ['nullable', 'regex:/^[0-9+\-\s()]+$/', 'max:30'],
            'cta_message' => ['nullable', 'string', 'max:1000'],
            'cta_is_active' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);

        $logoPath = $validated['logo_path'] ?: $setting->logo_path;

        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('navbar', 'public');
            $logoPath = Storage::url($path);
        }

        $setting->update([
            'logo_path' => $logoPath ?: '/Images/logo/hmps-rpl-logo.png',
            'logo_alt' => $validated['logo_alt'],
            'brand_title' => $validated['brand_title'],
            'brand_subtitle' => $validated['brand_subtitle'] ?? null,
            'cta_label' => $validated['cta_label'] ?? null,
            'cta_contact_name' => $validated['cta_contact_name'] ?? null,
            'cta_whatsapp_number' => $this->normalizeWhatsappNumber(
                $validated['cta_whatsapp_number'] ?? ''
            ),
            'cta_message' => $validated['cta_message'] ?? null,
            'cta_is_active' => $validated['cta_is_active'],
            'is_active' => $validated['is_active'],
        ]);

        return back()->with('success', 'Pengaturan navbar berhasil diperbarui.');
    }

    public function storeItem(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->itemRules());

        NavigationItem::create([
            'label' => $validated['label'],
            'href' => $validated['href'],
            'path' => $validated['path'] ?: $validated['href'],
            'icon_path' => $validated['icon_path'] ?? null,
            'target' => $validated['target'],
            'is_active' => $validated['is_active'],
            'sort_order' => $validated['sort_order'],
        ]);

        return back()->with('success', 'Menu navbar berhasil ditambahkan.');
    }

    public function updateItem(Request $request, NavigationItem $navigationItem): RedirectResponse
    {
        $validated = $request->validate($this->itemRules());

        $navigationItem->update([
            'label' => $validated['label'],
            'href' => $validated['href'],
            'path' => $validated['path'] ?: $validated['href'],
            'icon_path' => $validated['icon_path'] ?? null,
            'target' => $validated['target'],
            'is_active' => $validated['is_active'],
            'sort_order' => $validated['sort_order'],
        ]);

        return back()->with('success', 'Menu navbar berhasil diperbarui.');
    }

    public function destroyItem(NavigationItem $navigationItem): RedirectResponse
    {
        $navigationItem->delete();

        return back()->with('success', 'Menu navbar berhasil dihapus.');
    }

    private function itemRules(): array
    {
        return [
            'label' => ['required', 'string', 'max:120'],
            'href' => ['required', 'string', 'max:255'],
            'path' => ['nullable', 'string', 'max:255'],
            'icon_path' => ['nullable', 'string', 'max:3000'],
            'target' => ['required', Rule::in(['_self', '_blank'])],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    private function normalizeWhatsappNumber(?string $number): ?string
    {
        $clean = preg_replace('/[^0-9]/', '', (string) $number) ?: null;

        if (! $clean) {
            return null;
        }

        if (str_starts_with($clean, '0')) {
            return '62' . substr($clean, 1);
        }

        if (str_starts_with($clean, '8')) {
            return '62' . $clean;
        }

        return $clean;
    }

    private function ensureDefaultNavbarData(): void
    {
        if (! NavbarSetting::query()->exists()) {
            NavbarSetting::create($this->defaultNavbarSetting());
        }

        $items = [
            [
                'label' => 'Beranda',
                'href' => '/',
                'path' => '/',
                'icon_path' => 'M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5Z',
                'sort_order' => 1,
            ],
            [
                'label' => 'Profil',
                'href' => '/profil',
                'path' => '/profil',
                'icon_path' => 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z',
                'sort_order' => 2,
            ],
            [
                'label' => 'Kepengurusan',
                'href' => '/kepengurusan',
                'path' => '/kepengurusan',
                'icon_path' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM5 21a7 7 0 0 1 14 0M18 8h3M19.5 6.5v3M3 8h3M4.5 6.5v3',
                'sort_order' => 3,
            ],
            [
                'label' => 'Program Kerja',
                'href' => '/program-kerja',
                'path' => '/program-kerja',
                'icon_path' => 'M10 6V5a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v1m-8 0h12a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Zm0 6h12',
                'sort_order' => 4,
            ],
            [
                'label' => 'Layanan Jasa',
                'href' => '/layananJasa',
                'path' => '/layananJasa',
                'icon_path' => 'M12 3l2.2 4.46 4.92.72-3.56 3.47.84 4.9L12 14.23l-4.4 2.32.84-4.9-3.56-3.47 4.92-.72L12 3Z',
                'sort_order' => 5,
            ],
            [
                'label' => 'Konsultasi',
                'href' => '/konsultasi',
                'path' => '/konsultasi',
                'icon_path' => 'M7 8h10M7 12h6m8 0a8 8 0 1 1-3.1-6.32L21 4v5h-5',
                'sort_order' => 6,
            ],
            [
                'label' => 'Dokumentasi',
                'href' => '/dokumentasi',
                'path' => '/dokumentasi',
                'icon_path' => 'M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Zm4 10 3-3 2 2 3-4 4 5M8 8h.01',
                'sort_order' => 7,
            ],
        ];

        foreach ($items as $item) {
            NavigationItem::query()->firstOrCreate(
                ['href' => $item['href']],
                [
                    ...$item,
                    'target' => '_self',
                    'is_active' => true,
                ]
            );
        }
    }

    private function defaultNavbarSetting(): array
    {
        return [
            'logo_path' => '/Images/logo/hmps-rpl-logo.png',
            'logo_alt' => 'Logo HMPS RPL',
            'brand_title' => 'HMPS RPL',
            'brand_subtitle' => 'Rekayasa Perangkat Lunak',
            'cta_label' => 'Hubungi Kami',
            'cta_contact_name' => 'Admin',
            'cta_whatsapp_number' => '6285712324386',
            'cta_message' => 'Halo Kak, saya ingin menghubungi HMPS RPL.',
            'cta_is_active' => true,
            'is_active' => true,
        ];
    }
}
