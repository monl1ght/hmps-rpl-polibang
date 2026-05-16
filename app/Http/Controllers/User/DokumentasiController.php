<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Documentation;
use Inertia\Inertia;
use Inertia\Response;

class DokumentasiController extends Controller
{
    public function index(): Response
    {
        $documentations = Documentation::query()
            ->with([
                'images' => fn ($query) => $query->ordered(),
            ])
            ->published()
            ->ordered()
            ->get()
            ->map(fn (Documentation $documentation) => $this->transformDocumentation($documentation))
            ->values()
            ->all();

        return Inertia::render('user/pages/Dokumentasi', [
            'documentations' => $documentations,
        ]);
    }

    private function transformDocumentation(Documentation $documentation): array
    {
        return [
            'id' => $documentation->id,
            'title' => $documentation->title,
            'slug' => $documentation->slug,
            'category' => $documentation->category,
            'year' => $documentation->year,
            'date' => optional($documentation->event_date)?->format('d M Y'),
            'location' => $documentation->location,
            'cover' => $documentation->cover_url,
            'description' => $documentation->description,
            'is_featured' => (bool) $documentation->is_featured,
            'featured_size' => $documentation->featured_size ?: Documentation::FEATURED_SIZE_MEDIUM,
            'gallery' => $documentation->images
                ->map(fn ($image) => $image->image_url)
                ->values()
                ->all(),
        ];
    }
}