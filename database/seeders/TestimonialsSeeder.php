<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialsSeeder extends Seeder
{
    public function run(): void
    {
        Testimonial::query()->delete();

        $reviews = [
            [
                'author' => 'طارق المنصوري / Tariq Al-Mansoori',
                'avatar' => '/images/reviews/tariq.jpg',
                'location' => ['en' => 'Abu Dhabi, UAE', 'ar' => 'أبوظبي، الإمارات'],
                'role' => ['en' => 'Specialty Coffee Connoisseur', 'ar' => 'متذوق قهوة مختصة'],
                'rating' => 5,
                'source' => 'google',
                'reviewed_on' => now()->subDays(3)->toDateString(),
                'content' => [
                    'ar' => 'أفضل فلات وايت وسبانيش لاتيه في المنطقة بدون منازع! الجلسات الصباحية هادئة جداً مع إضاءة طبيعية ساحرة، وتصميم الكافيه بدرجات السماوي والأوف وايت مريح للنفس. في المساء الشيشة ممتازة والخدمة سريعة وراقية.',
                    'en' => 'Undisputed best Spanish Latte and Flat White in the area! The morning atmosphere with natural sunlight and the serene sky-blue aesthetic is pure luxury. In the evening, the terrace shisha and prompt service make it my go-to lounge.',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'author' => 'ريم العتيبي / Reem Al-Otaibi',
                'avatar' => '/images/reviews/reem.jpg',
                'location' => ['en' => 'Riyadh, KSA', 'ar' => 'الرياض، السعودية'],
                'role' => ['en' => 'Design & Food Blogger', 'ar' => 'مهتمة بالتصميم والتذوق'],
                'rating' => 5,
                'source' => 'google',
                'reviewed_on' => now()->subDays(6)->toDateString(),
                'content' => [
                    'ar' => 'التفاصيل والضيافة الخليجية هنا على أعلى مستوى. تشيز كيك الفستق مع شيك الأفوكادو بالكريمة والمكسرات خيالي! الإطلالة والديكورات الحديثة بألوانها الفاتحة تعطي انطباع راقي جداً مناسب لجمعات العائلة والصديقات.',
                    'en' => 'Top-tier Gulf hospitality and attention to detail. The Pistachio Cheesecake paired with the signature Avocado Shake is extraordinary! The bright, elegant interior feels like a 5-star boutique resort lounge.',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'author' => 'فيصل الكواري / Faisal Al-Kuwari',
                'avatar' => '/images/reviews/faisal.jpg',
                'location' => ['en' => 'Doha, Qatar', 'ar' => 'الدوحة، قطر'],
                'role' => ['en' => 'Business Traveler & Regular', 'ar' => 'زائر دائم ورائد أعمال'],
                'rating' => 5,
                'source' => 'google',
                'reviewed_on' => now()->subDays(10)->toDateString(),
                'content' => [
                    'ar' => 'أجواء ليالي المباريات لا توصف! الشاشات العملاقة وتوزيع الصوت الاحترافي مع جلسات الماجلس الخارجية تجربة استثنائية. حجز الطاولة أونلاين كان سلس جداً والضيافة من لحظة الدخول كانت مميزة.',
                    'en' => 'Match nights here are unbeatable! Massive high-definition screens, crystal-clear acoustics, and plush outdoor terrace seating. The online table reservation was seamless and the hospitality was exceptional from arrival.',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'author' => 'مريم آل نهيان / Maryam Al-Nahyan',
                'avatar' => '/images/reviews/maryam.jpg',
                'location' => ['en' => 'Dubai, UAE', 'ar' => 'دبي، الإمارات'],
                'role' => ['en' => 'Lifestyle & Lifestyle Reviewer', 'ar' => 'ناقدة أسلوب حياة وضيافة'],
                'rating' => 5,
                'source' => 'google',
                'reviewed_on' => now()->subDays(14)->toDateString(),
                'content' => [
                    'ar' => 'المكان متكامل: قائمة أطباق طازجة ومبتكرة، وشاي الكرك مع التوست المحمص بالزعتر والحلوم لا يفوت. أحببت الجمع المتقن بين الطابع الخليجي الأصيل واللمسات العصرية باللون السماوي والأبيض.',
                    'en' => 'An absolute gem: fresh, creative dishes, artisan mocktails, and the Karak Chai with Halloumi Zaatar toast is a must. Love the blend of authentic Khaleeji warmth with modern sky-blue and marble aesthetics.',
                ],
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($reviews as $r) {
            $t = new Testimonial([
                'author' => $r['author'],
                'avatar' => $r['avatar'],
                'rating' => $r['rating'],
                'source' => $r['source'],
                'reviewed_on' => $r['reviewed_on'],
                'is_active' => $r['is_active'],
                'sort_order' => $r['sort_order'],
            ]);
            $t->fillTranslations([
                'content' => $r['content'],
                'location' => $r['location'],
                'role' => $r['role'],
            ])->save();
        }
    }
}
