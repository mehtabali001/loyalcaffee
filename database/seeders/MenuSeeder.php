<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

/**
 * Starter menu. Prices are placeholders in AED — replace them in Admin → Menu
 * with the cafe's real price list before launch.
 */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        MenuItem::query()->delete();
        MenuCategory::query()->delete();

        $menu = [
            ['shisha', 'shisha', ['Shisha', 'الشيشة'], ['Hand-packed to order, fresh coals all evening.', 'تُحضّر عند الطلب مع تبديل الفحم طوال السهرة.'], [
                [['Double Apple', 'تفاحتين'], ['The classic, aniseed-sweet and smooth.', 'النكهة الكلاسيكية بلمسة يانسون ناعمة.'], 75, ['signature', 'popular'], true, '/images/menu/double-apple.jpg'],
                [['Grape Mint', 'عنب ونعناع'], null, 75, ['popular'], false, '/images/photos/shisha-green.webp'],
                [['Lemon Mint', 'ليمون ونعناع'], null, 75, [], false, null],
                [['Blueberry', 'توت أزرق'], null, 75, [], false, null],
                [['Love 66', 'لوف 66'], ['Passion fruit, melon and mint.', 'باشن فروت وشمام ونعناع.'], 80, ['popular'], false, null],
                [['Loyal House Blend', 'خلطة لويال الخاصة'], ['Ask your shisha host for tonight\'s mix.', 'اسأل مسؤول الشيشة عن خلطة الليلة.'], 90, ['signature', 'new'], true, '/images/photos/shisha-green.webp'],
            ]],
            ['coffee', 'coffee', ['Coffee', 'القهوة'], ['Specialty beans, pulled and poured with care.', 'حبوب مختصة تُحضّر بعناية.'], [
                [['Espresso', 'إسبريسو'], null, 14, [], false, null],
                [['Americano', 'أمريكانو'], null, 16, [], false, null],
                [['Cappuccino', 'كابتشينو'], null, 20, [], false, null],
                [['Flat White', 'فلات وايت'], null, 22, ['popular'], false, '/images/photos/latte-art.webp'],
                [['Spanish Latte', 'سبانيش لاتيه'], ['Sweet condensed milk, double shot.', 'حليب مكثف محلى مع جرعتين إسبريسو.'], 24, ['signature', 'popular'], true, '/images/menu/spanish-latte.jpg'],
                [['V60 Pour Over', 'في 60'], ['Single origin, ask for today\'s bean.', 'محصول أحادي المصدر، اسأل عن حبوب اليوم.'], 28, ['popular'], true, '/images/photos/coffee-sunlight.webp'],
                [['Turkish Coffee', 'قهوة تركية'], null, 15, [], false, '/images/photos/gahwa-pour.webp'],
                [['Karak Chai', 'شاي كرك'], null, 8, ['popular'], false, '/images/photos/dates-gahwa.webp'],
            ]],
            ['avocado-juices', 'juice', ['Avocado & Fresh Juice', 'الأفوكادو والعصائر الطازجة'], ['Pressed and blended to order.', 'تُعصر وتُخلط عند الطلب.'], [
                [['Avocado Shake', 'شيك أفوكادو'], ['With honey, nuts and a scoop of cream.', 'مع العسل والمكسرات والقشطة.'], 26, ['signature', 'popular'], true, '/images/menu/avocado-shake.jpg'],
                [['Loyal Avocado Cocktail', 'كوكتيل أفوكادو لويال'], ['Layers of avocado, mango and strawberry.', 'طبقات من الأفوكادو والمانجو والفراولة.'], 30, ['signature'], true, '/images/photos/specialty-drinks.jpg'],
                [['Fresh Orange', 'برتقال طازج'], null, 18, [], false, null],
                [['Lemon Mint', 'ليمون ونعناع'], null, 18, ['popular'], false, null],
                [['Mango', 'مانجو'], null, 20, [], false, null],
                [['Strawberry', 'فراولة'], null, 20, [], false, null],
            ]],
            ['mocktails', 'glass', ['Mojitos & Mocktails', 'موهيتو وموكتيل'], null, [
                [['Classic Mojito', 'موهيتو كلاسيك'], null, 24, [], false, '/images/photos/iced-latte.webp'],
                [['Passion Fruit Mojito', 'موهيتو باشن فروت'], null, 26, ['popular'], false, null],
                [['Blue Lagoon', 'بلو لاجون'], null, 26, [], false, null],
                [['Iced Hibiscus', 'كركديه مثلج'], null, 20, ['new'], false, null],
            ]],
            ['breakfast', 'sun', ['Breakfast', 'الفطور'], ['Served all day.', 'يُقدّم طوال اليوم.'], [
                [['Shakshuka', 'شكشوكة'], ['Eggs baked in spiced tomato, warm bread.', 'بيض مطهو في صلصة طماطم متبلة مع خبز دافئ.'], 32, ['popular'], false, '/images/photos/mezze.webp'],
                [['Foul Medames', 'فول مدمس'], null, 25, ['vegetarian'], false, null],
                [['Halloumi & Zaatar Toast', 'توست حلوم وزعتر'], null, 30, ['vegetarian'], false, null],
                [['Avocado Toast', 'توست أفوكادو'], ['Sourdough, poached egg, chilli flakes.', 'خبز العجين المخمر مع بيض مسلوق ورقائق الفلفل.'], 38, ['signature', 'new'], true, '/images/menu/avocado-toast.jpg'],
            ]],
            ['food', 'plate', ['Food', 'الأطباق'], null, [
                [['Loyal Club Sandwich', 'كلوب ساندويتش لويال'], null, 42, ['popular'], false, null],
                [['Beef Burger', 'برجر لحم'], ['Cheddar, caramelised onion, fries.', 'شيدر وبصل مكرمل مع البطاطا.'], 48, [], false, null],
                [['Grilled Chicken Platter', 'طبق دجاج مشوي'], null, 52, [], false, null],
                [['Fattoush', 'فتوش'], null, 26, ['vegan'], false, null],
                [['Match Night Sharing Box', 'بوكس المباراة للمشاركة'], ['Wings, sliders, fries and dips for four.', 'أجنحة وسلايدرز وبطاطا وصلصات لأربعة أشخاص.'], 120, ['signature', 'sharing'], true, '/images/menu/sharing-box.jpg'],
            ]],
            ['desserts', 'dessert', ['Desserts', 'الحلويات'], null, [
                [['Kunafa', 'كنافة'], ['Warm golden spun pastry with fresh cream and pistachios.', 'كنافة مقرمشة بالقشطة الطازجة والفستق.'], 32, ['popular'], false, '/images/photos/specialty-drinks.jpg'],
                [['Pistachio Cheesecake', 'تشيز كيك بالفستق'], ['Rich pistachio cream, crumbled crust and gold leaf.', 'تشيز كيك غني بكريمة الفستق ولمسات الذهب.'], 34, ['signature', 'popular'], true, '/images/menu/pistachio-cheesecake.jpg'],
                [['Lotus Cake', 'كيكة لوتس'], null, 30, [], false, null],
            ]],
        ];

        foreach ($menu as $ci => [$slug, $icon, $name, $desc, $items]) {
            $cat = new MenuCategory(['slug' => $slug, 'icon' => $icon, 'is_active' => true, 'sort_order' => $ci + 1]);
            $cat->fillTranslations([
                'name' => ['en' => $name[0], 'ar' => $name[1]],
                'description' => $desc ? ['en' => $desc[0], 'ar' => $desc[1]] : null,
            ])->save();

            foreach ($items as $ii => $itemData) {
                $n = $itemData[0];
                $d = $itemData[1];
                $price = $itemData[2];
                $tags = $itemData[3] ?? [];
                $featured = $itemData[4] ?? false;
                $img = $itemData[5] ?? null;

                $item = new MenuItem([
                    'menu_category_id' => $cat->id,
                    'price' => $price,
                    'tags' => $tags,
                    'image' => $img,
                    'is_featured' => $featured,
                    'is_available' => true,
                    'is_active' => true,
                    'sort_order' => $ii + 1,
                ]);
                $item->fillTranslations([
                    'name' => ['en' => $n[0], 'ar' => $n[1]],
                    'description' => $d ? ['en' => $d[0], 'ar' => $d[1]] : null,
                ])->save();
            }
        }
    }
}
