import { defineI18nConfig } from '#imports'

export default defineI18nConfig(() => ({
  legacy: false,
  fallbackLocale: 'en',
  availableLocales: ['es', 'en', 'ca'],
  warnHtmlMessage: false
}))
