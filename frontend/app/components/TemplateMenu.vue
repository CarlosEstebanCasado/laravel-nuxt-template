<script setup lang="ts">
import type { DropdownMenuItem } from '@nuxt/ui'

const { t } = useI18n()

const items = computed<DropdownMenuItem[]>(() => [{
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
  to: 'https://saas-template.nuxt.dev/',
  color: 'primary',
  checked: true,
  type: 'checkbox'
}, {
  label: t('templates.dashboard'),
  to: 'https://dashboard-template.nuxt.dev/'
}, {
  label: t('templates.chat'),
  to: 'https://chat-template.nuxt.dev/'
}, {
  label: t('templates.portfolio'),
  to: 'https://portfolio-template.nuxt.dev/'
}, {
  label: t('templates.changelog'),
  to: 'https://changelog-template.nuxt.dev/'
}] as DropdownMenuItem[])
</script>

<template>
  <UDropdownMenu
    v-slot="{ open }"
    :modal="false"
    :items="items"
    :content="{ align: 'start' }"
    :ui="{ content: 'min-w-fit' }"
    size="xs"
  >
    <UButton
      :label="t('templates.saas')"
      variant="subtle"
      trailing-icon="i-lucide-chevron-down"
      size="xs"
      class="-mb-[6px] font-semibold rounded-full truncate"
      :class="[open && 'bg-primary/15']"
      :ui="{
        trailingIcon: ['transition-transform duration-200', open ? 'rotate-180' : undefined].filter(Boolean).join(' ')
      }"
    />
  </UDropdownMenu>
</template>
