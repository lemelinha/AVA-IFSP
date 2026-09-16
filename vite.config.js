import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig, lazyPlugins } from 'vite-plus';

const isCodespace = !!process.env.CODESPACE_NAME;

export default defineConfig({
    plugins: lazyPlugins(() => [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ]),
    server: {
        host: '0.0.0.0',
        port: 5173,
        cors: true,
        hmr: {
            host: isCodespace ? `${process.env.CODESPACE_NAME}-5173.app.github.dev` : 'localhost',
            protocol: isCodespace ? 'wss' : 'ws',
            clientPort: isCodespace ? 443 : 5173
        },
        watch: {
            ignored: [
                '**/.agents/**',
                '**/.claude/**',
                '**/.cursor/**',
                '**/.junie/**',
                '**/storage/framework/views/**',
                '**/vendor/**',
            ],
        },
    },
});