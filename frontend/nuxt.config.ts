import { defineNuxtConfig } from 'nuxt/config'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({

  modules: [
    '@nuxt/image',
    '@nuxt/ui',
    '@nuxt/content',
    '@vueuse/nuxt'
  ],

  ssr: false,

  devtools: {
    enabled: true
  },

  css: ['~/assets/css/main.css'],

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'https://api.project.dev',
      internalApiBase: process.env.NUXT_PUBLIC_INTERNAL_API_BASE || 'http://gateway-api',
      apiPrefix: process.env.NUXT_PUBLIC_API_PREFIX || '/api/v1',
      authPrefix: process.env.NUXT_PUBLIC_AUTH_PREFIX || '/auth',
      appBaseUrl: process.env.NUXT_PUBLIC_APP_BASE_URL || 'https://app.project.dev'
    }
  },

  routeRules: {
    '/docs': { redirect: '/docs/getting-started', prerender: false }
  },

  devServer: {
    host: '0.0.0.0'
  },
  compatibilityDate: '2025-07-15',

  nitro: {
    prerender: {
      routes: ['/'],
      crawlLinks: true
    }
  },

  vite: {
    server: {
      allowedHosts: ['nuxt', 'gateway', 'gateway-api', 'app.project.dev']
    }
  }
})
