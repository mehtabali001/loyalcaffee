<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Section */
class SectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->t('title'),
            'subtitle' => $this->t('subtitle'),
            'body' => $this->t('body'),
            'cta_label' => $this->t('cta_label'),
            'cta_link' => $this->cta_link,
            'image' => media_url($this->image),
            'image_secondary' => media_url($this->image_secondary),
            'video' => media_url($this->video),
            'data' => self::localiseData($this->data ?? []),
        ];
    }

    /** Section extras can contain nested {en, ar} blobs (e.g. day/night panels) — resolve them. */
    public static function localiseData(mixed $data): mixed
    {
        if (! is_array($data)) {
            return $data;
        }
        $keys = array_keys($data);
        $locales = config('loyal.locales');
        if ($keys && ! array_diff($keys, $locales)) {
            return tr($data);
        }
        foreach ($data as $k => $v) {
            if (is_string($v) && preg_match('/(^|_)(image|photo|bg)$/', (string) $k)) {
                $data[$k] = media_url($v);
            } else {
                $data[$k] = self::localiseData($v);
            }
        }

        return $data;
    }
}
