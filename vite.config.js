import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    server: {
        // public/build.zip and public/*.zip are leftover deploy artifacts, not source files -
        // watching them has crashed the dev server before with an EBUSY file-lock error (some
        // other process, e.g. antivirus, briefly locks them). Vite doesn't need to watch built
        // output anyway.
        watch: {
            ignored: ['**/public/*.zip'],
        },
    },
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
