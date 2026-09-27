import { createI18n } from 'vue-i18n';
import en from './en.json';
import ar from './ar.json';
import { deepMerge } from '@/shared/merge';

export const bundled = { en, ar };

export function makeI18n(locale, overrides = {}) {
    return createI18n({
        legacy: false,
        locale,
        fallbackLocale: 'en',
        messages: { [locale]: deepMerge(bundled[locale] || en, overrides), ...(locale !== 'en' ? { en } : {}) },
        missingWarn: false,
        fallbackWarn: false,
    });
}
