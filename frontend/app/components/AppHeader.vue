<script setup lang="ts">
const route = useRoute()
const router = useRouter()
const { t, locale } = useI18n()
const localePath = useLocalePath()
const switchLocalePath = useSwitchLocalePath()

type LocaleCode = 'es' | 'en' | 'ca'
const localeCodes: LocaleCode[] = ['es', 'en', 'ca']
const localeLabels: Record<LocaleCode, string> = {
  es: 'Español',
  en: 'English',
  ca: 'Català'
}

const localeOptions = computed(() => {
  return localeCodes.map((code) => {
    return { label: localeLabels[code], value: code }
  })
})

const activeLocale = computed<LocaleCode>({
  get: () => locale.value as LocaleCode,
  set: (value) => {
    const target = switchLocalePath(value)
    if (target) {
      void router.push(target)
    }
  }
})

const items = computed(() => [{
  label: t('navigation.docs'),
  to: localePath('/docs'),
  active: route.path.startsWith(localePath('/docs'))
}, {
  label: t('navigation.pricing'),
  to: localePath('/pricing')
}, {
  label: t('navigation.blog'),
  to: localePath('/blog')
}, {
  label: t('navigation.changelog'),
  to: localePath('/changelog')
}])
</script>

<template>
  <UHeader>
    <template #left>
      <NuxtLink :to="localePath('/')">
        <AppLogo class="w-auto h-6 shrink-0" />
      </NuxtLink>
      <TemplateMenu />
    </template>

    <UNavigationMenu
      :items="items"
      variant="link"
    />

    <template #right>
      <USelect
        v-model="activeLocale"
        :items="localeOptions"
        value-key="value"
        label-key="label"
        color="neutral"
        size="xs"
        class="hidden md:inline-flex min-w-28"
        :ui="{ trailingIcon: 'group-data-[state=open]:rotate-180 transition-transform duration-200' }"
      />
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

      <USeparator class="my-6" />
      <USelect
        v-model="activeLocale"
        :items="localeOptions"
        value-key="value"
        label-key="label"
        color="neutral"
        size="sm"
        class="w-full"
        :ui="{ trailingIcon: 'group-data-[state=open]:rotate-180 transition-transform duration-200' }"
      />
    </template>
  </UHeader>
</template>
