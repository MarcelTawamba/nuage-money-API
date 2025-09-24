import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    resolve: {
        alias: {
            '~': path.resolve(__dirname, 'node_modules/'),
            '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap')
        }
    },
    plugins: [
        laravel({
            input: ['resources/sass/app.scss', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                includePaths: ['node_modules']
            }
        }
    }
});