<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    /** Whitelisted keys and their groups — anything else is rejected. */
    public const KEYS = [
        'site_name' => 'general', 'tagline' => 'general', 'logo' => 'general', 'logo_light' => 'general',
        'favicon' => 'general', 'announcement' => 'general', 'theme' => 'general',
        'phone' => 'contact', 'phone_intl' => 'contact', 'whatsapp' => 'contact', 'whatsapp_message' => 'contact', 'email' => 'contact',
        'address' => 'contact', 'maps' => 'contact', 'socials' => 'contact',
        'hours' => 'hours', 'hours_note' => 'hours', 'amenities' => 'hours',
        'rating' => 'reputation',
        'reservation' => 'reservation',
        'seo' => 'seo', 'og_image' => 'seo', 'analytics' => 'seo',
    ];

    public function index(): JsonResponse
    {
        $values = Setting::allAsArray();
        $out = [];
        foreach (self::KEYS as $key => $group) {
            $out[$key] = $values[$key] ?? null;
        }
        $out['_media'] = collect(['logo', 'logo_light', 'favicon', 'og_image'])
            ->mapWithKeys(fn ($k) => [$k => media_url($values[$k] ?? null)]);

        return response()->json($out);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'settings' => ['required', 'array'],
            'settings.hours' => ['sometimes', 'array', 'size:7'],
            'settings.hours.*.day' => ['required_with:settings.hours', 'integer', 'between:0,6'],
            'settings.hours.*.open' => ['required_with:settings.hours', 'date_format:H:i'],
            'settings.hours.*.close' => ['required_with:settings.hours', 'date_format:H:i'],
            'settings.hours.*.closed' => ['boolean'],
            'settings.email' => ['sometimes', 'nullable', 'email'],
            'settings.whatsapp' => ['sometimes', 'nullable', 'regex:/^\d{8,15}$/'],
            'settings.reservation.notify_email' => ['sometimes', 'nullable', 'email'],
            'settings.theme.*' => ['sometimes', 'nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.rating.value' => ['sometimes', 'numeric', 'between:0,5'],
            'settings.rating.count' => ['sometimes', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['settings'] as $key => $value) {
                if (isset(self::KEYS[$key])) {
                    Setting::put($key, $value, self::KEYS[$key]);
                }
            }
        });

        return $this->index();
    }
}
