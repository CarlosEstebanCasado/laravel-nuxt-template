import { config } from '@vue/test-utils'
import {
  computed,
  definePageMeta,
  onMounted,
  reactive,
  ref,
  watch,
  useHead,
  useAuth,
  useI18n,
  useRouter,
  useRoute,
  useSeoMeta,
  useToast
} from '#imports'
import { useSecurityAuditFormat } from '~/composables/useSecurityAuditFormat'

Object.assign(globalThis, {
  computed,
  definePageMeta,
  onMounted,
  reactive,
  ref,
  watch,
  useHead,
  useAuth,
  useI18n,
  useRouter,
  useRoute,
  useSeoMeta,
  useToast,
  useSecurityAuditFormat
})

const stub = (name: string) => ({
  name,
  props: ['title', 'description', 'label', 'open', 'variant', 'color', 'icon', 'loading', 'disabled'],
  template: `<div :data-stub="'${name}'"><slot /><slot name="header" /><slot name="body" /><slot name="footer" />{{ title }}{{ description }}{{ label }}</div>`
})

config.global.stubs = {
  UPageCard: stub('UPageCard'),
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
