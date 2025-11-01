<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

const route = useRoute()
const toast = useToast()

const open = ref(false)
const dashboardBase = '/dashboard'

const makeClose = () => {
  open.value = false
}

const links = [[{
  label: 'Overview',
  icon: 'i-lucide-house',
  to: dashboardBase,
  onSelect: makeClose
}, {
  label: 'Inbox',
  icon: 'i-lucide-inbox',
  to: `${dashboardBase}/inbox`,
  badge: '4',
  onSelect: makeClose
}, {
  label: 'Customers',
  icon: 'i-lucide-users',
  to: `${dashboardBase}/customers`,
  onSelect: makeClose
}, {
  label: 'Settings',
  to: `${dashboardBase}/settings`,
  icon: 'i-lucide-settings',
  defaultOpen: true,
  type: 'trigger',
  children: [{
    label: 'General',
    to: `${dashboardBase}/settings`,
    exact: true,
    onSelect: makeClose
  }, {
    label: 'Members',
    to: `${dashboardBase}/settings/members`,
    onSelect: makeClose
  }, {
    label: 'Notifications',
    to: `${dashboardBase}/settings/notifications`,
    onSelect: makeClose
  }, {
    label: 'Security',
    to: `${dashboardBase}/settings/security`,
    onSelect: makeClose
  }]
}], [{
  label: 'Marketing site',
  icon: 'i-lucide-globe',
  to: '/',
  onSelect: makeClose
}, {
  label: 'Support',
  icon: 'i-lucide-life-buoy',
  to: 'https://ui.nuxt.com',
  target: '_blank'
}]] satisfies NavigationMenuItem[][]

const sourcePath = computed(() => {
  const current = route.path.startsWith(dashboardBase)
    ? route.path.slice(dashboardBase.length) || '/index'
    : route.path || '/index'

  return current.startsWith('/') ? current : `/${current}`
})

const groups = computed(() => [{
  id: 'links',
  label: 'Go to',
  items: links.flat()
}, {
  id: 'code',
  label: 'Code',
  items: [{
    id: 'source',
    label: 'View page source',
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
    title: 'We use first-party cookies to enhance your experience on our website.',
    duration: 0,
    close: false,
    actions: [{
      label: 'Accept',
      color: 'neutral',
      variant: 'outline',
      onClick: () => {
        cookie.value = 'accepted'
      }
    }, {
      label: 'Opt out',
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
