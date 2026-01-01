<script setup lang="ts">
import * as z from 'zod'
import type { FormError, FormSubmitEvent } from '@nuxt/ui'

defineI18nRoute(false)

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})

const auth = useAuth()
const router = useRouter()
const toast = useToast()
const { t } = useI18n()
const isSubmitting = ref(false)
const isLoading = ref(true)

const profileSchema = z.object({
  name: z.string().min(2, t('messages.validation.too_short')),
  email: z.string().email(t('messages.validation.invalid_email')),
})

type ProfileSchema = z.output<typeof profileSchema>

type ProfileState = Partial<ProfileSchema> & { current_password?: string }

const profile = reactive<ProfileState>({
  name: undefined,
  email: undefined,
  current_password: undefined,
})

const requiresPasswordForEmailChange = computed(() => {
  const user = auth.user.value
  if (!user) return false

  const hasPassword = user.auth_provider === 'password' || !!user.password_set_at
  const currentEmail = user.email ?? ''
  const nextEmail = profile.email ?? ''

  return hasPassword && nextEmail !== '' && nextEmail !== currentEmail
})

const validateProfile = (state: ProfileState): FormError[] => {
  const errors: FormError[] = []

  if (requiresPasswordForEmailChange.value && !state.current_password?.trim()) {
    errors.push({
      name: 'current_password',
      message: t('settings.general.password_required'),
    })
  }

  return errors
}

const extractErrorMessage = (error: unknown) => {
  const fallback = t('settings.general.error_fallback')
  if (!error || typeof error !== 'object') {
    return fallback
  }

  const errorObject = error as {
    data?: { message?: unknown; errors?: Record<string, unknown> }
    response?: { _data?: { message?: unknown; errors?: Record<string, unknown> } }
    message?: unknown
  }

  const data = errorObject.data ?? errorObject.response?._data
  const errors = data?.errors

  if (errors && typeof errors === 'object') {
    const firstError = Object.values(errors).flat()[0]
    if (typeof firstError === 'string') {
      return firstError
    }
  }

  if (typeof data?.message === 'string') {
    return data.message
  }

  return typeof errorObject.message === 'string' ? errorObject.message : fallback
}

const syncFromUser = () => {
  const user = auth.user.value
  profile.name = user?.name ?? ''
  profile.email = user?.email ?? ''
  profile.current_password = ''
}

onMounted(async () => {
  try {
    await auth.fetchUser(true)
    syncFromUser()
  } finally {
    isLoading.value = false
  }
})

async function onSubmit(event: FormSubmitEvent<ProfileSchema>) {
  if (isSubmitting.value) {
    return
  }

  isSubmitting.value = true
  try {
    const user = await auth.updateProfile({
      name: event.data.name,
      email: event.data.email,
      current_password: requiresPasswordForEmailChange.value ? profile.current_password : undefined,
    })

    toast.add({
      title: t('settings.general.toast_success_title'),
      description: t('settings.general.toast_success_description'),
      icon: 'i-lucide-check',
      color: 'success'
    })

    // If the email changed, Fortify will set email_verified_at = null and send a new verification email.
    if (!user?.email_verified_at) {
      toast.add({
        title: t('settings.general.toast_verify_title'),
        description: t('settings.general.toast_verify_description'),
      })
      await router.push(`/auth/verify-email?redirect=${encodeURIComponent('/dashboard/settings')}`)
      return
    }

    profile.current_password = ''
  } catch (error) {
    toast.add({
      title: t('settings.general.toast_error_title'),
      description: extractErrorMessage(error),
      color: 'error',
    })
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <UForm
    id="settings"
    :schema="profileSchema"
    :state="profile"
    :validate="validateProfile"
    @submit="onSubmit"
  >
    <UPageCard
      :title="t('settings.general.title')"
      :description="t('settings.general.description')"
      variant="naked"
      orientation="horizontal"
      class="mb-4"
    >
      <UButton
        form="settings"
        :label="t('actions.save_changes')"
        color="neutral"
        :loading="isSubmitting || isLoading"
        type="submit"
        class="w-fit lg:ms-auto"
      />
    </UPageCard>

    <UPageCard variant="subtle">
      <UFormField
        name="name"
        :label="t('settings.general.name_label')"
        :description="t('settings.general.name_description')"
        required
        class="flex max-sm:flex-col justify-between items-start gap-4"
      >
        <UInput
          v-model="profile.name"
          autocomplete="off"
          :disabled="isLoading"
        />
      </UFormField>
      <USeparator />
      <UFormField
        name="email"
        :label="t('settings.general.email_label')"
        :description="t('settings.general.email_description')"
        required
        class="flex max-sm:flex-col justify-between items-start gap-4"
      >
        <UInput
          v-model="profile.email"
          type="email"
          autocomplete="off"
          :disabled="isLoading"
        />
      </UFormField>

      <USeparator v-if="requiresPasswordForEmailChange" />
      <UFormField
        v-if="requiresPasswordForEmailChange"
        name="current_password"
        :label="t('settings.general.confirm_password_label')"
        :description="t('settings.general.confirm_password_description')"
        required
        class="flex max-sm:flex-col justify-between items-start gap-4"
      >
        <UInput
          v-model="profile.current_password"
          type="password"
          autocomplete="current-password"
          :disabled="isLoading"
        />
      </UFormField>
    </UPageCard>
  </UForm>
</template>
