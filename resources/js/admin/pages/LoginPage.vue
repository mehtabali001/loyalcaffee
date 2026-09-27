<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuth } from '../stores/auth';
import { errorMessage, fieldErrors } from '../api';

const auth = useAuth();
const router = useRouter();
const route = useRoute();
const email = ref('');
const password = ref('');
const error = ref('');
const busy = ref(false);

async function submit() {
    error.value = '';
    busy.value = true;
    try {
        await auth.login(email.value.trim(), password.value);
        router.replace(typeof route.query.next === 'string' ? route.query.next : '/');
    } catch (e) {
        const f = fieldErrors(e);
        error.value = f ? Object.values(f)[0] : e.response?.status === 429 ? 'Too many attempts. Wait a minute and try again.' : errorMessage(e);
    } finally {
        busy.value = false;
    }
}
</script>

<template>
    <div class="relative grid min-h-screen place-items-center bg-gradient-to-br from-sky-50 via-white to-sky-100/60 px-4 overflow-hidden">
        <!-- Ambient Decorative Sky Blue Glow -->
        <div class="pointer-events-none absolute -top-40 start-1/4 h-[32rem] w-[32rem] rounded-full bg-sky-200/40 blur-[130px]" />
        <div class="pointer-events-none absolute -bottom-40 end-1/4 h-[32rem] w-[32rem] rounded-full bg-sky-300/30 blur-[140px]" />

        <form class="relative w-full max-w-sm rounded-3xl border border-sky-100 bg-white/95 p-8 sm:p-10 shadow-2xl shadow-sky-950/10 backdrop-blur-md" @submit.prevent="submit">
            <div class="mb-6">
                <div class="flex items-center gap-2">
                    <span class="font-display text-3xl font-bold tracking-tight text-slate-900">Loyal</span>
                    <span class="rounded-full bg-sky-50 border border-sky-200 px-2.5 py-0.5 text-[0.7rem] font-bold text-sky-700">Admin CMS</span>
                </div>
                <p class="mt-1 text-xs text-slate-500">Sign in to manage cafe menu, reviews & reservations</p>
            </div>

            <label class="a-label" for="email">Email Address</label>
            <input id="email" v-model="email" type="email" autocomplete="username" class="a-input mb-4" placeholder="admin@loyalcafe.ae" required dir="ltr">

            <label class="a-label" for="password">Password</label>
            <input id="password" v-model="password" type="password" autocomplete="current-password" class="a-input" placeholder="••••••••" required dir="ltr">

            <p v-if="error" class="a-error mt-3" role="alert">{{ error }}</p>

            <button class="a-btn a-btn-primary mt-6 w-full shadow-lg shadow-sky-500/25" :disabled="busy">
                {{ busy ? 'Signing in…' : 'Sign in to Dashboard' }}
            </button>
        </form>
    </div>
</template>
