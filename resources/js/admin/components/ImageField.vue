<script setup>
import { computed, ref } from 'vue';
import { upload, errorMessage } from '../api';
import { toast } from '../toast';

const props = defineProps({ modelValue: String, previewUrl: String, folder: { type: String, default: 'uploads' } });
const emit = defineEmits(['update:modelValue']);

const busy = ref(0);
const fresh = ref(null);
const input = ref(null);
const dragging = ref(false);

const src = computed(() => {
    if (fresh.value) return fresh.value;
    const p = props.modelValue;
    if (!p) return null;
    if (props.previewUrl) return props.previewUrl;
    return /^(https?:|\/)/.test(p) ? p : `/storage/${p}`;
});

async function handle(files) {
    if (!files?.length) return;
    busy.value = 1;
    try {
        const [f] = await upload([files[0]], props.folder, (p) => (busy.value = Math.max(1, p)));
        fresh.value = f.url;
        emit('update:modelValue', f.path);
    } catch (e) {
        toast(errorMessage(e), 'error');
    } finally {
        busy.value = 0;
        if (input.value) input.value.value = '';
    }
}
function clear() {
    fresh.value = null;
    emit('update:modelValue', null);
}
</script>

<template>
    <div
        class="flex items-center gap-4 rounded-lg border border-dashed p-3 transition"
        :class="dragging ? 'border-emerald bg-emerald/5' : 'border-stone-300'"
        @dragover.prevent="dragging = true" @dragleave="dragging = false"
        @drop.prevent="dragging = false; handle($event.dataTransfer.files)"
    >
        <div class="grid h-20 w-28 shrink-0 place-items-center overflow-hidden rounded-md bg-stone-100">
            <img v-if="src" :src="src" alt="" class="h-full w-full object-cover">
            <span v-else class="text-xs text-stone-400">No image</span>
        </div>
        <div class="min-w-0 flex-1 text-sm">
            <p v-if="busy" class="text-emerald">Uploading… {{ busy }}%</p>
            <p v-else class="text-stone-500">Drop an image here or</p>
            <div class="mt-2 flex gap-2">
                <button type="button" class="a-btn a-btn-ghost a-btn-sm" :disabled="!!busy" @click="input.click()">Choose file</button>
                <button v-if="modelValue" type="button" class="a-btn a-btn-danger a-btn-sm" @click="clear">Remove</button>
            </div>
            <p class="a-hint">JPG, PNG or WebP. Resized and converted to WebP automatically.</p>
        </div>
        <input ref="input" type="file" accept="image/*" class="hidden" @change="handle($event.target.files)">
    </div>
</template>
