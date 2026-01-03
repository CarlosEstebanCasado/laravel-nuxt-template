import { config } from '@vue/test-utils'
import {
  computed,
  defineI18nRoute,
  definePageMeta,
  onMounted,
  reactive,
  ref,
  watch,
  useHead,
  useAuth,
  useI18n,
  useLocalePath,
  useRouter,
  useRoute,
  useSeoMeta,
  useSwitchLocalePath,
  useToast
} from '#imports'
import { useSecurityAuditFormat } from '~/composables/useSecurityAuditFormat'

Object.assign(globalThis, {
  computed,
  defineI18nRoute,
  definePageMeta,
  onMounted,
  reactive,
  ref,
  watch,
  useHead,
  useAuth,
  useI18n,
  useLocalePath,
  useRouter,
  useRoute,
  useSeoMeta,
  useSwitchLocalePath,
  useToast,
  useSecurityAuditFormat
})

const stub = (name: string) => ({
  name,
  props: ['title', 'description', 'label', 'open', 'variant', 'color', 'icon', 'loading', 'disabled'],
  template: `<div :data-stub="'${name}'"><slot /><slot name="header" /><slot name="body" /><slot name="footer" /><slot name="right" />{{ title }}{{ description }}{{ label }}</div>`
})

config.global.stubs = {
  UPageCard: stub('UPageCard'),
  UHeader: stub('UHeader'),
  UFooter: {
    name: 'UFooter',
    props: ['ui'],
    template: `<div data-stub="UFooter"><slot /><slot name="top" /><slot name="left" /><slot name="right" /></div>`
  },
  UFooterColumns: stub('UFooterColumns'),
  UContainer: stub('UContainer'),
  UForm: stub('UForm'),
  UFormField: stub('UFormField'),
  UInput: stub('UInput'),
  UButton: {
    name: 'UButton',
    props: ['label', 'loading', 'disabled'],
    template: `<button type="button" :disabled="disabled" @click="$emit('click')">{{ label }}<slot /></button>`
  },
  USelect: {
    name: 'USelect',
    props: ['modelValue', 'items', 'labelKey', 'valueKey', 'disabled'],
    template: `<select data-stub="USelect"></select>`
  },
  UDropdownMenu: {
    name: 'UDropdownMenu',
    props: ['items', 'content', 'ui', 'modal', 'size'],
    template: `<div data-stub="UDropdownMenu"><slot /></div>`
  },
  UNavigationMenu: {
    name: 'UNavigationMenu',
    props: ['items', 'variant', 'orientation'],
    template: `<nav data-stub="UNavigationMenu"></nav>`
  },
  UColorModeButton: stub('UColorModeButton'),
  NuxtLink: {
    name: 'NuxtLink',
    props: ['to'],
    template: `<a :href="to"><slot /></a>`
  },
  AppLogo: stub('AppLogo'),
  TemplateMenu: stub('TemplateMenu'),
  USeparator: stub('USeparator'),
  UAuthForm: {
    name: 'UAuthForm',
    props: ['title', 'loading', 'fields', 'schema', 'state', 'providers', 'submit'],
    template: `<button type="button" data-stub="UAuthForm" @click="$emit('submit', { data: {} })">{{ title }}</button>`
  },
  ULink: {
    name: 'ULink',
    props: ['to'],
    template: `<a :href="to"><slot /></a>`
  },
  UAlert: stub('UAlert'),
  UModal: stub('UModal'),
  UIcon: stub('UIcon'),
  UBadge: stub('UBadge'),
  UTooltip: stub('UTooltip')
}
