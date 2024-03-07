/// <reference types="vitest" />

import { defineConfig } from 'vite'
import path from "path";
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
    vue(),
    UnoCSS({
      configFile: 'uno.config.ts',
    }),
    // vsharp(),
    Components({
      dirs: [
        'resources/js/components/'
      ],
      // directoryAsNamespace: true,
      resolvers: [
        AnuComponentResolver()
      ],
    }),
    AutoImport({
      imports: [
        'vitest',
        'vue',
        'pinia',
        VueRouterAutoImports,
      ],
    }),
    Layouts({
      layoutsDirs: 'resources/js/layouts',
    })
  ],
  test: {
    globals: true,
    environment: "jsdom",
  },
  
  root: path.resolve(__dirname, './resources/tests'),

  resolve: {
    alias: {
      vue: 'vue/dist/vue.esm-bundler.js',
      '@': require('path').resolve(__dirname, 'resources/js'),
      '~': require('path').resolve(__dirname, 'node_modules'),
    },
  }
})