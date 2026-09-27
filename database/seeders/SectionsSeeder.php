<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;

/** Home page building blocks, copy written to the Brand Voice guide (§1.5). */
class SectionsSeeder extends Seeder
{
    public function run(): void
    {
        Section::query()->where('page', 'home')->delete();

        $blocks = [
            [
                'type' => 'hero',
                'title' => ['en' => 'Stay a while.', 'ar' => 'خذ راحتك.'],
                'subtitle' => ['en' => 'Artisanal coffee & matcha by day, luxury terrace shisha by night — the GCC luxury cafe experience in Madinat Zayed, Abu Dhabi.', 'ar' => 'قهوة مختصة وماتشا منعشة في النهار، وشيشة فاخرة على التراس مساءً — تجربة المقاهي الخليجية الراقية في قلب مدينة زايد، أبوظبي.'],
                'cta_label' => ['en' => 'Reserve a table', 'ar' => 'احجز طاولة'],
                'cta_link' => '/reserve',
                'image' => '/images/photos/hero-sky-luxury.jpg',
                'image_secondary' => '/images/photos/evening-terrace.jpg',
                'data' => ['secondary_label' => ['en' => 'See the menu', 'ar' => 'تصفّح القائمة'], 'secondary_link' => '/menu'],
            ],
            [
                'type' => 'story',
                'title' => ['en' => 'Where Gulf Hospitality Meets Modern Elegance', 'ar' => 'حيث تلتقي الضيافة الخليجية بالأناقة العصرية'],
                'body' => [
                    'en' => "The best moments happen when people gather without rushing. In a vibrant city that moves fast, Loyal is your serene sanctuary — airy sky-blue accents, polished white marble, warm ambient glow, and a table that is always ready with genuine Khaleeji warmth.\n\nA morning business espresso flows into a long conversation. Families unwind over artisanal desserts. Friends gather under the evening stars for the big match.",
                    'ar' => "أجمل اللحظات تُصنع حين يجتمع الأحبة دون استعجال. في مدينة نابضة وسريعة الإيقاع، لويال هو ملاذك الهادئ: لمسات سماوية مشرقة، ورخام أبيض ناصع، وإضاءة دافئة وضيافة خليجية أصيلة ترحب بالجميع.\n\nلقاء عمل صباحي مع قهوة مختصة يتحول إلى حوار ملهم، وعائلات تستمتع بأشهى الحلويات المبتكرة، وأصدقاء يجتمعون تحت سماء المساء لمشاهدة المباراة بحماس.",
                ],
                'image' => '/images/photos/specialty-drinks.jpg',
            ],
            [
                'type' => 'day_night',
                'title' => ['en' => 'One place, two moods', 'ar' => 'مكان واحد بمزاجين'],
                'data' => [
                    'day' => [
                        'title' => ['en' => 'By day', 'ar' => 'في النهار'],
                        'body' => ['en' => 'Specialty V60, Spanish lattes, fresh avocado cocktails, and sunlit marble corners with high-speed Wi-Fi.', 'ar' => 'قهوة مختصة V60، وسبانيش لاتيه، وكوكتيلات أفوكادو طازجة، وجلسات رخامية مشمسة مع واي فاي فائق السرعة.'],
                        'time' => ['en' => 'From 8 AM', 'ar' => 'من 8 صباحاً'],
                        'image' => '/images/photos/specialty-drinks.jpg',
                    ],
                    'night' => [
                        'title' => ['en' => 'By night', 'ar' => 'في المساء'],
                        'body' => ['en' => 'Hand-packed luxury shisha, starry terrace breezes, glowing lanterns, and immersive match night screenings.', 'ar' => 'شيشة ملكية فاخرة، ونسيم التراس المفتوح، وفوانيس مضيئة، وأجواء مباريات حماسية على الشاشات العملاقة.'],
                        'time' => ['en' => 'Until 3 AM', 'ar' => 'حتى 3 فجراً'],
                        'image' => '/images/photos/evening-terrace.jpg',
                    ],
                ],
            ],
            [
                'type' => 'featured_menu',
                'title' => ['en' => 'Guests keep ordering these', 'ar' => 'الأكثر طلباً عند ضيوفنا'],
                'cta_label' => ['en' => 'See the full menu', 'ar' => 'القائمة كاملة'],
                'cta_link' => '/menu',
            ],
            [
                'type' => 'matches',
                'title' => ['en' => 'Match nights on the big screens', 'ar' => 'ليالي المباريات على الشاشات الكبيرة'],
                'subtitle' => ['en' => 'Every big game, live. Book early for derbies and finals.', 'ar' => 'كل المباريات الكبرى مباشرة. احجز مبكراً للديربي والنهائيات.'],
                'cta_label' => ['en' => 'All fixtures', 'ar' => 'جدول المباريات'],
                'cta_link' => '/match-nights',
                'image' => '/images/photos/match-crowd.webp',
            ],
            ['type' => 'offers', 'title' => ['en' => 'This week at Loyal', 'ar' => 'هذا الأسبوع في لويال']],
            [
                'type' => 'gallery',
                'title' => ['en' => 'Take a look inside', 'ar' => 'ألقِ نظرة من الداخل'],
                'cta_label' => ['en' => 'Open the gallery', 'ar' => 'افتح المعرض'],
                'cta_link' => '/gallery',
            ],
            ['type' => 'testimonials', 'title' => ['en' => 'From our guests', 'ar' => 'من ضيوفنا']],
            [
                'type' => 'visit',
                'title' => ['en' => 'Find us on Al Marami Street', 'ar' => 'تجدنا في شارع المرامي'],
                'subtitle' => ['en' => 'Free parking right outside.', 'ar' => 'مواقف مجانية أمام المدخل.'],
            ],
        ];

        foreach ($blocks as $i => $b) {
            $s = new Section(['page' => 'home', 'type' => $b['type'], 'cta_link' => $b['cta_link'] ?? null,
                'image' => $b['image'] ?? null, 'image_secondary' => $b['image_secondary'] ?? null, 'data' => $b['data'] ?? null, 'is_active' => true, 'sort_order' => $i + 1]);
            $s->fillTranslations($b)->save();
        }
    }
}
