<script setup lang="ts">
const route = useRoute()
const { t } = useI18n()

const items = computed(() => [{
  label: t('navigation.docs'),
  to: '/docs',
  active: route.path.startsWith('/docs')
}, {
  label: t('navigation.pricing'),
  to: '/pricing'
}, {
  label: t('navigation.blog'),
  to: '/blog'
}, {
  label: t('navigation.changelog'),
  to: '/changelog'
}])
</script>

<template>
  <UHeader>
    <template #left>
      <NuxtLink to="/">
        <AppLogo class="w-auto h-6 shrink-0" />
      </NuxtLink>
      <TemplateMenu />
    </template>

    <UNavigationMenu
      :items="items"
      variant="link"
    />

    <template #right>
      <UColorModeButton />

      <UButton
        icon="i-lucide-log-in"
        color="neutral"
        variant="ghost"
        to="/login"
        class="lg:hidden"
        :aria-label="t('actions.sign_in')"
      />

      <UButton
        :label="t('actions.sign_in')"
        color="neutral"
        variant="outline"
        to="/login"
        class="hidden lg:inline-flex"
      />

      <UButton
        :label="t('actions.sign_up')"
        color="neutral"
        trailing-icon="i-lucide-arrow-right"
        class="hidden lg:inline-flex"
        to="/signup"
      />
    </template>

    <template #body>
      <UNavigationMenu
        :items="items"
        orientation="vertical"
        class="-mx-2.5"
      />

      <USeparator class="my-6" />

      <UButton
        :label="t('actions.sign_in')"
        color="neutral"
        variant="subtle"
        to="/login"
        block
        class="mb-3"
      />
      <UButton
        :label="t('actions.sign_up')"
        color="neutral"
        to="/signup"
        block
      />
    </template>
  </UHeader>
</template>
