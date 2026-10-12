/// <reference types="vitest/config" />

import vue from '@vitejs/plugin-vue'
import vuetify from 'vite-plugin-vuetify'
import { defineConfig } from 'vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue(), vuetify({ autoImport: true })],
  test: {
    css: true,
    environment: 'jsdom',
    globals: true,
    include: ['src/**/*.spec.ts'],
  },
})
