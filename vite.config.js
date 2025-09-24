import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    resolve: {
        alias: {
            '~': path.resolve(__dirname, 'node_modules/'),
            '@popperjs/core': path.resolve(__dirname, 'node_modules/@popperjs/core/dist/esm/popper.js')
        }
    },
    plugins: [
        laravel({
            input: ['resources/sass/app.scss', 'resources/js/app.js'],
            refresh: true,
        }),
    ]
});