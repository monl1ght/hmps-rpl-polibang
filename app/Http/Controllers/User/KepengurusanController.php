<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ManagementDivision;
use App\Models\ManagementHeroSetting;
use App\Models\ManagementMember;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class KepengurusanController extends Controller
{
    public function index(): Response
    {
        $hero = $this->ensureDefaultHeroSetting();

        $topLeaders = ManagementMember::query()
            ->select([
                'id',
                'management_division_id',
                'name',
                'position',
                'task_description',
                'category',
                'photo',
                'sort_order',
            ])
            ->core()
            ->ordered()
            ->get();

        $divisions = ManagementDivision::query()
            ->select([
                'id',
                'name',
                'short_name',
                'description',
                'sort_order',
            ])
            ->ordered()
            ->with([
                'members' => function ($query) {
                    $query->select([
                        'id',
                        'management_division_id',
                        'name',
                        'position',
                        'task_description',
                        'category',
                        'photo',
                        'sort_order',
                    ])->ordered();
                },
            ])
            ->get();

        return Inertia::render('user/pages/Kepengurusan', [
            'managementHero' => $this->heroPayload($hero),

            'topLeaders' => $topLeaders
                ->map(fn (ManagementMember $member) => $this->transformTopLeader($member))
                ->values()
                ->all(),

            'divisions' => $divisions
                ->map(fn (ManagementDivision $division) => $this->transformDivision($division))
                ->values()
                ->all(),

            'stats' => $this->buildStats($topLeaders, $divisions),
        ]);
    }

    private function heroPayload(ManagementHeroSetting $hero): array
    {
        return [
            'id' => $hero->id,
            'badge' => $hero->badge,
            'title' => $hero->title,
            'titleHighlight' => $hero->title_highlight,
            'description' => $hero->description,
            'primaryButtonLabel' => $hero->primary_button_label,
            'primaryButtonUrl' => $hero->primary_button_url,
            'secondaryButtonLabel' => $hero->secondary_button_label,
            'secondaryButtonUrl' => $hero->secondary_button_url,
            'tertiaryButtonLabel' => $hero->tertiary_button_label,
            'tertiaryButtonUrl' => $hero->tertiary_button_url,
            'imageOneUrl' => $hero->image_one_url,
            'imageOneAlt' => $hero->image_one_alt,
            'imageTwoUrl' => $hero->image_two_url,
            'imageTwoAlt' => $hero->image_two_alt,
            'imageThreeUrl' => $hero->image_three_url,
            'imageThreeAlt' => $hero->image_three_alt,
            'imageFourUrl' => $hero->image_four_url,
            'imageFourAlt' => $hero->image_four_alt,
            'floatingBadgeTopIcon' => $hero->floating_badge_top_icon,
            'floatingBadgeTopTitle' => $hero->floating_badge_top_title,
            'floatingBadgeTopSubtitle' => $hero->floating_badge_top_subtitle,
            'floatingBadgeBottomIcon' => $hero->floating_badge_bottom_icon,
            'floatingBadgeBottomTitle' => $hero->floating_badge_bottom_title,
            'floatingBadgeBottomSubtitle' => $hero->floating_badge_bottom_subtitle,
        ];
    }

    private function transformTopLeader(ManagementMember $member): array
    {
        return [
            'id' => $member->id,
            'role' => $member->position,
            'name' => $member->name,
            'photo' => $member->photo_url,
            'task_description' => $member->task_description,
            'description' => $this->memberTaskDescription($member),
        ];
    }

    private function transformDivision(ManagementDivision $division): array
    {
        $coordinator = $division->members
            ->firstWhere('category', ManagementMember::CATEGORY_COORDINATOR);

        $members = $division->members
            ->where('category', ManagementMember::CATEGORY_MEMBER)
            ->values();

        return [
            'id' => $division->id,
            'name' => $division->name,
            'short' => $division->short_name ?: $division->name,
            'description' => $division->description ?: "Divisi {$division->name} HMPS RPL.",
            'coordinator' => $coordinator
                ? [
                    'id' => $coordinator->id,
                    'name' => $coordinator->name,
                    'role' => $coordinator->position,
                    'photo' => $coordinator->photo_url,
                    'task_description' => $coordinator->task_description,
                    'description' => $this->memberTaskDescription($coordinator),
                ]
                : null,
            'members' => $members
                ->map(fn (ManagementMember $member) => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'role' => $member->position,
                    'photo' => $member->photo_url,
                    'task_description' => $member->task_description,
                    'description' => $this->memberTaskDescription($member),
                ])
                ->values()
                ->all(),
        ];
    }

    private function memberTaskDescription(ManagementMember $member): string
    {
        $description = trim((string) $member->task_description);

        if ($description !== '') {
            return $description;
        }

        $position = mb_strtolower((string) $member->position);

        if (str_contains($position, 'ketua')) {
            return 'Bertanggung jawab memimpin arah organisasi, mengoordinasikan pengurus, mengambil keputusan strategis, serta memastikan seluruh program kerja berjalan selaras dengan visi HMPS RPL.';
        }

        if (str_contains($position, 'sekretaris')) {
            return 'Bertanggung jawab mengelola administrasi organisasi, surat-menyurat, notulensi, arsip, dan dokumentasi resmi agar tata kelola HMPS RPL berjalan tertib dan profesional.';
        }

        if (str_contains($position, 'bendahara')) {
            return 'Bertanggung jawab mengelola keuangan organisasi, menyusun pencatatan pemasukan dan pengeluaran, serta menjaga transparansi laporan keuangan HMPS RPL.';
        }

        return 'Pengurus HMPS RPL yang berperan aktif dalam koordinasi, pelaksanaan program kerja, dan penguatan budaya organisasi yang profesional.';
    }

    private function buildStats(Collection $topLeaders, Collection $divisions): array
    {
        $coordinatorCount = $divisions
            ->sum(fn (ManagementDivision $division) => $division->members
                ->where('category', ManagementMember::CATEGORY_COORDINATOR)
                ->count());

        $memberCount = $divisions
            ->sum(fn (ManagementDivision $division) => $division->members
                ->where('category', ManagementMember::CATEGORY_MEMBER)
                ->count());

        return [
            [
                'label' => 'Pengurus Inti',
                'value' => (string) $topLeaders->count(),
            ],
            [
                'label' => 'Jumlah Divisi',
                'value' => (string) $divisions->count(),
            ],
            [
                'label' => 'Total Pengurus',
                'value' => (string) ($topLeaders->count() + $coordinatorCount + $memberCount),
            ],
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
}
