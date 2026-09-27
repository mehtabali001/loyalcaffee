<script setup>
import Icon from '@/shared/Icon.vue';

defineProps({ section: { type: Object, required: true } });
</script>

<template>
    <section aria-labelledby="daynight-title">
        <h2 id="daynight-title" class="sr-only">{{ section.title }}</h2>
        <div class="grid md:grid-cols-2">
            <article
                v-for="(key, k) in ['day', 'night']" :key="key"
                class="group relative isolate flex min-h-[36rem] flex-col justify-end overflow-hidden p-8 sm:p-12 lg:min-h-[42rem]"
                :class="key === 'day' ? 'bg-sky-50 text-slate-900' : 'bg-slate-950 text-white'"
            >
                <template v-if="section.data?.[key]?.image">
                    <div class="absolute inset-0 -z-20 overflow-hidden">
                        <div class="h-full w-full transition-transform duration-[1.6s] ease-lounge group-hover:scale-105">
                            <img v-parallax="0.1" :src="section.data[key].image" alt="" loading="lazy" class="h-full w-full object-cover">
                        </div>
                    </div>
                    <div
                        class="absolute inset-0 -z-10"
                        :class="key === 'day'
                            ? 'bg-gradient-to-t from-sky-50 from-15% via-sky-50/85 via-45% to-transparent'
                            : 'bg-gradient-to-t from-slate-950 from-15% via-slate-950/80 via-45% to-slate-950/20'"
                    />
                </template>
                <div v-reveal="{ delay: k * 150 }">
                    <p class="mb-4 inline-flex items-center gap-2 rounded-full border px-3.5 py-1 text-xs font-bold uppercase tracking-wider backdrop-blur" :class="key === 'day' ? 'border-sky-200 bg-white/80 text-sky-700 shadow-sm' : 'border-sky-400/30 bg-slate-900/60 text-sky-300'">
                        <Icon :name="key === 'day' ? 'sun' : 'moon'" :size="16" />
                        {{ section.data?.[key]?.time }}
                    </p>
                    <h3 class="t-h2 font-bold">{{ section.data?.[key]?.title }}</h3>
                    <span class="gold-rule mt-5 transition-all duration-700 ease-lounge group-hover:w-32" />
                    <p class="t-lead mt-5 max-w-md font-normal" :class="key === 'day' ? 'text-slate-600' : 'text-slate-300'">{{ section.data?.[key]?.body }}</p>
                </div>
            </article>
        </div>
    </section>
</template>
