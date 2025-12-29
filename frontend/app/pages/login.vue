<script setup lang="ts">
import * as z from 'zod'
import type { FormSubmitEvent } from '@nuxt/ui'

definePageMeta({
  layout: 'auth',
  middleware: 'guest'
})

const { t } = useI18n()

useSeoMeta({
  title: t('auth.login.seo_title'),
  description: t('auth.login.seo_description')
})

const toast = useToast()
const auth = useAuth()
const router = useRouter()
const route = useRoute()
const isSubmitting = ref(false)

const fields = computed(() => [{
  name: 'email',
  type: 'text' as const,
  label: t('auth.fields.email_label'),
  placeholder: t('auth.fields.email_placeholder'),
  required: true
}, {
  name: 'password',
  label: t('auth.fields.password_label'),
  type: 'password' as const,
  placeholder: t('auth.fields.password_placeholder')
}, {
  name: 'remember',
  label: t('auth.fields.remember_me'),
  type: 'checkbox' as const
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

const schema = z.object({
  remember: z.boolean().optional(),
  email: z.string().email(t('messages.validation.invalid_email')),
  password: z.string().min(8, t('messages.validation.password_min'))
})

type Schema = z.output<typeof schema>

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

  return t('auth.login.error_generic')
}

async function onSubmit(event: FormSubmitEvent<Schema>) {
  if (isSubmitting.value) {
    return
  }

  isSubmitting.value = true

  try {
    const user = await auth.login(event.data)

    toast.add({
      title: t('auth.login.toast_success_title'),
      description: t('auth.login.toast_success_description'),
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
      title: t('auth.login.toast_error_title'),
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
    :title="t('auth.login.title')"
    icon="i-lucide-lock"
    @submit="onSubmit"
  >
    <template #description>
      {{ t('auth.login.cta_text') }}
      <ULink
        to="/signup"
        class="text-primary font-medium"
      >{{ t('auth.login.cta_action') }}</ULink>.
    </template>

    <template #password-hint>
      <ULink
        to="/forgot-password"
        class="text-primary font-medium"
        tabindex="-1"
      >{{ t('auth.login.forgot') }}</ULink>
    </template>

    <template #footer>
      {{ t('auth.login.footer_text') }}
      <ULink
        to="/"
        class="text-primary font-medium"
      >{{ t('auth.login.footer_link') }}</ULink>.
    </template>
  </UAuthForm>
</template>
