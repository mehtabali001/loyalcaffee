<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

/**
 * SAMPLE fixtures generated relative to today so the page is never empty in development.
 * Replace with real fixtures in Admin → Match nights before launch.
 */
class EventsSeeder extends Seeder
{
    public function run(): void
    {
        Event::query()->delete();

        $rows = [
            [2, '21:30', 'UEFA Champions League', ['League phase', 'مرحلة الدوري'], null, null, null, null, false,
                ['Champions League night', 'ليلة دوري أبطال أوروبا'], null],
            [4, '19:00', 'UAE Pro League', ['Matchday', 'الجولة'], ['Al Ain', 'العين'], ['Al Jazira', 'الجزيرة'], 'ae', 'ae', false,
                ['Al Ain vs Al Jazira', 'العين ضد الجزيرة'], ['Shisha + 2 fresh juices, AED 99', 'شيشة + عصيرين طازجين بـ 99 درهم']],
            [9, '22:00', 'La Liga', ['El Clásico', 'الكلاسيكو'], ['Real Madrid', 'ريال مدريد'], ['Barcelona', 'برشلونة'], 'es', 'es', true,
                ['El Clásico on the big screens', 'الكلاسيكو على الشاشات الكبيرة'], ['Match Night Sharing Box, AED 120 for four', 'بوكس المباراة لأربعة أشخاص بـ 120 درهم']],
        ];

        foreach ($rows as [$days, $time, $comp, $stage, $home, $away, $hf, $af, $big, $title, $offer]) {
            (new Event([
                'kind' => 'match', 'competition' => $comp, 'home_flag' => $hf, 'away_flag' => $af,
                'starts_at' => now()->addDays($days)->setTimeFromTimeString($time),
                'is_big_match' => $big, 'reservations_open' => true, 'capacity' => $big ? 80 : null, 'is_active' => true,
            ]))->fillTranslations([
                'title' => ['en' => $title[0], 'ar' => $title[1]],
                'stage' => ['en' => $stage[0], 'ar' => $stage[1]],
                'home_team' => $home ? ['en' => $home[0], 'ar' => $home[1]] : null,
                'away_team' => $away ? ['en' => $away[0], 'ar' => $away[1]] : null,
                'offer' => $offer ? ['en' => $offer[0], 'ar' => $offer[1]] : null,
            ])->save();
        }
    }
}
