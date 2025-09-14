import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/study-utils.js',
                'resources/js/study-session-unified.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
