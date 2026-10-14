import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Serif', {
                    alias: 'editorial',
                    weights: [400],
                    styles: ['normal', 'italic'],
                }),
                bunny('Newsreader', {
                    alias: 'reading',
                    weights: [400, 500, 600, 700],
                    styles: ['normal', 'italic'],
                }),
                bunny('Instrument Sans', {
                    alias: 'interface',
                    weights: [400, 500, 600],
                    styles: ['normal', 'italic'],
                }),
                bunny('JetBrains Mono', {
                    alias: 'code',
                    weights: [400, 500],
                    styles: ['normal'],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
