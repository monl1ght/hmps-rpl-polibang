<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ProgramWork;
use App\Models\ProgramWorkCategory;
use App\Models\ProgramWorkHeroSetting;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProgramKerjaController extends Controller
{
    public function index(): Response
    {
        $this->ensureDefaultHeroSetting();

        $categories = ProgramWorkCategory::query()
            ->ordered()
            ->get();

        $programs = ProgramWork::query()
            ->with('category:id,name,slug,title,description,sort_order')
            ->published()
            ->ordered()
            ->get()
            ->map(fn(ProgramWork $program) => $this->transformProgram($program))
            ->values()
            ->all();

        $categoryInfo = collect([
            [
                'key' => 'Semua',
                'title' => 'Semua Program',
                'desc' => 'Menampilkan seluruh rancangan program kerja HMPS RPL dalam satu halaman.',
            ],
        ])->concat(
            $categories->map(fn(ProgramWorkCategory $category) => [
                'key' => $category->name,
                'title' => $category->title ?: "Program {$category->name}",
                'desc' => $category->description ?: "Kategori {$category->name} pada program kerja HMPS RPL.",
            ])
        )->values()->all();

        $ticks = collect(['Program Kerja HMPS RPL 2026'])
            ->concat($categories->pluck('name'))
            ->concat(['Kolaboratif', 'Profesional', 'Inovatif'])
            ->unique()
            ->values()
            ->all();

        return Inertia::render('user/pages/ProgramKerja', [
            'programHero' => $this->programHeroPayload(),
            'programs' => $programs,
            'categoryInfo' => $categoryInfo,
            'ticks' => $ticks,
        ]);
    }

    private function programHeroPayload(): array
    {
        $setting = ProgramWorkHeroSetting::query()
            ->where('is_active', true)
            ->latest('id')
            ->first() ?: ProgramWorkHeroSetting::query()->latest('id')->first();

        return [
            'id' => $setting?->id,
            'badge' => $setting?->badge ?: 'Halaman Program Kerja HMPS RPL',
            'title' => $setting?->title ?: 'Program Kerja',
            'titleHighlight' => $setting?->title_highlight ?: 'HMPS RPL',
            'description' => $setting?->description ?: 'Halaman ini menampilkan seluruh rancangan program kerja HMPS Rekayasa Perangkat Lunak secara rapi, modern, dan mudah dipahami berdasarkan kategori, divisi pelaksana, penanggung jawab, serta estimasi anggaran.',
            'primaryButtonLabel' => $setting?->primary_button_label ?: 'Lihat Semua Program',
            'primaryButtonUrl' => $setting?->primary_button_url ?: '#program-list',
            'secondaryButtonLabel' => $setting?->secondary_button_label ?: 'Konsultasi Gratis',
            'secondaryButtonUrl' => $setting?->secondary_button_url ?: '/konsultasi',
            'images' => [
                [
                    'src' => $setting?->image_one_url ?: 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=800&h=600&fit=crop&auto=format',
                    'alt' => $setting?->image_one_alt ?: 'Workshop dan program kerja mahasiswa',
                ],
                [
                    'src' => $setting?->image_two_url ?: 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=700&h=900&fit=crop&auto=format',
                    'alt' => $setting?->image_two_alt ?: 'Seminar dan kegiatan mahasiswa',
                ],
                [
                    'src' => $setting?->image_three_url ?: 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?w=700&h=900&fit=crop&auto=format',
                    'alt' => $setting?->image_three_alt ?: 'Upgrading dan koordinasi organisasi',
                ],
                [
                    'src' => $setting?->image_four_url ?: 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&h=600&fit=crop&auto=format',
                    'alt' => $setting?->image_four_alt ?: 'Kolaborasi organisasi mahasiswa',
                ],
            ],
            'floatingBadgeTopIcon' => $setting?->floating_badge_top_icon ?: '📌',
            'floatingBadgeTopTitle' => $setting?->floating_badge_top_title ?: 'Program Tersusun',
            'floatingBadgeTopSubtitle' => $setting?->floating_badge_top_subtitle ?: 'Berdasarkan kategori',
            'floatingBadgeBottomIcon' => $setting?->floating_badge_bottom_icon ?: '🚀',
            'floatingBadgeBottomTitle' => $setting?->floating_badge_bottom_title ?: 'Fokus Periode',
            'floatingBadgeBottomSubtitle' => $setting?->floating_badge_bottom_subtitle ?: 'Kegiatan berkelanjutan',
        ];
    }

    private function ensureDefaultHeroSetting(): void
    {
        ProgramWorkHeroSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'badge' => 'Halaman Program Kerja HMPS RPL',
                'title' => 'Program Kerja',
                'title_highlight' => 'HMPS RPL',
                'description' => 'Halaman ini menampilkan seluruh rancangan program kerja HMPS Rekayasa Perangkat Lunak secara rapi, modern, dan mudah dipahami berdasarkan kategori, divisi pelaksana, penanggung jawab, serta estimasi anggaran.',
                'primary_button_label' => 'Lihat Semua Program',
                'primary_button_url' => '#program-list',
                'secondary_button_label' => 'Konsultasi Gratis',
                'secondary_button_url' => '/konsultasi',
                'image_one_alt' => 'Workshop dan program kerja mahasiswa',
                'image_two_alt' => 'Seminar dan kegiatan mahasiswa',
                'image_three_alt' => 'Upgrading dan koordinasi organisasi',
                'image_four_alt' => 'Kolaborasi organisasi mahasiswa',
                'floating_badge_top_icon' => '📌',
                'floating_badge_top_title' => 'Program Tersusun',
                'floating_badge_top_subtitle' => 'Berdasarkan kategori',
                'floating_badge_bottom_icon' => '🚀',
                'floating_badge_bottom_title' => 'Fokus Periode',
                'floating_badge_bottom_subtitle' => 'Kegiatan berkelanjutan',
                'is_active' => true,
            ]
        );
    }

    private function transformProgram(ProgramWork $program): array
    {
        $categoryName = $program->category?->name ?? 'Tanpa Kategori';
        $categorySlug = $program->category?->slug ?? 'lainnya';

        return [
            'id' => $program->id,
            'category' => $categoryName,
            'title' => $program->title,
            'date' => $program->schedule_text ?: '-',
            'division' => $program->division_name ?: 'HMPS RPL',
            'person' => $program->person_in_charge ?: '-',
            'target' => $program->target ?: '-',
            'budget' => (int) $program->budget,
            'budgetText' => $program->budget_text,
            'tagClass' => $this->resolveTagClass($categorySlug),
            'img' => $program->image_url,
            'desc' => $program->excerpt ?: Str::limit(strip_tags((string) $program->description), 140),
            'description' => $program->description ?: $program->excerpt ?: '-',
            'goals' => $program->goals ?? [],
            'benefits' => $program->benefits ?? [],
            'sources' => $program->funding_sources ?? [],
            'isFeatured' => (bool) $program->is_featured,
        ];
    }

    private function resolveTagClass(string $slug): string
    {
        return match ($slug) {
            'unggulan' => 'bg-sky-500',
            'pengabdian' => 'bg-emerald-500',
            'pendukung' => 'bg-violet-600',
            'rutin' => 'bg-amber-500',
            default => 'bg-slate-700',
        };
    }
}
