import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    return {
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/css/tailwind.css',

                    'resources/js/alpine.js',
                    'resources/js/app.js',
                    'resources/js/axios.js',
                    'resources/js/echo.js',
                ],
                refresh: true,
            }),
        ],
        server: {
            hmr: {
                host: env.APP_IP,
            },
        },
    };
});
