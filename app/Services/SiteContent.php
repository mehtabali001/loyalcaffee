<?php

namespace App\Services;

use App\Http\Resources\EventResource;
use App\Http\Resources\GalleryItemResource;
use App\Http\Resources\MenuCategoryResource;
use App\Http\Resources\MenuItemResource;
use App\Http\Resources\OfferResource;
use App\Http\Resources\SectionResource;
use App\Http\Resources\TestimonialResource;
use App\Models\Event;
use App\Models\GalleryItem;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Offer;
use App\Models\Section;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\Translation;
use App\Support\ContentVersion;
use App\Support\Locale;

/**
 * Single source for everything the public site reads. Each block is cached per locale
 * and invalidated automatically whenever any content model is written.
 */
class SiteContent
{
    /** Keys never exposed publicly. */
    private const PRIVATE_SETTINGS = ['reservation.notify_email'];

    public function settings(): array
    {
        return ContentVersion::remember('settings', function () {
            $all = Setting::allAsArray();
            foreach (self::PRIVATE_SETTINGS as $k) {
                data_forget($all, $k);
            }
            $out = SectionResource::localiseData($all);
            foreach (['logo', 'logo_light', 'og_image', 'favicon'] as $img) {
                $out[$img] = media_url($all[$img] ?? null);
            }

            return $out;
        });
    }

    /** Everything needed for first paint — inlined into the HTML so the SPA boots with zero API calls. */
    public function bootstrap(): array
    {
        return [
            'locale' => app()->getLocale(),
            'locales' => Locale::all(),
            'rtl' => Locale::isRtl(),
            'settings' => $this->settings(),
            'messages' => ContentVersion::remember('ui-messages', fn () => Translation::messagesFor(app()->getLocale())),
            'offers' => $this->offers(),
            'version' => ContentVersion::current(),
        ];
    }

    public function sections(string $page = 'home'): array
    {
        return ContentVersion::remember("sections:$page", fn () => SectionResource::collection(
            Section::query()->where('page', $page)->active()->ordered()->get()
        )->resolve());
    }

    public function home(): array
    {
        return ContentVersion::remember('home', fn () => [
            'sections' => $this->sections('home'),
            'featured' => MenuItemResource::collection(
                MenuItem::query()->active()->where('is_featured', true)
                    ->whereHas('category', fn ($q) => $q->where('is_active', true))
                    ->ordered()->limit(8)->get()
            )->resolve(),
            'events' => $this->events(4),
            'gallery' => GalleryItemResource::collection(
                GalleryItem::query()->active()->orderByDesc('is_featured')->ordered()->limit(9)->get()
            )->resolve(),
            'testimonials' => TestimonialResource::collection(Testimonial::query()->active()->ordered()->limit(10)->get())->resolve(),
        ]);
    }

    public function menu(): array
    {
        return ContentVersion::remember('menu', fn () => MenuCategoryResource::collection(
            MenuCategory::query()->active()->ordered()
                ->with(['items' => fn ($q) => $q->where('is_active', true)])
                ->get()
        )->resolve());
    }

    public function gallery(): array
    {
        return ContentVersion::remember('gallery', fn () => GalleryItemResource::collection(
            GalleryItem::query()->active()->ordered()->get()
        )->resolve());
    }

    public function events(?int $limit = null): array
    {
        return ContentVersion::remember('events:'.($limit ?? 'all'), function () use ($limit) {
            $q = Event::query()->active()->upcoming()
                ->withSum(['reservations as seats_taken' => fn ($r) => $r->whereIn('status', ['pending', 'confirmed', 'seated'])], 'guests');
            if ($limit) {
                $q->limit($limit);
            }

            return EventResource::collection($q->get())->resolve();
        }, 300); // short TTL: depends on the clock and on live seat counts
    }

    public function offers(): array
    {
        return ContentVersion::remember('offers', fn () => OfferResource::collection(Offer::query()->current()->ordered()->get())->resolve());
    }
}
