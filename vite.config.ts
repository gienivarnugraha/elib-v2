import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import UnoCSS from 'unocss/vite'
import VueRouter from 'unplugin-vue-router/vite'
import { VueRouterAutoImports } from 'unplugin-vue-router'
// import vsharp from "vite-plugin-vsharp"
import { AnuComponentResolver } from 'anu-vue'
import Components from 'unplugin-vue-components/vite'
import AutoImport from 'unplugin-auto-import/vite'
import Layouts from 'vite-plugin-vue-layouts';

export default defineConfig({
    plugins: [
        VueRouter({
            routesFolder: 'resources/js/pages/',
            dts: 'resources/js/types/typed-router.d.ts'
        }),
        laravel({
            input: ['resources/js/app.js', 'resources/css/app.css'],
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
        UnoCSS({
            configFile: 'uno.config.ts',
        }),
        // vsharp(),
        Components({
            globs: [
                'resources/js/components/**/**/*.vue',
            ],
            // directoryAsNamespace: true,
            resolvers: [
                AnuComponentResolver()
            ],
            dts: 'resources/js/types/components.d.ts'
        }),
        AutoImport({
            dts: 'resources/js/types/auto-import.d.ts',
            eslintrc: {
                enabled: true, // <-- this
            },
            imports: [
                'vue',
                'pinia',
                VueRouterAutoImports,
            ],
        }),
        Layouts({
            layoutsDirs: 'resources/js/layouts',
        })
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
            '@': require('path').resolve(__dirname, 'resources/js'),
            '~': require('path').resolve(__dirname, 'node_modules'),
        },
    },
    build: {
        emptyOutDir: true,
        manifest: true,
    },
});
