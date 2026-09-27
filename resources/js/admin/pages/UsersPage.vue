<script setup>
import { ref } from 'vue';
import { api, errorMessage, fieldErrors } from '../api';
import { useAuth } from '../stores/auth';
import { toast } from '../toast';
import { fmtDateTime } from '../format';

const auth = useAuth();
const users = ref([]);
const editing = ref(null);
const errors = ref({});

const load = async () => (users.value = (await api.get('/users')).data.data);
load().catch((e) => toast(errorMessage(e), 'error'));

const startNew = () => { errors.value = {}; editing.value = { name: '', email: '', role: 'editor', password: '' }; };
const startEdit = (u) => { errors.value = {}; editing.value = { ...u, password: '' }; };

async function save() {
    errors.value = {};
    const u = editing.value;
    const body = { name: u.name, email: u.email, role: u.role, password: u.password || null };
    try {
        u.id ? await api.put(`/users/${u.id}`, body) : await api.post('/users', body);
        toast('Account saved');
        editing.value = null;
        load();
    } catch (e) {
        errors.value = fieldErrors(e) || {};
        if (!fieldErrors(e)) toast(errorMessage(e), 'error');
    }
}
async function remove(u) {
    if (!confirm(`Remove ${u.name}'s access?`)) return;
    try {
        await api.delete(`/users/${u.id}`);
        load();
    } catch (e) {
        toast(errorMessage(e), 'error');
    }
}
</script>

<template>
    <div class="max-w-4xl">
        <div class="mb-6 flex items-end justify-between">
            <div>
                <h1 class="font-display text-3xl">Staff accounts</h1>
                <p class="text-sm text-stone-500">Editors manage content and bookings. Only owners manage accounts.</p>
            </div>
            <button type="button" class="a-btn a-btn-primary" @click="startNew">Add person</button>
        </div>

        <form v-if="editing" class="a-card mb-6 grid gap-4 p-5 md:grid-cols-2" @submit.prevent="save">
            <div><label class="a-label">Name</label><input v-model="editing.name" class="a-input" required><p v-if="errors.name" class="a-error">{{ errors.name }}</p></div>
            <div><label class="a-label">Email</label><input v-model="editing.email" type="email" class="a-input" dir="ltr" required><p v-if="errors.email" class="a-error">{{ errors.email }}</p></div>
            <div>
                <label class="a-label">Role</label>
                <select v-model="editing.role" class="a-input"><option value="editor">Editor</option><option value="owner">Owner</option></select>
            </div>
            <div>
                <label class="a-label">{{ editing.id ? 'New password (optional)' : 'Password' }}</label>
                <input v-model="editing.password" type="password" class="a-input" autocomplete="new-password" :required="!editing.id" dir="ltr">
                <p class="a-hint">At least 10 characters with upper and lower case letters and a number.</p>
                <p v-if="errors.password" class="a-error">{{ errors.password }}</p>
            </div>
            <div class="flex gap-2 md:col-span-2">
                <button class="a-btn a-btn-primary">Save</button>
                <button type="button" class="a-btn a-btn-ghost" @click="editing = null">Cancel</button>
            </div>
        </form>

        <div class="a-card overflow-x-auto">
            <table class="a-table">
                <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Last sign-in</th><th /></tr></thead>
                <tbody>
                    <tr v-for="u in users" :key="u.id">
                        <td class="font-medium">{{ u.name }}<span v-if="u.id === auth.user?.id" class="text-xs text-stone-400"> (you)</span></td>
                        <td dir="ltr">{{ u.email }}</td>
                        <td class="capitalize">{{ u.role }}</td>
                        <td class="text-xs text-stone-500">{{ u.last_login_at ? fmtDateTime(u.last_login_at) : 'Never' }}</td>
                        <td class="whitespace-nowrap text-end">
                            <button type="button" class="a-btn a-btn-ghost a-btn-sm" @click="startEdit(u)">Edit</button>
                            <button v-if="u.id !== auth.user?.id" type="button" class="a-btn a-btn-danger a-btn-sm ms-1" @click="remove(u)">Remove</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
