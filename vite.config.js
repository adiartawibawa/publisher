import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import svgLoader from "vite-svg-loader";
const path = require('path')

export default defineConfig(async ({
    command
}) => {
    return {
        base: command === "serve" ?
            process.env.ASSET_URL || "" : `${process.env.ASSET_URL || ""}/build/`,
        publicDir: false,
        build: {
            manifest: true,
            outDir: "public/build",
            rollupOptions: {
                input: {
                    app: "resources/js/app.js",
                },
            },
        },
        server: {
            fs: {
                allow: [`${process.cwd()}`]
            },
            port: process.env?.VITE_PORT ?? 5173,
        },
        plugins: [
            laravel({
                input: 'resources/js/app.js',
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
            svgLoader(),
        ],
        resolve: {
            alias: {
                '~node': path.resolve(__dirname, './node_modules/'),
            }
        },
        optimizeDeps: {
            include: [
                "@inertiajs/inertia",
                "@inertiajs/inertia-vue3",
                "axios",
                "vue",
            ],
        },
    }

});