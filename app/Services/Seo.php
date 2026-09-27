<?php

namespace App\Services;

use App\Models\Setting;
use App\Support\Locale;
use Carbon\Carbon;

/** Builds <head> meta, hreflang alternates and schema.org JSON-LD for each public page. */
class Seo
{
    private const DEFAULTS = [
        'home' => [
            'en' => ['Loyal Restaurant & Cafe | Shisha, Specialty Coffee & Match Nights in Madinat Zayed, Abu Dhabi', 'Premium shisha lounge and cafe on Al Marami St, Madinat Zayed. Specialty coffee, fresh juices and food, big LED screens for live football. Open daily 8 AM – 3 AM.'],
            'ar' => ['مطعم وكافيه لويال | شيشة وقهوة مختصة وسهرات المباريات في مدينة زايد، أبوظبي', 'لاونج شيشة وكافيه راقٍ في شارع المرامي، مدينة زايد. قهوة مختصة وعصائر طازجة وأطباق شهية وشاشات LED كبيرة لمشاهدة المباريات. مفتوح يومياً من 8 صباحاً حتى 3 فجراً.'],
        ],
        'menu' => [
            'en' => ['Menu | Loyal Restaurant & Cafe Abu Dhabi', 'Browse our shisha flavours, specialty coffee, fresh juices, avocado drinks, breakfast and food — with prices in AED.'],
            'ar' => ['القائمة | مطعم وكافيه لويال أبوظبي', 'تصفح نكهات الشيشة والقهوة المختصة والعصائر الطازجة ومشروبات الأفوكادو والفطور والأطباق مع الأسعار بالدرهم.'],
        ],
        'gallery' => [
            'en' => ['Gallery | Loyal Restaurant & Cafe', 'See the lounge, the terrace, the shisha and the big screens before you visit.'],
            'ar' => ['معرض الصور | مطعم وكافيه لويال', 'شاهد اللاونج والجلسات الخارجية والشيشة والشاشات الكبيرة قبل زيارتك.'],
        ],
        'match-nights' => [
            'en' => ['Where to Watch Football in Abu Dhabi | Match Nights at Loyal Cafe', 'Watch the FIFA World Cup 2026 and every big match live on our LED screens in Madinat Zayed. See the fixtures and reserve your table.'],
            'ar' => ['أين تشاهد المباريات في أبوظبي | سهرات المباريات في كافيه لويال', 'شاهد كأس العالم 2026 وكل المباريات الكبرى مباشرة على شاشاتنا في مدينة زايد. اطّلع على الجدول واحجز طاولتك.'],
        ],
        'reserve' => [
            'en' => ['Reserve a Table | Loyal Restaurant & Cafe', 'Book your table online for coffee, shisha or a match night in Madinat Zayed, Abu Dhabi.'],
            'ar' => ['احجز طاولة | مطعم وكافيه لويال', 'احجز طاولتك أونلاين للقهوة أو الشيشة أو سهرة مباراة في مدينة زايد، أبوظبي.'],
        ],
        'contact' => [
            'en' => ['Contact & Directions | Loyal Restaurant & Cafe', 'Al Marami St 8, Madinat Zayed, Abu Dhabi. Call 050 278 3288 or message us on WhatsApp. Free parking.'],
            'ar' => ['تواصل معنا والموقع | مطعم وكافيه لويال', 'شارع المرامي 8، مدينة زايد، أبوظبي. اتصل على 050 278 3288 أو راسلنا عبر واتساب. مواقف مجانية.'],
        ],
    ];

    public function __construct(private readonly SiteContent $content) {}

    /** Title + description for every page — shipped to the SPA so client navigation updates <head>. */
    public function titles(string $locale): array
    {
        return collect(config('loyal.pages'))->mapWithKeys(function ($page) use ($locale) {
            $seo = Setting::get("seo.$page", []);
            [$t, $d] = self::DEFAULTS[$page][$locale] ?? self::DEFAULTS['home']['en'];

            return [$page => [
                'title' => tr($seo['title'] ?? null, $locale) ?: $t,
                'description' => tr($seo['description'] ?? null, $locale) ?: $d,
            ]];
        })->all();
    }

    public function forPage(string $page, string $locale): array
    {
        $seo = Setting::get("seo.$page", []);
        [$defTitle, $defDesc] = self::DEFAULTS[$page][$locale] ?? self::DEFAULTS['home'][$locale] ?? self::DEFAULTS['home']['en'];
        $path = $page === 'home' ? '' : '/'.$page;

        $alternates = [];
        foreach (Locale::all() as $l) {
            $alternates[$l] = url("/$l$path");
        }

        return [
            'title' => tr($seo['title'] ?? null, $locale) ?: $defTitle,
            'description' => tr($seo['description'] ?? null, $locale) ?: $defDesc,
            'canonical' => url("/$locale$path"),
            'alternates' => $alternates,
            'x_default' => $alternates[Locale::default()],
            'og_image' => media_url(Setting::get('og_image')) ? url(media_url(Setting::get('og_image'))) : null,
            'og_locale' => $locale === 'ar' ? 'ar_AE' : 'en_AE',
            'json_ld' => $this->jsonLd($page, $locale),
        ];
    }

    private function jsonLd(string $page, string $locale): array
    {
        $s = Setting::allAsArray();
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        $business = array_filter([
            '@context' => 'https://schema.org',
            '@type' => ['Restaurant', 'CafeOrCoffeeShop'],
            '@id' => url('/').'#business',
            'name' => tr($s['site_name'] ?? null, $locale) ?? 'Loyal Restaurant & Cafe',
            'url' => url("/$locale"),
            'telephone' => $s['phone'] ?? null,
            'image' => media_url($s['og_image'] ?? null) ? url(media_url($s['og_image'])) : null,
            'logo' => media_url($s['logo'] ?? null) ? url(media_url($s['logo'])) : null,
            'priceRange' => 'AED 15–120',
            'servesCuisine' => ['Cafe', 'Middle Eastern', 'Shisha lounge'],
            'acceptsReservations' => true,
            'hasMenu' => url("/$locale/menu"),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => tr($s['address']['street'] ?? null, 'en') ?? 'Al Marami St 8',
                'addressLocality' => 'Madinat Zayed',
                'addressRegion' => 'Abu Dhabi',
                'addressCountry' => 'AE',
            ],
            'geo' => isset($s['maps']['lat'], $s['maps']['lng'])
                ? ['@type' => 'GeoCoordinates', 'latitude' => (float) $s['maps']['lat'], 'longitude' => (float) $s['maps']['lng']]
                : null,
            'openingHoursSpecification' => collect($s['hours'] ?? [])->reject(fn ($h) => $h['closed'] ?? false)->map(fn ($h) => [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => $dayNames[$h['day']],
                'opens' => $h['open'],
                'closes' => $h['close'],
            ])->values()->all(),
            'aggregateRating' => isset($s['rating']['value'], $s['rating']['count']) ? [
                '@type' => 'AggregateRating',
                'ratingValue' => (string) $s['rating']['value'],
                'reviewCount' => (string) $s['rating']['count'],
            ] : null,
            'sameAs' => array_values(array_filter($s['socials'] ?? [])),
            'amenityFeature' => collect($s['amenities'] ?? [])->map(fn ($a) => [
                '@type' => 'LocationFeatureSpecification', 'name' => tr($a['label'] ?? null, 'en'), 'value' => true,
            ])->all(),
        ]);

        $graph = [$business];

        if ($page === 'match-nights') {
            foreach (array_slice($this->content->events(), 0, 12) as $e) {
                $start = Carbon::parse($e['starts_at']);
                $graph[] = [
                    '@context' => 'https://schema.org',
                    '@type' => 'Event',
                    'name' => $e['title'].' — '.($business['name']),
                    'startDate' => $e['starts_at'],
                    'endDate' => $start->copy()->addHours(2)->toIso8601String(),
                    'eventStatus' => 'https://schema.org/EventScheduled',
                    'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
                    'location' => ['@id' => url('/').'#business'],
                    'description' => $e['description'] ?? $e['offer'],
                    'organizer' => ['@id' => url('/').'#business'],
                ];
            }
        }

        return $graph;
    }
}
