import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/site.css',
                'resources/js/site/main.js',
                'resources/css/admin.css',
                'resources/js/admin/main.js',
            ],
            refresh: true,
        }),
        vue({ template: { transformAssetUrls: { base: null, includeAbsolute: false } } }),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
    define: { __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false },
    build: {
        rollupOptions: {
            output: {
                manualChunks: { vendor: ['vue', 'vue-router', 'pinia', 'vue-i18n', 'axios'] },
            },
        },
    },
});
