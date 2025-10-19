// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  ssr: true,
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'https://api.project.dev/api/v1',
      appBaseUrl: process.env.NUXT_PUBLIC_APP_BASE_URL || 'https://app.project.dev'
    }
  }
})
