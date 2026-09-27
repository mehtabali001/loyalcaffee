<?php

use App\Models\GalleryItem;
use App\Models\Offer;
use App\Models\Section;
use Illuminate\Database\Migrations\Migration;

/**
 * Replaces the seeded SVG placeholders with real photography (public/images/photos, see CREDITS.txt).
 * Only values that still point at a placeholder (or are empty where a photo is now expected) change,
 * so anything the owner has already uploaded through Admin is left alone.
 */
return new class extends Migration
{
    private const PHOTOS = '/images/photos/';

    public function up(): void
    {
        $swap = fn (?string $current, string $photo) => ($current === null || str_starts_with($current, '/images/placeholders/'))
            ? self::PHOTOS.$photo
            : $current;

        foreach (Section::query()->where('page', 'home')->get() as $s) {
            match ($s->type) {
                'hero' => [$s->image = $swap($s->image, 'hero-day.webp'), $s->image_secondary = $swap($s->image_secondary, 'hero-night.webp')],
                'story' => $s->image = $swap($s->image, 'lounge-green.webp'),
                'matches' => $s->image = $swap($s->image, 'match-crowd.webp'),
                'day_night' => $s->data = $this->dayNight($s->data ?? [], $swap),
                default => null,
            };
            if ($s->isDirty()) {
                $s->save();
            }
        }

        $offerPhotos = ['Golden hour' => 'iced-latte.webp', 'Bring the table' => 'mezze.webp', "First cup's on us" => 'latte-art.webp'];
        foreach (Offer::query()->whereNull('image')->get() as $o) {
            if ($photo = $offerPhotos[$o->title['en'] ?? ''] ?? null) {
                $o->image = self::PHOTOS.$photo;
                $o->save();
            }
        }

        // The gallery is only rebuilt while it holds nothing but the seeded placeholders.
        $images = GalleryItem::query()->pluck('image');
        if ($images->every(fn ($p) => str_starts_with((string) $p, '/images/placeholders/'))) {
            GalleryItem::query()->delete();
            foreach (self::gallery() as $i => [$cat, $file, $en, $ar, $featured]) {
                (new GalleryItem(['category' => $cat, 'image' => self::PHOTOS.$file, 'is_featured' => $featured, 'is_active' => true, 'sort_order' => $i + 1]))
                    ->fillTranslations(['caption' => ['en' => $en, 'ar' => $ar]])->save();
            }
        }
    }

    public function down(): void
    {
        // Content change only; nothing structural to roll back.
    }

    private function dayNight(array $data, \Closure $swap): array
    {
        foreach (['day' => 'coffee-sunlight.webp', 'night' => 'shisha-gold.webp'] as $key => $photo) {
            if (isset($data[$key])) {
                $data[$key]['image'] = $swap($data[$key]['image'] ?? null, $photo);
            }
        }

        return $data;
    }

    /** Kept in step with GallerySeeder. */
    public static function gallery(): array
    {
        return [
            ['interior', 'lounge-green.webp', 'The lounge at golden hour', 'اللاونج وقت الغروب', true],
            ['shisha', 'shisha-gold.webp', 'Fresh coals, slow evenings', 'فحم طازج وأمسيات هادئة', true],
            ['drinks', 'dates-gahwa.webp', 'Gahwa and dates, the way we welcome guests', 'قهوة وتمر، هكذا نستقبل ضيوفنا', true],
            ['match-nights', 'match-crowd.webp', 'Screens ready for kick-off', 'الشاشات جاهزة لصافرة البداية', true],
            ['drinks', 'latte-art.webp', 'Spanish latte, poured to order', 'سبانيش لاتيه يُحضّر عند الطلب', true],
            ['food', 'mezze.webp', 'Sharing plates for the table', 'أطباق للمشاركة على الطاولة', false],
            ['interior', 'chesterfield.webp', 'Deep seats, long conversations', 'مقاعد مريحة وأحاديث تطول', false],
            ['drinks', 'avocado-shake.webp', 'Avocado cocktail', 'كوكتيل الأفوكادو', false],
            ['shisha', 'shisha-green.webp', 'Hand-packed, slow-burning', 'محضّرة يدوياً بعناية', false],
            ['drinks', 'gahwa-pour.webp', 'Arabic coffee, poured at the table', 'قهوة عربية تُصب على الطاولة', false],
            ['interior', 'terrace-lights.webp', 'The terrace after sunset', 'الجلسات الخارجية بعد الغروب', false],
            ['drinks', 'iced-latte.webp', 'Iced Spanish latte', 'سبانيش لاتيه مثلج', false],
            ['match-nights', 'match-screen.webp', 'Every big game, live', 'كل المباريات الكبرى مباشرة', false],
            ['drinks', 'dallah-row.webp', 'Brass dallahs, always warm', 'دلال نحاسية دافئة دائماً', false],
        ];
    }
};
