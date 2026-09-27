<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use App\Support\ContentVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * UI strings. The admin sends the full set of overrides; blank values fall back to the
 * defaults bundled with the frontend (resources/js/site/i18n/*.json).
 */
class TranslationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Translation::query()->orderBy('key')->get(['key', 'value'])->mapWithKeys(fn ($t) => [$t->key => $t->value]));
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'translations' => ['required', 'array'],
            'translations.*' => ['array'],
            'translations.*.*' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['translations'] as $key => $value) {
                if (! preg_match('/^[a-z0-9_]+(\.[a-z0-9_]+)+$/i', $key)) {
                    continue;
                }
                $clean = array_filter(array_intersect_key($value, array_flip(config('loyal.locales'))), fn ($v) => $v !== null && $v !== '');
                $clean ? Translation::updateOrCreate(['key' => $key], ['value' => $clean]) : Translation::where('key', $key)->delete();
            }
        });
        ContentVersion::bump();

        return $this->index();
    }
}
