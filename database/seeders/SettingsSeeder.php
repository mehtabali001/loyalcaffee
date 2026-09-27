<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/** Business facts taken from the Digital Growth Proposal & Brand Design System. */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $hours = collect(range(0, 6))->map(fn ($d) => ['day' => $d, 'open' => '08:00', 'close' => '03:00', 'closed' => false])->all();

        $settings = [
            'general' => [
                'site_name' => ['en' => 'Loyal Restaurant & Cafe', 'ar' => 'مطعم وكافيه لويال'],
                'tagline' => ['en' => 'Stay a while.', 'ar' => 'خذ راحتك.'],
                'logo' => null,
                'logo_light' => null,
                'favicon' => null,
                'announcement' => ['enabled' => false, 'text' => ['en' => '', 'ar' => ''], 'link' => ''],
                // Brand Design System — Sky Blue Light & Gulf Luxury Palette
                'theme' => ['primary' => '#0284C7', 'accent' => '#D4A359', 'background' => '#F8FAFC', 'text' => '#1E293B', 'warm' => '#0EA5E9'],
            ],
            'contact' => [
                'phone' => '050 278 3288',
                'phone_intl' => '+971502783288',
                'whatsapp' => '971502783288',
                'whatsapp_message' => ['en' => 'Hi Loyal Cafe, I would like to ask about…', 'ar' => 'مرحباً كافيه لويال، أود الاستفسار عن…'],
                'email' => null,
                'address' => [
                    'street' => ['en' => 'Al Marami St 8', 'ar' => 'شارع المرامي 8'],
                    'area' => ['en' => 'Madinat Zayed, Abu Dhabi, UAE', 'ar' => 'مدينة زايد، أبوظبي، الإمارات'],
                ],
                'maps' => [
                    // Set exact pin from Google Maps → Share → Embed a map
                    'lat' => null,
                    'lng' => null,
                    'embed_url' => 'https://www.google.com/maps?q=Loyal+Restaurant+%26+Cafe+Al+Marami+St+Madinat+Zayed+Abu+Dhabi&output=embed',
                    'directions_url' => 'https://www.google.com/maps/dir/?api=1&destination=Loyal+Restaurant+%26+Cafe+Al+Marami+St+Madinat+Zayed+Abu+Dhabi',
                ],
                'socials' => ['instagram' => '', 'tiktok' => '', 'facebook' => '', 'google' => ''],
            ],
            'hours' => [
                'hours' => $hours,
                'hours_note' => ['en' => 'Kitchen and shisha served until closing.', 'ar' => 'المطبخ والشيشة متوفران حتى وقت الإغلاق.'],
                'amenities' => [
                    ['icon' => 'parking', 'label' => ['en' => 'Free parking', 'ar' => 'مواقف مجانية']],
                    ['icon' => 'screen', 'label' => ['en' => 'Big LED screens', 'ar' => 'شاشات LED كبيرة']],
                    ['icon' => 'shisha', 'label' => ['en' => 'Premium shisha', 'ar' => 'شيشة فاخرة']],
                    ['icon' => 'wifi', 'label' => ['en' => 'Free Wi-Fi', 'ar' => 'واي فاي مجاني']],
                    ['icon' => 'family', 'label' => ['en' => 'Family seating', 'ar' => 'جلسات عائلية']],
                    ['icon' => 'moon', 'label' => ['en' => 'Open until 3 AM', 'ar' => 'مفتوح حتى 3 فجراً']],
                ],
            ],
            'reputation' => [
                'rating' => ['value' => 4.0, 'count' => 110, 'url' => 'https://www.google.com/maps/search/?api=1&query=Loyal+Restaurant+%26+Cafe+Madinat+Zayed'],
            ],
            'reservation' => [
                'reservation' => [
                    'enabled' => true,
                    'min_guests' => 1,
                    'max_guests' => 30,
                    'slot_minutes' => 30,
                    'lead_minutes' => 30,
                    'notify_email' => null,
                ],
            ],
            'seo' => [
                'seo' => [], // per-page overrides; defaults live in App\Services\Seo
                'og_image' => null,
                'analytics' => ['ga4_id' => '', 'meta_pixel_id' => ''],
            ],
        ];

        foreach ($settings as $group => $pairs) {
            foreach ($pairs as $key => $value) {
                Setting::updateOrCreate(['key' => $key], ['group' => $group, 'value' => $value]);
            }
        }
    }
}
