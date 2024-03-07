// uno.config.ts
import { defineConfig } from 'unocss'
import transformerVariantGroup from '@unocss/transformer-variant-group'
import transformerDirectives from '@unocss/transformer-directives'
import { presetIcons } from 'unocss'
import presetUno from '@unocss/preset-uno'
import { presetAnu, presetIconExtraProperties } from 'anu-vue'
import { presetThemeDefault } from '@anu-vue/preset-theme-default'

export default defineConfig({
  // ...
  shortcuts: [
    { 'navbar-separator': 'mx-3 h-navbar border-l border-neutral-200 dark:border-neutral-600 lg:mx-6' }
  ],
  presets: [
    presetUno(),
    presetIcons({
      scale: 1.2,
      extraProperties: presetIconExtraProperties,
    }),

    // anu-vue preset
    presetAnu(),

    // default theme preset
    presetThemeDefault(),
  ],
  transformers: [
    transformerVariantGroup(),
    transformerDirectives(),
  ],
  safelist: [
    /* icon color safelist */
    ...['green', 'purple', 'orange', 'blue', 'yellow', 'indigo','pink','gray'].map(color => {
      return `text-${color}-400 dark:text-${color}-500`
    }).join(' ').split(' '),
    
  ],
  content: {
    pipeline: {
      include: [/.*\/anu-vue\.js(.*)?$/, './**/*.vue', './**/*.md'],
    }
  },
  include: [/.*\/anu-vue\.js(.*)?$/, './**/*.vue', './**/*.md'],

})