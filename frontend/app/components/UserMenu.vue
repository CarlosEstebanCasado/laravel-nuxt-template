<script setup lang="ts">
import type { DropdownMenuItem } from '@nuxt/ui'

defineProps<{
  collapsed?: boolean
}>()

const auth = useAuth()
const router = useRouter()
const { t } = useI18n()

const dashboardBase = '/dashboard'

const user = computed(() => {
  const name = auth.user.value?.name ?? t('userMenu.guest')
  const email = auth.user.value?.email ?? ''
  return {
    name,
    avatar: {
      alt: name,
      text: name.charAt(0).toUpperCase()
    },
    email
  }
})

const items = computed<DropdownMenuItem[][]>(() => ([[{
  type: 'label',
  label: user.value.name,
  description: user.value.email,
  avatar: user.value.avatar
}], [{
  label: t('userMenu.profile'),
  icon: 'i-lucide-user'
}, {
  label: t('userMenu.billing'),
  icon: 'i-lucide-credit-card'
}, {
  label: t('userMenu.settings'),
  icon: 'i-lucide-settings',
  to: `${dashboardBase}/settings`
}], [{
  label: t('userMenu.templates'),
  icon: 'i-lucide-layout-template',
  children: [{
    label: t('templates.starter'),
    to: 'https://starter-template.nuxt.dev/'
  }, {
    label: t('templates.landing'),
    to: 'https://landing-template.nuxt.dev/'
  }, {
    label: t('templates.docs'),
    to: 'https://docs-template.nuxt.dev/'
  }, {
    label: t('templates.saas'),
    to: 'https://saas-template.nuxt.dev/'
  }, {
    label: t('templates.dashboard'),
    to: 'https://dashboard-template.nuxt.dev/',
    color: 'primary',
    checked: true,
    type: 'checkbox'
  }, {
    label: t('templates.chat'),
    to: 'https://chat-template.nuxt.dev/'
  }, {
    label: t('templates.portfolio'),
    to: 'https://portfolio-template.nuxt.dev/'
  }, {
    label: t('templates.changelog'),
    to: 'https://changelog-template.nuxt.dev/'
  }]
}], [{
  label: t('userMenu.documentation'),
  icon: 'i-lucide-book-open',
  to: 'https://ui.nuxt.com/docs/getting-started/installation/nuxt',
  target: '_blank'
}, {
  label: t('userMenu.github'),
  icon: 'i-simple-icons-github',
  to: 'https://github.com/nuxt-ui-templates/dashboard',
  target: '_blank'
}, {
  label: t('userMenu.logout'),
  icon: 'i-lucide-log-out',
  onSelect: async () => {
    await auth.logout()
    router.push('/login')
  }
}]]))
</script>

<template>
  <UDropdownMenu
    :items="items"
    :content="{ align: 'center', collisionPadding: 12 }"
    :ui="{ content: collapsed ? 'w-48' : 'w-(--reka-dropdown-menu-trigger-width)' }"
  >
    <UButton
      v-bind="{
        avatar: user.avatar,
        label: collapsed ? undefined : user?.name,
        trailingIcon: collapsed ? undefined : 'i-lucide-chevrons-up-down'
      }"
      data-testid="user-menu-trigger"
      color="neutral"
      variant="ghost"
      block
      :square="collapsed"
      class="data-[state=open]:bg-elevated"
      :ui="{
        trailingIcon: 'text-dimmed'
      }"
    />

  </UDropdownMenu>
</template>
