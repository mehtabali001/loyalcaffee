<?php

namespace App\Support;

/**
 * Lightweight translatable attributes stored as JSON: {"en": "...", "ar": "..."}.
 * Declare `protected array $translatable = ['name', ...]` on the model.
 */
trait HasTranslations
{
    public function initializeHasTranslations(): void
    {
        $this->mergeCasts(array_fill_keys($this->translatable ?? [], UnicodeJson::class));
    }

    public function getTranslatable(): array
    {
        return $this->translatable ?? [];
    }

    public function t(string $attribute, ?string $locale = null): mixed
    {
        return Locale::pick($this->getAttribute($attribute), $locale);
    }

    /** Fill translatable attributes from an admin payload, normalising locale keys. */
    public function fillTranslations(array $payload): static
    {
        foreach ($this->getTranslatable() as $attr) {
            if (array_key_exists($attr, $payload)) {
                $this->setAttribute($attr, Locale::normalise($payload[$attr]));
            }
        }

        return $this;
    }
}
