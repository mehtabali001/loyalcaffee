<?php

namespace Database\Seeders;

use App\Models\GalleryItem;
use Illuminate\Database\Seeder;

/**
 * Licensed stock photography (public/images/photos/CREDITS.txt) until the professional shoot
 * (Brand Guide §3.6) replaces it via Admin → Gallery. Featured items lead the home mosaic.
 */
class GallerySeeder extends Seeder
{
    public function run(): void
    {
        GalleryItem::query()->delete();
        $items = [
            ['interior', 'lounge-green.webp', ['The lounge at golden hour', 'اللاونج وقت الغروب'], true],
            ['shisha', 'shisha-gold.webp', ['Fresh coals, slow evenings', 'فحم طازج وأمسيات هادئة'], true],
            ['drinks', 'dates-gahwa.webp', ['Gahwa and dates, the way we welcome guests', 'قهوة وتمر، هكذا نستقبل ضيوفنا'], true],
            ['match-nights', 'match-crowd.webp', ['Screens ready for kick-off', 'الشاشات جاهزة لصافرة البداية'], true],
            ['drinks', 'latte-art.webp', ['Spanish latte, poured to order', 'سبانيش لاتيه يُحضّر عند الطلب'], true],
            ['food', 'mezze.webp', ['Sharing plates for the table', 'أطباق للمشاركة على الطاولة'], false],
            ['interior', 'chesterfield.webp', ['Deep seats, long conversations', 'مقاعد مريحة وأحاديث تطول'], false],
            ['drinks', 'avocado-shake.webp', ['Avocado cocktail', 'كوكتيل الأفوكادو'], false],
            ['shisha', 'shisha-green.webp', ['Hand-packed, slow-burning', 'محضّرة يدوياً بعناية'], false],
            ['drinks', 'gahwa-pour.webp', ['Arabic coffee, poured at the table', 'قهوة عربية تُصب على الطاولة'], false],
            ['interior', 'terrace-lights.webp', ['The terrace after sunset', 'الجلسات الخارجية بعد الغروب'], false],
            ['drinks', 'iced-latte.webp', ['Iced Spanish latte', 'سبانيش لاتيه مثلج'], false],
            ['match-nights', 'match-screen.webp', ['Every big game, live', 'كل المباريات الكبرى مباشرة'], false],
            ['drinks', 'dallah-row.webp', ['Brass dallahs, always warm', 'دلال نحاسية دافئة دائماً'], false],
        ];
        foreach ($items as $i => [$cat, $file, $caption, $featured]) {
            (new GalleryItem(['category' => $cat, 'image' => "/images/photos/$file", 'is_featured' => $featured, 'is_active' => true, 'sort_order' => $i + 1]))
                ->fillTranslations(['caption' => ['en' => $caption[0], 'ar' => $caption[1]]])->save();
        }
    }
}
