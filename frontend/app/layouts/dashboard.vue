<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

// Private area should not be indexed by search engines.
useSeoMeta({
  robots: 'noindex, nofollow'
})

const route = useRoute()
const toast = useToast()
const { t } = useI18n()

const open = ref(false)
const dashboardBase = '/dashboard'

const makeClose = () => {
  open.value = false
}

const links = computed<NavigationMenuItem[][]>(() => [[{
  label: t('sidebar.overview'),
  icon: 'i-lucide-house',
  to: dashboardBase,
  onSelect: makeClose
}, {
  label: t('sidebar.inbox'),
  icon: 'i-lucide-inbox',
  to: `${dashboardBase}/inbox`,
  badge: '4',
  onSelect: makeClose
}, {
  label: t('sidebar.customers'),
  icon: 'i-lucide-users',
  to: `${dashboardBase}/customers`,
  onSelect: makeClose
}, {
  label: t('navigation.settings'),
  to: `${dashboardBase}/settings`,
  icon: 'i-lucide-settings',
  defaultOpen: true,
  type: 'trigger',
  children: [{
    label: t('navigation.general'),
    to: `${dashboardBase}/settings`,
    exact: true,
    onSelect: makeClose
  }, {
    label: t('navigation.preferences'),
    to: `${dashboardBase}/settings/preferences`,
    onSelect: makeClose
  }, {
    label: t('navigation.members'),
    to: `${dashboardBase}/settings/members`,
    onSelect: makeClose
  }, {
    label: t('navigation.notifications'),
    to: `${dashboardBase}/settings/notifications`,
    onSelect: makeClose
  }, {
    label: t('navigation.security'),
    to: `${dashboardBase}/settings/security`,
    onSelect: makeClose
  }]
}], [{
  label: t('sidebar.marketing_site'),
  icon: 'i-lucide-globe',
  to: '/',
  onSelect: makeClose
}, {
  label: t('sidebar.support'),
  icon: 'i-lucide-life-buoy',
  to: 'https://ui.nuxt.com',
  target: '_blank'
}]])

const sourcePath = computed(() => {
  const current = route.path.startsWith(dashboardBase)
    ? route.path.slice(dashboardBase.length) || '/index'
    : route.path || '/index'

  return current.startsWith('/') ? current : `/${current}`
})

const groups = computed(() => [{
  id: 'links',
  label: t('sidebar.go_to'),
  items: links.value.flat()
}, {
  id: 'code',
  label: t('sidebar.code'),
  items: [{
    id: 'source',
    label: t('sidebar.view_source'),
    icon: 'i-simple-icons-github',
    to: `https://github.com/nuxt-ui-templates/dashboard/blob/main/app/pages${sourcePath.value}.vue`,
    target: '_blank'
  }]
}])

onMounted(async () => {
  const cookie = useCookie('cookie-consent')
  if (cookie.value === 'accepted') {
    return
  }

  toast.add({
    title: t('cookies.message'),
    duration: 0,
    close: false,
    actions: [{
      label: t('cookies.accept'),
      color: 'neutral',
      variant: 'outline',
      onClick: () => {
        cookie.value = 'accepted'
      }
    }, {
      label: t('cookies.opt_out'),
      color: 'neutral',
      variant: 'ghost'
    }]
  })
})
</script>

<template>
  <UDashboardGroup unit="rem">
    <UDashboardSidebar
      id="default"
      v-model:open="open"
      collapsible
      resizable
      class="bg-elevated/25"
      :ui="{ footer: 'lg:border-t lg:border-default' }"
    >
      <template #header="{ collapsed }">
        <TeamsMenu :collapsed="collapsed" />
      </template>

      <template #default="{ collapsed }">
        <UDashboardSearchButton :collapsed="collapsed" class="bg-transparent ring-default" />

        <UNavigationMenu
          :collapsed="collapsed"
          :items="links[0]"
          orientation="vertical"
          tooltip
          popover
        />

        <UNavigationMenu
          :collapsed="collapsed"
          :items="links[1]"
          orientation="vertical"
          tooltip
          class="mt-auto"
        />
      </template>

      <template #footer="{ collapsed }">
        <UserMenu :collapsed="collapsed" />
      </template>
    </UDashboardSidebar>

    <UDashboardSearch :groups="groups" />

    <slot />

    <NotificationsSlideover />
  </UDashboardGroup>
</template>
