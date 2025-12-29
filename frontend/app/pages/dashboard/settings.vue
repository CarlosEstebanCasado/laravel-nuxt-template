<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})

const dashboardBase = '/dashboard'
const { t } = useI18n()

const links = computed(() => [[{
  label: t('navigation.general'),
  icon: 'i-lucide-user',
  to: `${dashboardBase}/settings`,
  exact: true
}, {
  label: t('navigation.preferences'),
  icon: 'i-lucide-sliders-horizontal',
  to: `${dashboardBase}/settings/preferences`
}, {
  label: t('navigation.members'),
  icon: 'i-lucide-users',
  to: `${dashboardBase}/settings/members`
}, {
  label: t('navigation.notifications'),
  icon: 'i-lucide-bell',
  to: `${dashboardBase}/settings/notifications`
}, {
  label: t('navigation.security'),
  icon: 'i-lucide-shield',
  to: `${dashboardBase}/settings/security`
}], [{
  label: t('navigation.documentation'),
  icon: 'i-lucide-book-open',
  to: 'https://ui.nuxt.com/docs/getting-started/installation/nuxt',
  target: '_blank'
}]]) satisfies NavigationMenuItem[][]
</script>

<template>
  <UDashboardPanel id="settings" :ui="{ body: 'lg:py-12' }">
    <template #header>
      <UDashboardNavbar :title="t('settings.title')">
        <template #leading>
          <UDashboardSidebarCollapse />
        </template>
      </UDashboardNavbar>

      <UDashboardToolbar>
        <!-- NOTE: The `-mx-1` class is used to align with the `DashboardSidebarCollapse` button here. -->
        <UNavigationMenu :items="links" highlight class="-mx-1 flex-1" />
      </UDashboardToolbar>
    </template>

    <template #body>
      <div class="flex flex-col gap-4 sm:gap-6 lg:gap-12 w-full lg:max-w-2xl mx-auto">
        <NuxtPage />
      </div>
    </template>
  </UDashboardPanel>
</template>
