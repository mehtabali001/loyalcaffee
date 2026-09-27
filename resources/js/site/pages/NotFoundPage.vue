<script setup>
import { useI18n } from 'vue-i18n';
import { useSite } from '../stores/site';

const site = useSite();
const { t } = useI18n();
</script>

<template>
    <section class="relative flex min-h-[80vh] items-center overflow-hidden bg-emerald-night text-ivory">
        <div class="lattice absolute inset-0 opacity-[0.06]" />
        <div class="shell relative py-32">
            <p class="font-display text-7xl text-gold/80">404</p>
            <h1 class="t-h2 mt-6">{{ t('errors.not_found_title') }}</h1>
            <p class="t-lead mt-4 max-w-xl text-ivory/75">{{ t('errors.not_found_body') }}</p>
            <div class="mt-10 flex flex-wrap gap-3">
                <RouterLink :to="site.lp('/')" class="btn btn-gold">{{ t('errors.back_home') }}</RouterLink>
                <RouterLink :to="site.lp('/match-nights')" class="btn btn-ghost-light">{{ t('nav.matches') }}</RouterLink>
            </div>
        </div>
    </section>
</template>
