import { config } from '@vue/test-utils'

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
  UAlert: stub('UAlert'),
  UModal: stub('UModal'),
  UIcon: stub('UIcon'),
  UBadge: stub('UBadge'),
  UTooltip: stub('UTooltip')
}
