<script setup>
defineProps({ status: String });
const styles = {
    pending: 'bg-gold/20 text-[#7a5f0a]',
    confirmed: 'bg-emerald/15 text-emerald',
    seated: 'bg-sky-100 text-sky-800',
    cancelled: 'bg-stone-200 text-stone-600',
    no_show: 'bg-terracotta/15 text-terracotta',
};
</script>

<template>
    <span class="a-pill capitalize" :class="styles[status] || 'bg-stone-100'">{{ status?.replace('_', ' ') }}</span>
</template>
