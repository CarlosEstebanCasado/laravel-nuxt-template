<script setup lang="ts">
const colorMode = useColorMode()
const { t, locale: activeLocale } = useI18n()
const localePath = useLocalePath()
const nuxtApp = useNuxtApp()
const config = useRuntimeConfig()
const router = useRouter()
const localeCookie = useCookie<string | null>('i18n_redirected', {
  domain: config.public.i18nCookieDomain,
  path: '/'
})
const supportedLocales = ['es', 'en', 'ca'] as const

const color = computed(() => colorMode.value === 'dark' ? '#020618' : 'white')

useHead(() => ({
  meta: [
    { charset: 'utf-8' },
    { name: 'viewport', content: 'width=device-width, initial-scale=1' },
    { key: 'theme-color', name: 'theme-color', content: color.value }
  ],
  link: [
    { rel: 'icon', href: '/favicon.ico' }
  ],
  htmlAttrs: {
    lang: activeLocale.value
  }
}))

useSeoMeta({
  titleTemplate: '%s - Nuxt SaaS template',
  ogImage: 'https://ui.nuxt.com/assets/templates/nuxt/saas-light.png',
  twitterImage: 'https://ui.nuxt.com/assets/templates/nuxt/saas-light.png',
  twitterCard: 'summary_large_image'
})

const auth = useAuth()

if (import.meta.client) {
  document.cookie = 'i18n_redirected=; Max-Age=0; path=/'
  const currentHost = window.location.host
  const appHost = (() => {
    try {
      return new URL(config.public.appBaseUrl).host
    } catch {
      return null
    }
  })()
  const isAppHost = appHost ? currentHost === appHost : false
  const currentUrl = new URL(window.location.href)
  const localeParam = currentUrl.searchParams.get('locale')
  const validParamLocale = supportedLocales.includes(localeParam as (typeof supportedLocales)[number])
    ? (localeParam as (typeof supportedLocales)[number])
    : null

  if (isAppHost && validParamLocale) {
    localeCookie.value = validParamLocale
    if (nuxtApp.$i18n?.setLocale) {
      void nuxtApp.$i18n.setLocale(validParamLocale)
    } else if (nuxtApp.$i18n?.locale) {
      nuxtApp.$i18n.locale.value = validParamLocale
    } else {
      activeLocale.value = validParamLocale
    }

    currentUrl.searchParams.delete('locale')
    const nextPath = `${currentUrl.pathname}${currentUrl.search}${currentUrl.hash}`
    if (nextPath !== window.location.pathname + window.location.search + window.location.hash) {
      void router.replace(nextPath)
    }
  }

  const cookieLocale = supportedLocales.includes(localeCookie.value as (typeof supportedLocales)[number])
    ? (localeCookie.value as (typeof supportedLocales)[number])
    : null

  if (isAppHost) {
    if (cookieLocale && cookieLocale !== activeLocale.value) {
      if (nuxtApp.$i18n?.setLocale) {
        void nuxtApp.$i18n.setLocale(cookieLocale)
      } else if (nuxtApp.$i18n?.locale) {
        nuxtApp.$i18n.locale.value = cookieLocale
      } else {
        activeLocale.value = cookieLocale
      }
    } else if (!cookieLocale) {
      localeCookie.value = activeLocale.value
    }
  }

  watch(
    () => activeLocale.value,
    (value) => {
      if (!value) {
        return
      }

      if (localeCookie.value !== value) {
        localeCookie.value = value
      }
    }
  )

  if (isAppHost) {
    auth.fetchUser().catch(() => {
      /* swallow errors so the app can render */
    })
  }
}

const { data: navigation } = useAsyncData('navigation', () => queryCollectionNavigation('docs'), {
  transform: data => data.find(item => item.path === '/docs')?.children || []
})
const { data: files } = useLazyAsyncData('search', () => queryCollectionSearchSections('docs'), {
  server: false
})

const links = computed(() => [{
  label: t('navigation.docs'),
  icon: 'i-lucide-book',
  to: localePath('/docs/getting-started')
}, {
  label: t('navigation.pricing'),
  icon: 'i-lucide-credit-card',
  to: localePath('/pricing')
}, {
  label: t('navigation.blog'),
  icon: 'i-lucide-pencil',
  to: localePath('/blog')
}, {
  label: t('navigation.changelog'),
  icon: 'i-lucide-history',
  to: localePath('/changelog')
}])

provide('navigation', navigation)
</script>

<template>
  <UApp>
    <NuxtLoadingIndicator />

    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>

    <ClientOnly>
      <LazyUContentSearch
        :files="files"
        shortcut="meta_k"
        :navigation="navigation"
        :links="links"
        :fuse="{ resultLimit: 42 }"
      />
    </ClientOnly>
  </UApp>
</template>
