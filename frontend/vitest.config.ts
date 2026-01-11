import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '#imports': fileURLToPath(new URL('./tests/__mocks__/nuxt-imports.ts', import.meta.url)),
      '~': fileURLToPath(new URL('./app', import.meta.url)),
      '@': fileURLToPath(new URL('./app', import.meta.url))
    }
  },
  test: {
    include: ['tests/unit/**/*.test.ts'],
    environment: 'jsdom',
    setupFiles: ['./tests/setup.ts'],
    globals: true,
    exclude: ['tests/e2e/**']
  }
})
