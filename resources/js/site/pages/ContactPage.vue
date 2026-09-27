<script setup>
import { computed, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@/shared/Icon.vue';
import PageIntro from '../components/PageIntro.vue';
import HoursTable from '../components/HoursTable.vue';
import MapEmbed from '../components/MapEmbed.vue';
import OpenStatus from '../components/OpenStatus.vue';
import { useSite } from '../stores/site';
import { validationErrors } from '@/shared/http';

const site = useSite();
const { t } = useI18n();
const s = computed(() => site.settings);

const blank = () => ({ name: '', phone: '', email: '', subject: '', message: '', website: '' });
const form = reactive(blank());
const errors = ref({});
const sending = ref(false);
const failed = ref(false);
const sent = ref(false);

async function submit() {
    errors.value = {};
    failed.value = false;
    sending.value = true;
    try {
        await site.http().post('/contact', {
            ...form,
            phone: form.phone.trim() || null,
            email: form.email.trim() || null,
            subject: form.subject.trim() || null,
        });
        sent.value = true;
        Object.assign(form, blank());
    } catch (e) {
        const v = validationErrors(e);
        if (v) errors.value = v;
        else failed.value = true;
    } finally {
        sending.value = false;
    }
}
</script>

<template>
    <div>
        <PageIntro :title="t('contact.title')" :intro="t('contact.intro')">
            <OpenStatus class="mt-6" />
        </PageIntro>

        <div class="shell grid gap-14 pb-20 lg:grid-cols-2">
            <!-- Details -->
            <section class="space-y-10">
                <div class="grid gap-8 sm:grid-cols-2">
                    <div>
                        <h2 class="label text-charcoal/60">{{ t('contact.address') }}</h2>
                        <address class="not-italic text-lg">{{ s.address?.street }}<br>{{ s.address?.area }}</address>
                        <a v-if="s.maps?.directions_url" :href="s.maps.directions_url" target="_blank" rel="noopener" class="mt-3 inline-flex items-center gap-2 text-emerald underline-offset-4 hover:underline">
                            <Icon name="pin" :size="18" /> {{ t('cta.directions') }}
                        </a>
                        <p class="mt-3 flex items-center gap-2 text-sm text-charcoal/70"><Icon name="parking" :size="18" class="text-emerald" /> {{ t('contact.parking') }}</p>
                    </div>
                    <div>
                        <h2 class="label text-charcoal/60">{{ t('contact.phone_label') }}</h2>
                        <a v-if="site.telLink" :href="site.telLink" class="text-lg hover:text-emerald" dir="ltr">{{ s.phone }}</a>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <a v-if="site.whatsappLink()" :href="site.whatsappLink()" target="_blank" rel="noopener" class="btn btn-emerald"><Icon name="whatsapp" :size="18" /> {{ t('cta.whatsapp') }}</a>
                            <a v-if="site.telLink" :href="site.telLink" class="btn btn-ghost-dark"><Icon name="phone" :size="18" /> {{ t('cta.call') }}</a>
                        </div>
                        <a v-if="s.email" :href="`mailto:${s.email}`" class="mt-4 block text-sm text-charcoal/70 hover:text-emerald" dir="ltr">{{ s.email }}</a>
                    </div>
                </div>
                <div class="max-w-sm">
                    <h2 class="mb-3 font-display text-xl">{{ t('home.hours_title') }}</h2>
                    <HoursTable />
                </div>
            </section>

            <!-- Form -->
            <section>
                <h2 class="t-h3 mb-6">{{ t('contact.form_title') }}</h2>
                <p v-if="sent" class="mb-6 flex items-center gap-2 rounded-lg bg-emerald/10 px-4 py-3 text-emerald" role="status">
                    <Icon name="check" :size="18" /> {{ t('contact.sent') }}
                </p>
                <form class="grid gap-5 sm:grid-cols-2" novalidate @submit.prevent="submit">
                    <div class="sm:col-span-2">
                        <label class="label" for="c-name">{{ t('contact.name') }}</label>
                        <input id="c-name" v-model="form.name" class="field" autocomplete="name" required maxlength="80">
                        <p v-if="errors.name" class="mt-1 text-sm text-terracotta">{{ errors.name }}</p>
                    </div>
                    <div>
                        <label class="label" for="c-phone">{{ t('contact.phone') }}</label>
                        <input id="c-phone" v-model="form.phone" class="field" type="tel" dir="ltr" autocomplete="tel">
                        <p v-if="errors.phone" class="mt-1 text-sm text-terracotta">{{ errors.phone }}</p>
                    </div>
                    <div>
                        <label class="label" for="c-email">{{ t('contact.email') }}</label>
                        <input id="c-email" v-model="form.email" class="field" type="email" dir="ltr" autocomplete="email">
                        <p v-if="errors.email" class="mt-1 text-sm text-terracotta">{{ errors.email }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label" for="c-subject">{{ t('contact.subject') }}</label>
                        <input id="c-subject" v-model="form.subject" class="field" maxlength="120">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label" for="c-message">{{ t('contact.message') }}</label>
                        <textarea id="c-message" v-model="form.message" class="field min-h-36" required maxlength="2000" />
                        <p v-if="errors.message" class="mt-1 text-sm text-terracotta">{{ errors.message }}</p>
                    </div>
                    <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
                        <input v-model="form.website" tabindex="-1" autocomplete="off" aria-label="Website">
                    </div>
                    <div class="sm:col-span-2">
                        <p v-if="failed" class="mb-4 text-sm text-terracotta" role="alert">{{ t('errors.generic') }}</p>
                        <button type="submit" class="btn btn-gold" :disabled="sending">{{ sending ? t('contact.sending') : t('contact.submit') }}</button>
                    </div>
                </form>
            </section>
        </div>

        <MapEmbed class="h-[26rem]" />
    </div>
</template>
