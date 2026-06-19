import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            // app.css/app.js are the shared base (and fallback); each theme also gets
            // its own CSS bundle, loaded by that theme's layouts. app.js (Alpine) is shared.
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/themes/kewlchats.css',
                'resources/css/themes/ready2im.css',
            ],
            refresh: true,
        }),
    ],
});
