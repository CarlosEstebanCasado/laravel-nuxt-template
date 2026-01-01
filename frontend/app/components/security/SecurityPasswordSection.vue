<script setup lang="ts">
import * as z from 'zod'
import type { FormError, FormSubmitEvent } from '@nuxt/ui'

const auth = useAuth()
const toast = useToast()
const { t } = useI18n()

const passwordSchema = z.object({
  current_password: z.string().min(8, t('messages.validation.password_min')),
  password: z.string().min(8, t('messages.validation.password_min')),
  password_confirmation: z.string().min(8, t('messages.validation.password_min'))
})

type PasswordSchema = z.output<typeof passwordSchema>

const password = reactive<Partial<PasswordSchema>>({
  current_password: undefined,
  password: undefined,
  password_confirmation: undefined
})

const validate = (state: Partial<PasswordSchema>): FormError[] => {
  const errors: FormError[] = []
  if (state.current_password && state.password && state.current_password === state.password) {
    errors.push({ name: 'password', message: t('messages.validation.passwords_different') })
  }
  if (state.password && state.password_confirmation && state.password !== state.password_confirmation) {
    errors.push({ name: 'password_confirmation', message: t('messages.validation.passwords_mismatch') })
  }
  return errors
}

const extractErrorMessage = (error: unknown) => {
  const data = (error as any)?.data ?? (error as any)?.response?._data
  if (data?.errors) {
    const firstError = Object.values(data.errors).flat()[0]
    if (typeof firstError === 'string') {
      return firstError
    }
  }
  if (typeof data?.message === 'string') {
    return data.message
  }
  return (error as any)?.message || t('settings.security.errors.password')
}

const isSubmitting = ref(false)

async function onSubmit(event: FormSubmitEvent<PasswordSchema>) {
  if (isSubmitting.value) {
    return
  }

  isSubmitting.value = true
  try {
    await auth.updatePassword(event.data)
    toast.add({
      title: t('settings.security.toasts.password_updated'),
      description: t('settings.security.toasts.password_description'),
      color: 'success',
      icon: 'i-lucide-check'
    })

    password.current_password = undefined
    password.password = undefined
    password.password_confirmation = undefined
  } catch (error) {
    toast.add({
      title: t('settings.security.toasts.action_failed'),
      description: extractErrorMessage(error),
      color: 'error',
    })
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <UPageCard
    :title="t('settings.security.password_section.title')"
    :description="t('settings.security.password_section.description')"
    variant="subtle"
  >
    <UForm
      :schema="passwordSchema"
      :state="password"
      :validate="validate"
      class="flex flex-col gap-4 max-w-xs"
      @submit="onSubmit"
    >
      <UFormField name="current_password">
        <UInput
          v-model="password.current_password"
          type="password"
          :placeholder="t('settings.security.password_section.current_placeholder')"
          class="w-full"
        />
      </UFormField>

      <UFormField name="password">
        <UInput
          v-model="password.password"
          type="password"
          :placeholder="t('settings.security.password_section.new_placeholder')"
          class="w-full"
        />
      </UFormField>

      <UFormField name="password_confirmation">
        <UInput
          v-model="password.password_confirmation"
          type="password"
          :placeholder="t('settings.security.password_section.confirm_placeholder')"
          class="w-full"
        />
      </UFormField>

      <UButton
        :label="t('settings.security.password_section.button')"
        class="w-fit"
        type="submit"
        :loading="isSubmitting"
      />
    </UForm>
  </UPageCard>
</template>
