<script setup>
import { reactive, ref } from 'vue';
import { api, errorMessage, fieldErrors } from '../api';
import { useAuth } from '../stores/auth';
import { toast } from '../toast';

const auth = useAuth();
const form = reactive({ name: auth.user?.name || '', email: auth.user?.email || '', current_password: '', password: '', password_confirmation: '' });
const errors = ref({});
const saving = ref(false);

async function save() {
    saving.value = true;
    errors.value = {};
    const body = { name: form.name, email: form.email };
    if (form.password) Object.assign(body, { current_password: form.current_password, password: form.password, password_confirmation: form.password_confirmation });
    try {
        auth.user = (await api.put('/me', body)).data;
        Object.assign(form, { current_password: '', password: '', password_confirmation: '' });
        toast('Account updated');
    } catch (e) {
        errors.value = fieldErrors(e) || {};
        if (!fieldErrors(e)) toast(errorMessage(e), 'error');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div class="max-w-xl">
        <h1 class="mb-6 font-display text-3xl">My account</h1>
        <form class="a-card space-y-4 p-6" @submit.prevent="save">
            <div><label class="a-label">Name</label><input v-model="form.name" class="a-input" required><p v-if="errors.name" class="a-error">{{ errors.name }}</p></div>
            <div><label class="a-label">Email</label><input v-model="form.email" type="email" class="a-input" dir="ltr" required><p v-if="errors.email" class="a-error">{{ errors.email }}</p></div>
            <fieldset class="space-y-4 rounded-lg border border-stone-200 p-4">
                <legend class="px-2 text-sm font-semibold">Change password</legend>
                <div><label class="a-label">Current password</label><input v-model="form.current_password" type="password" class="a-input" autocomplete="current-password" dir="ltr"><p v-if="errors.current_password" class="a-error">{{ errors.current_password }}</p></div>
                <div><label class="a-label">New password</label><input v-model="form.password" type="password" class="a-input" autocomplete="new-password" dir="ltr"><p v-if="errors.password" class="a-error">{{ errors.password }}</p></div>
                <div><label class="a-label">Repeat new password</label><input v-model="form.password_confirmation" type="password" class="a-input" autocomplete="new-password" dir="ltr"></div>
                <p class="a-hint">Changing your password signs out your other devices.</p>
            </fieldset>
            <button class="a-btn a-btn-primary" :disabled="saving">{{ saving ? 'Saving…' : 'Save' }}</button>
        </form>
    </div>
</template>
