<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\HomeHighlight;
use App\Models\HomeMedia;
use App\Models\HomeSection;
use App\Models\HomeTicker;
use App\Models\ProgramWork;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $gallery = HomeMedia::query()
            ->group('gallery')
            ->active()
            ->ordered()
            ->take(6)
            ->get()
            ->map(fn(HomeMedia $media) => [
                'id' => $media->id,
                'title' => $media->title ?: 'Dokumentasi HMPS RPL',
                'category' => $media->category ?: 'Dokumentasi',
                'image' => $media->image_url,
                'alt' => $media->alt_text ?: $media->title ?: 'Dokumentasi HMPS RPL',
                'layout' => $media->layout ?: 'normal',
            ])
            ->values()
            ->all();
        $sections = HomeSection::query()
            ->active()
            ->ordered()
            ->get()
            ->keyBy('key');

        $ticks = HomeTicker::query()
            ->active()
            ->ordered()
            ->pluck('label')
            ->values()
            ->all();

        $highlights = HomeHighlight::query()
            ->active()
            ->ordered()
            ->get()
            ->map(fn(HomeHighlight $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'desc' => $item->description,
            ])
            ->values()
            ->all();

        $heroImages = HomeMedia::query()
            ->group('hero')
            ->active()
            ->ordered()
            ->take(4)
            ->get()
            ->map(fn(HomeMedia $media) => $this->transformMedia($media))
            ->values()
            ->all();

        $gallery = HomeMedia::query()
            ->group('gallery')
            ->active()
            ->ordered()
            ->take(6)
            ->get()
            ->map(fn(HomeMedia $media) => $this->transformMedia($media))
            ->values()
            ->all();

        $featuredPrograms = $this->featuredPrograms();

        $prokers = $featuredPrograms
            ->map(fn(ProgramWork $program) => [
                'id' => $program->id,
                'title' => $program->title,
                'tag' => $program->category?->name ?? 'Program',
                'tagClass' => $this->resolveTagClass($program->category?->slug),
                'date' => $program->schedule_text ?: '-',
                'division' => $program->division_name ?: 'HMPS RPL',
                'desc' => $program->excerpt ?: Str::limit(strip_tags((string) $program->description), 120),
                'img' => $program->image_url,
            ])
            ->values()
            ->all();

        return Inertia::render('user/pages/Home', [
            'homeHero' => $this->sectionData($sections, 'hero', [
                'badge' => 'Organisasi Mahasiswa Rekayasa Perangkat Lunak',
                'titleHighlight' => 'HMPS',
                'title' => 'Rekayasa Perangkat Lunak',
                'description' => 'Wadah aspirasi, kolaborasi, pengembangan skill, dan Program Kerja Mahasiswa RPL.',
                'primaryButtonLabel' => 'Lihat Program Kerja',
                'primaryButtonUrl' => '#proker',
                'secondaryButtonLabel' => 'Konsultasi Gratis',
                'secondaryButtonUrl' => '/konsultasi',
                'meta' => [
                    'top_badge_title' => 'Aktif & Produktif',
                    'top_badge_subtitle' => 'Organisasi mahasiswa RPL',
                    'bottom_badge_title' => 'Kegiatan Berkelanjutan',
                    'bottom_badge_subtitle' => 'Satu periode penuh',
                ],
            ]),

            'homeAbout' => $this->sectionData($sections, 'about', [
                'badge' => 'Tentang Kami',
                'titleHighlight' => 'HMPS RPL',
                'title' => 'Sekilas tentang',
                'description' => 'HMPS Rekayasa Perangkat Lunak merupakan organisasi mahasiswa yang menjadi wadah aspirasi, pengembangan potensi, dan kolaborasi mahasiswa RPL. Melalui berbagai kegiatan, HMPS RPL berupaya membangun lingkungan yang aktif, kreatif, dan profesional.',
                'primaryButtonLabel' => 'Lihat Profil Lengkap',
                'primaryButtonUrl' => '/profil',
                'meta' => [
                    'highlight_title' => 'Organisasi mahasiswa yang fokus pada pengembangan, kebersamaan, dan kontribusi nyata.',
                ],
            ]),

            'homeProgram' => $this->sectionData($sections, 'proker', [
                'badge' => 'Agenda Kami',
                'titleHighlight' => 'Unggulan',
                'title' => 'Program Kerja',
                'description' => 'Berbagai kegiatan yang dirancang untuk memperkuat kompetensi, solidaritas, dan pengalaman mahasiswa Rekayasa Perangkat Lunak.',
                'primaryButtonLabel' => 'Lihat Semua Program',
                'primaryButtonUrl' => '/program-kerja',
            ]),

            'homeGallerySection' => $this->sectionData($sections, 'gallery', [
                'badge' => 'Dokumentasi Kegiatan',
                'titleHighlight' => 'HMPS RPL',
                'title' => 'Galeri',
                'description' => 'Potret momen, kebersamaan, dan aktivitas yang menunjukkan semangat HMPS RPL dalam bergerak dan berkarya.',
                'primaryButtonLabel' => 'Lihat Semua Dokumentasi',
                'primaryButtonUrl' => '/dokumentasi',
            ]),

            'heroImages' => $heroImages,
            'gallery' => $gallery,
            'ticks' => $ticks,
            'highlights' => $highlights,
            'prokers' => $prokers,
        ]);
    }

    private function featuredPrograms(): Collection
    {
        $featured = ProgramWork::query()
            ->with('category:id,name,slug')
            ->published()
            ->where('is_featured', true)
            ->ordered()
            ->take(3)
            ->get();

        if ($featured->count() >= 3) {
            return $featured;
        }

        $fallback = ProgramWork::query()
            ->with('category:id,name,slug')
            ->published()
            ->whereNotIn('id', $featured->pluck('id'))
            ->ordered()
            ->take(3 - $featured->count())
            ->get();

        return $featured->concat($fallback)->values();
    }

    private function sectionData(Collection $sections, string $key, array $fallback): array
    {
        /** @var HomeSection|null $section */
        $section = $sections->get($key);

        if (! $section) {
            return $fallback;
        }

        return [
            'badge' => $section->badge ?: ($fallback['badge'] ?? null),
            'title' => $section->title ?: ($fallback['title'] ?? null),
            'titleHighlight' => $section->title_highlight ?: ($fallback['titleHighlight'] ?? null),
            'description' => $section->description ?: ($fallback['description'] ?? null),
            'primaryButtonLabel' => $section->primary_button_label ?: ($fallback['primaryButtonLabel'] ?? null),
            'primaryButtonUrl' => $section->primary_button_url ?: ($fallback['primaryButtonUrl'] ?? null),
            'secondaryButtonLabel' => $section->secondary_button_label ?: ($fallback['secondaryButtonLabel'] ?? null),
            'secondaryButtonUrl' => $section->secondary_button_url ?: ($fallback['secondaryButtonUrl'] ?? null),
            'meta' => array_replace_recursive($fallback['meta'] ?? [], $section->meta ?? []),
        ];
    }

    private function transformMedia(HomeMedia $media): array
    {
        return [
            'id' => $media->id,
            'title' => $media->title,
            'subtitle' => $media->subtitle,
            'category' => $media->category,
            'image' => $media->image_url,
            'alt' => $media->alt_text ?: $media->title ?: 'Dokumentasi HMPS RPL',
            'layout' => $media->layout ?: 'normal',
        ];
    }

    private function resolveTagClass(?string $slug): string
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
