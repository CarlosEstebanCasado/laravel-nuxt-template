// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  ssr: false,

  modules: [
    '@nuxt/image',
    '@nuxt/ui',
    '@nuxt/content',
    '@vueuse/nuxt',
    'nuxt-og-image'
  ],

  devtools: {
    enabled: true
  },

  css: ['~/assets/css/main.css'],

  routeRules: {
    '/docs': { redirect: '/docs/getting-started', prerender: false }
  },

  compatibilityDate: '2025-07-15',

  nitro: {
    prerender: {
      routes: ['/'],
      crawlLinks: true
    }
  },

  colorMode: {
    preference: 'system',
    fallback: 'dark',
    classSuffix: ''
  },

  devServer: {
    host: '0.0.0.0'
  },

  vite: {
    server: {
      allowedHosts: ['nuxt', 'gateway', 'gateway-api', 'app.project.dev']
    }
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'https://api.project.dev',
      internalApiBase: process.env.NUXT_PUBLIC_INTERNAL_API_BASE || 'http://gateway-api',
      apiPrefix: process.env.NUXT_PUBLIC_API_PREFIX || '/api/v1',
      authPrefix: process.env.NUXT_PUBLIC_AUTH_PREFIX || '/auth',
      appBaseUrl: process.env.NUXT_PUBLIC_APP_BASE_URL || 'https://app.project.dev'
    }
  }
})
