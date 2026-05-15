import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'; // Tambahkan ini
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'], // Biasanya CSS di-import di dalam app.js
            refresh: true,
        }),
        vue({ // Tambahkan ini agar file .vue bisa dibaca
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js', // Shortcut agar import komponen lebih mudah
        },
    },
});