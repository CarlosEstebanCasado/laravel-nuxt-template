<script setup lang="ts">
import type { FormSubmitEvent } from '@nuxt/ui'

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})

const auth = useAuth()
const { t } = useI18n()
const toast = useToast()

const form = reactive({
  locale: 'es',
  theme: 'system'
})

const isLoading = ref(true)
const isSaving = ref(false)

const localeOptions = computed(() => auth.preferenceOptions.value.locales)
const themeOptions = computed(() => auth.preferenceOptions.value.themes)

const syncPreferences = () => {
  if (!auth.preferences.value) {
    return
  }

  form.locale = auth.preferences.value.locale
  form.theme = auth.preferences.value.theme
}

onMounted(async () => {
  try {
    await auth.fetchUser()
    await auth.fetchPreferences()
    syncPreferences()
  } finally {
    isLoading.value = false
  }
})

const onSubmit = async (event: FormSubmitEvent<typeof form>) => {
  if (isSaving.value) {
    return
  }

  isSaving.value = true
  try {
    await auth.updatePreferences({
      locale: event.data.locale,
      theme: event.data.theme
    })
    toast.add({
      title: t('preferences.success'),
      color: 'success',
      icon: 'i-lucide-check'
    })
  } catch (error: any) {
    toast.add({
      title: 'Error',
      description: error?.data?.message ?? 'Unable to save preferences.',
      color: 'error'
    })
  } finally {
    isSaving.value = false
  }
}
</script>

<template>
  <UForm
    :state="form"
    @submit="onSubmit"
  >
    <UPageCard
      :title="t('preferences.title')"
      :description="t('preferences.description')"
      variant="subtle"
      orientation="horizontal"
      class="mb-4"
    >
      <UButton
        type="submit"
        :label="t('preferences.save')"
        color="neutral"
        :loading="isSaving || isLoading"
        class="w-fit lg:ms-auto"
      />
    </UPageCard>

    <UPageCard variant="outline">
      <UFormField
        name="locale"
        :label="t('preferences.language_label')"
        :description="t('preferences.language_hint')"
        class="flex max-sm:flex-col justify-between items-start gap-4"
      >
        <USelect
          v-model="form.locale"
          :items="localeOptions"
          label-key="label"
          value-key="value"
          :disabled="isLoading"
        />
      </UFormField>

      <USeparator />

      <UFormField
        name="theme"
        :label="t('preferences.theme_label')"
        :description="t('preferences.theme_hint')"
        class="flex max-sm:flex-col justify-between items-start gap-4"
      >
        <USelect
          v-model="form.theme"
          :items="themeOptions"
          label-key="label"
          value-key="value"
          :disabled="isLoading"
        />
      </UFormField>
    </UPageCard>
  </UForm>
</template>
