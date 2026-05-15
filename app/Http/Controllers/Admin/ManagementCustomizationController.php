<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManagementDivision;
use App\Models\ManagementHeroSetting;
use App\Models\ManagementMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ManagementCustomizationController extends Controller
{
    public function index(): Response
    {
        $hero = $this->ensureDefaultHeroSetting();

        $divisions = ManagementDivision::query()
            ->withCount('members')
            ->ordered()
            ->get()
            ->map(fn(ManagementDivision $division) => [
                'id' => $division->id,
                'name' => $division->name,
                'short_name' => $division->short_name,
                'description' => $division->description,
                'sort_order' => (int) $division->sort_order,
                'members_count' => (int) $division->members_count,
            ])
            ->values()
            ->all();

        $members = ManagementMember::query()
            ->with('division:id,name,short_name')
            ->ordered()
            ->get()
            ->map(fn(ManagementMember $member) => [
                'id' => $member->id,
                'management_division_id' => $member->management_division_id,
                'division_name' => $member->division?->name,
                'division_short_name' => $member->division?->short_name,
                'name' => $member->name,
                'position' => $member->position,
                'task_description' => $member->task_description,
                'category' => $member->category,
                'photo' => $member->photo,
                'photo_url' => $member->photo_url,
                'sort_order' => (int) $member->sort_order,
            ])
            ->values()
            ->all();

        return Inertia::render('admin/pages/ManagementCustomization', [
            'hero' => $this->heroPayload($hero),
            'divisions' => $divisions,
            'members' => $members,
            'categories' => [
                ['value' => ManagementMember::CATEGORY_CORE, 'label' => 'Pengurus Inti'],
                ['value' => ManagementMember::CATEGORY_COORDINATOR, 'label' => 'Koordinator Divisi'],
                ['value' => ManagementMember::CATEGORY_MEMBER, 'label' => 'Anggota Divisi'],
            ],
        ]);
    }

    public function updateHeroSetting(Request $request, ManagementHeroSetting $managementHeroSetting): RedirectResponse
    {
        $validated = $request->validate([
            'badge' => ['nullable', 'string', 'max:160'],
            'title' => ['required', 'string', 'max:180'],
            'title_highlight' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2500'],
            'primary_button_label' => ['nullable', 'string', 'max:80'],
            'primary_button_url' => ['nullable', 'string', 'max:255'],
            'secondary_button_label' => ['nullable', 'string', 'max:80'],
            'secondary_button_url' => ['nullable', 'string', 'max:255'],
            'tertiary_button_label' => ['nullable', 'string', 'max:80'],
            'tertiary_button_url' => ['nullable', 'string', 'max:255'],
            'image_one_alt' => ['nullable', 'string', 'max:180'],
            'image_two_alt' => ['nullable', 'string', 'max:180'],
            'image_three_alt' => ['nullable', 'string', 'max:180'],
            'image_four_alt' => ['nullable', 'string', 'max:180'],
            'image_one_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_two_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_three_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_four_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'floating_badge_top_icon' => ['nullable', 'string', 'max:30'],
            'floating_badge_top_title' => ['nullable', 'string', 'max:120'],
            'floating_badge_top_subtitle' => ['nullable', 'string', 'max:160'],
            'floating_badge_bottom_icon' => ['nullable', 'string', 'max:30'],
            'floating_badge_bottom_title' => ['nullable', 'string', 'max:120'],
            'floating_badge_bottom_subtitle' => ['nullable', 'string', 'max:160'],
            'is_active' => ['required', 'boolean'],
        ]);

        $payload = collect($validated)
            ->except([
                'image_one_file',
                'image_two_file',
                'image_three_file',
                'image_four_file',
            ])
            ->map(fn($value) => is_string($value) ? trim($value) : $value)
            ->all();

        foreach ($this->heroImageFields() as $inputName => $columnName) {
            if ($request->hasFile($inputName)) {
                $this->deleteImageIfExists($managementHeroSetting->{$columnName});

                $payload[$columnName] = $request
                    ->file($inputName)
                    ->store('management-hero', 'public');
            }
        }

        $managementHeroSetting->update($payload);

        return back()->with('success', 'Hero section Kepengurusan berhasil diperbarui.');
    }

    public function storeDivision(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'short_name' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:1500'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        ManagementDivision::create($validated);

        return back()->with('success', 'Divisi berhasil ditambahkan.');
    }

    public function updateDivision(Request $request, ManagementDivision $managementDivision): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'short_name' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:1500'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $managementDivision->update($validated);

        return back()->with('success', 'Divisi berhasil diperbarui.');
    }

    public function destroyDivision(ManagementDivision $managementDivision): RedirectResponse
    {
        if ($managementDivision->members()->exists()) {
            return back()->with('error', 'Divisi tidak bisa dihapus karena masih memiliki anggota.');
        }

        $managementDivision->delete();

        return back()->with('success', 'Divisi berhasil dihapus.');
    }

    public function storeMember(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'management_division_id' => ['nullable', 'exists:management_divisions,id'],
            'name' => ['required', 'string', 'max:160'],
            'position' => ['required', 'string', 'max:160'],
            'task_description' => ['nullable', 'string', 'max:3000'],
            'category' => ['required', 'string', 'in:core,coordinator,member'],
            'photo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        if ($validated['category'] === ManagementMember::CATEGORY_CORE) {
            $validated['management_division_id'] = null;
        }

        $validated['task_description'] = $this->nullableString($validated['task_description'] ?? null);

        if ($request->hasFile('photo_file')) {
            $validated['photo'] = $request->file('photo_file')->store('management-members', 'public');
        }

        unset($validated['photo_file']);

        ManagementMember::create($validated);

        return back()->with('success', 'Pengurus berhasil ditambahkan.');
    }

    public function updateMember(Request $request, ManagementMember $managementMember): RedirectResponse
    {
        $validated = $request->validate([
            'management_division_id' => ['nullable', 'exists:management_divisions,id'],
            'name' => ['required', 'string', 'max:160'],
            'position' => ['required', 'string', 'max:160'],
            'task_description' => ['nullable', 'string', 'max:3000'],
            'category' => ['required', 'string', 'in:core,coordinator,member'],
            'photo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        if ($validated['category'] === ManagementMember::CATEGORY_CORE) {
            $validated['management_division_id'] = null;
        }

        $validated['task_description'] = $this->nullableString($validated['task_description'] ?? null);

        if ($request->hasFile('photo_file')) {
            $this->deleteImageIfExists($managementMember->photo);

            $validated['photo'] = $request->file('photo_file')->store('management-members', 'public');
        }

        unset($validated['photo_file']);

        $managementMember->update($validated);

        return back()->with('success', 'Pengurus berhasil diperbarui.');
    }

    public function destroyMember(ManagementMember $managementMember): RedirectResponse
    {
        $this->deleteImageIfExists($managementMember->photo);

        $managementMember->delete();

        return back()->with('success', 'Pengurus berhasil dihapus.');
    }

    private function heroPayload(ManagementHeroSetting $hero): array
    {
        return [
            'id' => $hero->id,
            'badge' => $hero->badge,
            'title' => $hero->title,
            'title_highlight' => $hero->title_highlight,
            'description' => $hero->description,
            'primary_button_label' => $hero->primary_button_label,
            'primary_button_url' => $hero->primary_button_url,
            'secondary_button_label' => $hero->secondary_button_label,
            'secondary_button_url' => $hero->secondary_button_url,
            'tertiary_button_label' => $hero->tertiary_button_label,
            'tertiary_button_url' => $hero->tertiary_button_url,
            'image_one' => $hero->image_one,
            'image_one_url' => $hero->image_one_url,
            'image_one_alt' => $hero->image_one_alt,
            'image_two' => $hero->image_two,
            'image_two_url' => $hero->image_two_url,
            'image_two_alt' => $hero->image_two_alt,
            'image_three' => $hero->image_three,
            'image_three_url' => $hero->image_three_url,
            'image_three_alt' => $hero->image_three_alt,
            'image_four' => $hero->image_four,
            'image_four_url' => $hero->image_four_url,
            'image_four_alt' => $hero->image_four_alt,
            'floating_badge_top_icon' => $hero->floating_badge_top_icon,
            'floating_badge_top_title' => $hero->floating_badge_top_title,
            'floating_badge_top_subtitle' => $hero->floating_badge_top_subtitle,
            'floating_badge_bottom_icon' => $hero->floating_badge_bottom_icon,
            'floating_badge_bottom_title' => $hero->floating_badge_bottom_title,
            'floating_badge_bottom_subtitle' => $hero->floating_badge_bottom_subtitle,
            'is_active' => (bool) $hero->is_active,
        ];
    }

    private function ensureDefaultHeroSetting(): ManagementHeroSetting
    {
        return ManagementHeroSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'badge' => 'Struktur Kepengurusan HMPS RPL',
                'title' => 'Kepengurusan',
                'title_highlight' => 'HMPS RPL',
                'description' => 'Halaman ini menampilkan susunan kepengurusan HMPS Rekayasa Perangkat Lunak, mulai dari pengurus inti, koordinator divisi, hingga anggota pada masing-masing divisi secara rapi dan profesional.',
                'primary_button_label' => 'Inti',
                'primary_button_url' => '#pengurus-inti',
                'secondary_button_label' => 'Divisi',
                'secondary_button_url' => '#divisi',
                'tertiary_button_label' => 'Semangat',
                'tertiary_button_url' => '#closing',
                'image_one_alt' => 'Kolaborasi pengurus HMPS RPL',
                'image_two_alt' => 'Diskusi pengurus organisasi',
                'image_three_alt' => 'Kegiatan organisasi mahasiswa',
                'image_four_alt' => 'Koordinasi kegiatan HMPS RPL',
                'floating_badge_top_icon' => '👥',
                'floating_badge_top_title' => 'Struktur Tersusun',
                'floating_badge_top_subtitle' => 'Pengurus dan divisi',
                'floating_badge_bottom_icon' => '🚀',
                'floating_badge_bottom_title' => 'Aktif & Profesional',
                'floating_badge_bottom_subtitle' => 'HMPS Rekayasa Perangkat Lunak',
                'is_active' => true,
            ]
        );
    }

    private function heroImageFields(): array
    {
        return [
            'image_one_file' => 'image_one',
            'image_two_file' => 'image_two',
            'image_three_file' => 'image_three',
            'image_four_file' => 'image_four',
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function deleteImageIfExists(?string $path): void
    {
        if (
            filled($path) &&
            ! str_starts_with($path, 'http://') &&
            ! str_starts_with($path, 'https://') &&
            ! str_starts_with($path, 'data:image') &&
            Storage::disk('public')->exists($path)
        ) {
            Storage::disk('public')->delete($path);
        }
    }
}
