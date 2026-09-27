<script setup>
defineProps({ modelValue: Boolean, label: String, disabled: Boolean });
defineEmits(['update:modelValue']);
</script>

<template>
    <label class="inline-flex cursor-pointer select-none items-center gap-3 text-sm" :class="{ 'opacity-50': disabled }">
        <button
            type="button" role="switch" :aria-checked="modelValue" :disabled="disabled"
            class="relative h-6 w-11 shrink-0 rounded-full transition-colors"
            :class="modelValue ? 'bg-emerald' : 'bg-stone-300'"
            @click="$emit('update:modelValue', !modelValue)"
        >
            <span class="absolute top-0.5 h-5 w-5 rounded-full bg-white shadow transition-all" :class="modelValue ? 'start-[1.375rem]' : 'start-0.5'" />
        </button>
        <span v-if="label">{{ label }}</span>
    </label>
</template>
