<script setup lang="ts">
import * as z from 'zod'
import type { FormSubmitEvent } from '@nuxt/ui'

definePageMeta({
  layout: 'auth',
  middleware: 'guest'
})

const { t } = useI18n()

useSeoMeta({
  title: t('auth.signup.seo_title'),
  description: t('auth.signup.seo_description')
})

const toast = useToast()
const auth = useAuth()
const router = useRouter()
const route = useRoute()
const isSubmitting = ref(false)

const fields = computed(() => [{
  name: 'name',
  type: 'text' as const,
  label: t('auth.fields.name_label'),
  placeholder: t('auth.fields.name_placeholder')
}, {
  name: 'email',
  type: 'text' as const,
  label: t('auth.fields.email_label'),
  placeholder: t('auth.fields.email_placeholder')
}, {
  name: 'password',
  label: t('auth.fields.password_label'),
  type: 'password' as const,
  placeholder: t('auth.fields.password_placeholder')
}, {
  name: 'password_confirmation',
  label: t('auth.fields.password_confirmation_label'),
  type: 'password' as const,
  placeholder: t('auth.fields.password_confirmation_placeholder')
}])

const providers = [{
  label: 'Google',
  icon: 'i-simple-icons-google',
  onClick: () => handleProvider('google')
}, {
  label: 'GitHub',
  icon: 'i-simple-icons-github',
  onClick: () => handleProvider('github')
}]

const requiredField = () => t('messages.validation.required')
const requiredString = (message: string) =>
  z.preprocess(
    (value) => (typeof value === 'string' ? value : ''),
    z.string().min(1, message)
  )
const requiredEmail = (message: string) =>
  z.preprocess(
    (value) => (typeof value === 'string' ? value : ''),
    z.string().min(1, requiredField()).email(message)
  )

const schema = z.object({
  name: requiredString(requiredField()),
  email: requiredEmail(t('messages.validation.invalid_email')),
  password: z.preprocess(
    (value) => (typeof value === 'string' ? value : ''),
    z.string().min(8, t('messages.validation.password_min'))
  ),
  password_confirmation: z.preprocess(
    (value) => (typeof value === 'string' ? value : ''),
    z.string().min(8, t('messages.validation.password_min'))
  )
}).refine((data) => data.password === data.password_confirmation, {
  message: t('messages.validation.passwords_mismatch'),
  path: ['password_confirmation'],
})

const handleProvider = (provider: 'google' | 'github') => {
  auth.loginWithProvider(provider)
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

  if ((error as any)?.message) {
    return (error as any).message
  }

  return t('auth.signup.toast_error_title')
}

async function onSubmit(event?: FormSubmitEvent<Record<string, unknown>>) {
  const data = event?.data ?? {}
  const payload = {
    name: String(data.name ?? ''),
    email: String(data.email ?? ''),
    password: String(data.password ?? ''),
    password_confirmation: String(data.password_confirmation ?? '')
  }
  if (isSubmitting.value) {
    return
  }

  isSubmitting.value = true

  try {
    const user = await auth.register(payload)

    toast.add({
      title: t('auth.signup.toast_success_title'),
      description: t('auth.signup.toast_success_description'),
    })

    const redirectTo = typeof route.query.redirect === 'string' ? route.query.redirect : '/dashboard'
    if (!user?.email_verified_at) {
      const qs = redirectTo ? `?redirect=${encodeURIComponent(redirectTo)}` : ''
      await router.push(`/auth/verify-email${qs}`)
      return
    }

    await router.push(redirectTo)
  } catch (error) {
    toast.add({
      title: t('auth.signup.toast_error_title'),
      description: extractErrorMessage(error),
      color: 'error',
    })
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <UAuthForm
    :fields="fields"
    :schema="schema"
    :providers="providers"
    :loading="isSubmitting"
    :title="t('auth.signup.title')"
    :submit="{ label: t('auth.signup.submit') }"
    @submit="onSubmit"
  >
    <template #description>
      {{ t('auth.signup.cta_text') }}
      <ULink
        to="/login"
        class="text-primary font-medium"
      >{{ t('auth.signup.cta_action') }}</ULink>.
    </template>

    <template #footer>
      {{ t('auth.signup.footer_text') }}
      <ULink
        to="/"
        class="text-primary font-medium"
      >{{ t('auth.signup.footer_link') }}</ULink>.
    </template>
  </UAuthForm>
</template>
