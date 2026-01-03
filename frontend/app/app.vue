<script setup lang="ts">
const colorMode = useColorMode()
const { t, locale: activeLocale } = useI18n()
const localePath = useLocalePath()

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
const config = useRuntimeConfig()

if (import.meta.client) {
  const shouldFetchUser = () => {
    try {
      const appHost = new URL(config.public.appBaseUrl).host
      return window.location.host === appHost
    } catch {
      return true
    }
  }

  if (shouldFetchUser()) {
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
