<?php

namespace Database\Seeders;

use App\Models\Offer;
use Illuminate\Database\Seeder;

/** Campaigns from the Social Media Strategy §3.4 — confirm with the owner before activating in production. */
class OffersSeeder extends Seeder
{
    public function run(): void
    {
        Offer::query()->delete();
        $offers = [
            [['Golden hour', 'الساعة الذهبية'], ['Specialty coffee and fresh juice offers every day between 5 and 6 PM.', 'عروض على القهوة المختصة والعصائر الطازجة يومياً بين 5 و6 مساءً.'], ['5–6 PM daily', '5–6 مساءً يومياً'], '/menu', 'iced-latte'],
            [['Bring the table', 'اجمع الشلة'], ['Book for four or more and dessert is on us.', 'احجز لأربعة أشخاص أو أكثر والحلى علينا.'], ['Groups of 4+', 'مجموعات 4+'], '/reserve', 'mezze'],
            [['First cup\'s on us', 'أول كوب علينا'], ['First visit? Follow us on Instagram, show it at the counter and your coffee or juice is free.', 'زيارتك الأولى؟ تابعنا على إنستغرام واعرضها عند الكاشير واحصل على قهوة أو عصير مجاناً.'], ['New guests', 'للضيوف الجدد'], null, 'latte-art'],
        ];
        foreach ($offers as $i => [$t, $d, $b, $link, $photo]) {
            (new Offer(['image' => "/images/photos/$photo.webp", 'cta_link' => $link, 'is_active' => true, 'sort_order' => $i + 1]))->fillTranslations([
                'title' => ['en' => $t[0], 'ar' => $t[1]],
                'description' => ['en' => $d[0], 'ar' => $d[1]],
                'badge' => ['en' => $b[0], 'ar' => $b[1]],
            ])->save();
        }
    }
}
