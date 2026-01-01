import { defineNuxtConfig } from 'nuxt/config'

const sharpOptionalPackages = [
  '@img/sharp-linux-x64',
  '@img/sharp-libvips-linux-x64',
  '@img/sharp-linuxmusl-x64',
  '@img/sharp-libvips-linuxmusl-x64'
]

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({

  modules: [
    '~/modules/ensure-nuxt-image-binaries',
    '@nuxt/image',
    '@nuxt/ui',
    '@nuxt/content',
    '@nuxt/eslint',
    '@vueuse/nuxt',
    '@nuxtjs/i18n'
  ],

  // SSR enabled by default (good for SEO on public pages).
  // We selectively disable SSR for private areas via routeRules below.

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
      appBaseUrl: process.env.NUXT_PUBLIC_APP_BASE_URL || 'https://app.project.dev',
      siteBaseUrl: process.env.NUXT_PUBLIC_SITE_BASE_URL || 'https://project.dev'
    }
  },

  // Nuxt supports `ssr: false` inside routeRules at runtime, but Nuxt 4 typings
  // currently don't include it in the route rules type. Cast to keep TS happy.
  routeRules: {
    '/docs': { redirect: '/docs/getting-started', prerender: false },
    '/es/docs': { redirect: '/es/docs/getting-started', prerender: false },
    '/en/docs': { redirect: '/en/docs/getting-started', prerender: false },
    '/ca/docs': { redirect: '/ca/docs/getting-started', prerender: false },

    // Private area: keep it SPA/CSR only (avoid SSR + keep auth purely client-side).
    '/dashboard': {
      ssr: false,
      prerender: false,
      headers: { 'x-robots-tag': 'noindex, nofollow' }
    },
    '/dashboard/**': {
      ssr: false,
      prerender: false,
      headers: { 'x-robots-tag': 'noindex, nofollow' }
    },

    // Auth pages rely on a purely client-side session fetch (cookies + XSRF),
    // so SSR would render incomplete UI (e.g. missing user email) before hydration.
    '/login': {
      ssr: false,
      prerender: false,
      headers: { 'x-robots-tag': 'noindex, nofollow' }
    },
    '/signup': {
      ssr: false,
      prerender: false,
      headers: { 'x-robots-tag': 'noindex, nofollow' }
    },
    '/forgot-password': {
      ssr: false,
      prerender: false,
      headers: { 'x-robots-tag': 'noindex, nofollow' }
    },
    '/reset-password/**': {
      ssr: false,
      prerender: false,
      headers: { 'x-robots-tag': 'noindex, nofollow' }
    },
    '/auth/**': {
      ssr: false,
      prerender: false,
      headers: { 'x-robots-tag': 'noindex, nofollow' }
    }
  } as any,

  devServer: {
    host: '0.0.0.0'
  },
  compatibilityDate: '2025-07-15',

  nitro: {
    prerender: {
      routes: ['/'],
      crawlLinks: true
    },
    ...(process.env.NODE_ENV === 'production'
      ? {
          externals: {
            trace: true,
            inline: sharpOptionalPackages,
            traceInclude: sharpOptionalPackages
          }
        }
      : {})
  },

  vite: {
    build: {
      rollupOptions: {
        onwarn(warning, warn) {
          if (
            warning.code === 'UNRESOLVED_IMPORT' &&
            sharpOptionalPackages.some((pkg) => warning.message.includes(pkg))
          ) {
            return
          }

          warn(warning)
        }
      }
    },
    server: {
      allowedHosts: ['nuxt', 'gateway', 'gateway-api', 'app.project.dev', 'project.dev'],
      hmr: false
    }
  },

  i18n: {
    strategy: 'prefix',
    defaultLocale: 'es',
    lazy: true,
    langDir: 'i18n/locales',
    restructureDir: '.',
    detectBrowserLanguage: {
      useCookie: true,
      cookieKey: 'i18n_redirected',
      redirectOn: 'root',
      alwaysRedirect: false
    },
    locales: [
      { code: 'es', iso: 'es-ES', file: 'es.json', name: 'Español' },
      { code: 'en', iso: 'en-US', file: 'en.json', name: 'English' },
      { code: 'ca', iso: 'ca-ES', file: 'ca.json', name: 'Català' }
    ],
    vueI18n: './i18n.config.ts'
  } as any
})
