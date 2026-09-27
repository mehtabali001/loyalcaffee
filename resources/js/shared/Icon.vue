<script setup>
/** Thin-line icon set (Brand Guide §1.11). 24px grid, 1.5 stroke, currentColor. */
const props = defineProps({ name: { type: String, required: true }, size: { type: [Number, String], default: 22 } });

const P = {
    coffee: '<path d="M4 9h13v5a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5V9Z"/><path d="M17 11h1.5a2.5 2.5 0 0 1 0 5H17"/><path d="M8 3c0 1.2 1 1.4 1 2.6M12 3c0 1.2 1 1.4 1 2.6"/>',
    shisha: '<path d="M10 3h4l-.6 3h-2.8L10 3Z"/><path d="M12 6v5"/><path d="M8.5 11h7l-.8 2.2c1.9.8 3.3 2.7 3.3 4.8a6 6 0 0 1-12 0c0-2.1 1.4-4 3.3-4.8L8.5 11Z"/><path d="M15 8c2.5 0 4 1 5 3"/>',
    plate: '<circle cx="12" cy="13" r="6"/><circle cx="12" cy="13" r="3"/><path d="M3 4v5a2 2 0 0 0 2 2v9M5 4v4M21 4c-1.5 0-2.5 2-2.5 5s1 3 2.5 3v8"/>',
    juice: '<path d="M6 7h12l-1.5 13h-9L6 7Z"/><path d="M14 7l2-4h3"/><path d="M7 12h10"/>',
    glass: '<path d="M5 4h14l-6 8v6h3M11 18h2"/><path d="M8.5 8.5h7"/><circle cx="16.5" cy="5.5" r="1.5"/>',
    dessert: '<path d="M5 13h14l-1.5 7h-11L5 13Z"/><path d="M6 13a6 6 0 0 1 12 0"/><path d="M12 4v3"/><circle cx="12" cy="3.5" r="1"/>',
    sun: '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
    moon: '<path d="M20 14.5A8 8 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5Z"/>',
    parking: '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M10 17V7h3a3 3 0 0 1 0 6h-3"/>',
    screen: '<rect x="2.5" y="4" width="19" height="12" rx="1.5"/><path d="M8 20h8M12 16v4"/><circle cx="12" cy="10" r="2.4"/>',
    wifi: '<path d="M2.5 9a14 14 0 0 1 19 0M5.5 12.5a9.5 9.5 0 0 1 13 0M8.8 15.8a5 5 0 0 1 6.4 0"/><circle cx="12" cy="19" r="1"/>',
    family: '<circle cx="8" cy="6" r="2.5"/><circle cx="16" cy="6" r="2.5"/><circle cx="12" cy="13" r="2"/><path d="M4 21v-6a4 4 0 0 1 4-4M20 21v-6a4 4 0 0 0-4-4M9 21v-3a3 3 0 0 1 6 0v3"/>',
    football: '<circle cx="12" cy="12" r="9"/><path d="m12 7 4 3-1.5 4.5h-5L8 10l4-3Z"/><path d="M12 3v4M16 10l4.5-1.5M14.5 14.5 17 19M9.5 14.5 7 19M8 10 3.5 8.5"/>',
    leaf: '<path d="M5 19c0-9 6-14 15-14 0 9-5 15-14 15"/><path d="M5 19 13 11"/>',
    phone: '<path d="M5 3h3.5l1.5 4.5-2.2 1.3a11 11 0 0 0 5.4 5.4l1.3-2.2L19 13.5V17a2 2 0 0 1-2 2A14 14 0 0 1 3 5a2 2 0 0 1 2-2Z"/>',
    whatsapp: '<path d="M4 20l1.2-3.7A8.5 8.5 0 1 1 8 19l-4 1Z"/><path d="M9 8.5c0 3.5 3 6.5 6.5 6.5l1.2-1.6-2-1-1 .9A5 5 0 0 1 11 10.6l.9-1-1-2L9 8.5Z"/>',
    pin: '<path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11Z"/><circle cx="12" cy="10" r="2.3"/>',
    clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    star: '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9L12 3Z"/>',
    instagram: '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.3" cy="6.7" r=".6" fill="currentColor"/>',
    tiktok: '<path d="M14 3v11.5a3.5 3.5 0 1 1-3.5-3.5"/><path d="M14 3c.5 2.8 2.3 4.4 5 4.6"/>',
    facebook: '<path d="M14 21v-7.5h2.6l.4-3H14V8.7c0-.9.3-1.5 1.6-1.5H17V4.5c-.3 0-1.2-.1-2.3-.1-2.3 0-3.7 1.4-3.7 3.9v2.2H8.5v3H11V21"/>',
    google: '<path d="M20.5 12.2c0-.6-.1-1.2-.2-1.7H12v3.3h4.8a4.3 4.3 0 0 1-1.8 2.7v2.2h2.9c1.7-1.6 2.6-3.8 2.6-6.5Z"/><path d="M12 21c2.4 0 4.5-.8 5.9-2.2L15 16.5c-.8.5-1.8.9-3 .9-2.3 0-4.3-1.6-5-3.7H4v2.3A9 9 0 0 0 12 21Z"/><path d="M7 13.7a5.4 5.4 0 0 1 0-3.4V8H4a9 9 0 0 0 0 8l3-2.3Z"/><path d="M12 6.6c1.3 0 2.5.5 3.4 1.3L18 5.4A9 9 0 0 0 4 8l3 2.3c.7-2.1 2.7-3.7 5-3.7Z"/>',
    menu: '<path d="M4 7h16M4 12h16M4 17h10"/>',
    close: '<path d="M6 6l12 12M18 6 6 18"/>',
    chevron: '<path d="m9 6 6 6-6 6"/>',
    search: '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>',
    calendar: '<rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
    users: '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M21.5 20a6.5 6.5 0 0 0-4-6"/>',
    check: '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
    globe: '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.7 3.7 5.7 3.7 9s-1.2 6.3-3.7 9c-2.5-2.7-3.7-5.7-3.7-9S9.5 5.7 12 3Z"/>',
};
</script>

<template>
    <svg
        :width="props.size" :height="props.size" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"
        v-html="P[props.name] || P.leaf"
    />
</template>
