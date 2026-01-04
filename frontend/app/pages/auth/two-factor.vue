<script setup lang="ts">
import type { FormError, FormSubmitEvent } from '@nuxt/ui'

defineI18nRoute(false)

definePageMeta({
  layout: 'auth',
  middleware: 'guest'
})

const auth = useAuth()
const toast = useToast()
const { t } = useI18n()
const route = useRoute()
const router = useRouter()

const method = ref<'code' | 'recovery'>('code')
const state = reactive({
  code: '',
  recovery_code: ''
})
const isSubmitting = ref(false)

const methodOptions = computed(() => [
  { label: t('auth.two_factor.method_app'), value: 'code' },
  { label: t('auth.two_factor.method_recovery'), value: 'recovery' }
])

const validate = (value: typeof state): FormError[] => {
  const errors: FormError[] = []
  if (method.value === 'code' && !value.code) {
    errors.push({ name: 'code', message: t('messages.validation.required') })
  }
  if (method.value === 'recovery' && !value.recovery_code) {
    errors.push({ name: 'recovery_code', message: t('messages.validation.required') })
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
  return (error as any)?.message || t('auth.two_factor.error_generic')
}

async function onSubmit(event: FormSubmitEvent<typeof state>) {
  if (isSubmitting.value) {
    return
  }

  const payload = method.value === 'code'
    ? { code: event.data.code }
    : { recovery_code: event.data.recovery_code }

  isSubmitting.value = true
  try {
    const user = await auth.completeTwoFactorLogin(payload)

    toast.add({
      title: t('auth.two_factor.toast_success_title'),
      description: t('auth.two_factor.toast_success_description'),
      color: 'success',
      icon: 'i-lucide-check'
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
      title: t('auth.two_factor.toast_error_title'),
      description: extractErrorMessage(error),
      color: 'error',
    })
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <div class="space-y-2">
      <h1 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
        {{ t('auth.two_factor.title') }}
      </h1>
      <p class="text-sm text-slate-600 dark:text-slate-400">
        {{ t('auth.two_factor.description') }}
      </p>
    </div>

    <UForm
      :state="state"
      :validate="validate"
      class="space-y-4"
      @submit="onSubmit"
    >
      <UFormField name="method">
        <USelect
          v-model="method"
          :items="methodOptions"
          value-key="value"
          label-key="label"
          class="w-full"
        />
      </UFormField>

      <UFormField v-if="method === 'code'" name="code">
        <UInput
          v-model="state.code"
          inputmode="numeric"
          autocomplete="one-time-code"
          :placeholder="t('auth.two_factor.code_placeholder')"
        />
      </UFormField>

      <UFormField v-else name="recovery_code">
        <UInput
          v-model="state.recovery_code"
          autocomplete="one-time-code"
          :placeholder="t('auth.two_factor.recovery_placeholder')"
        />
      </UFormField>

      <UButton
        type="submit"
        :label="t('auth.two_factor.submit')"
        :loading="isSubmitting"
      />
    </UForm>

    <div class="text-sm text-slate-600 dark:text-slate-400">
      <ULink to="/login" class="text-primary font-medium">
        {{ t('auth.two_factor.back_to_login') }}
      </ULink>
    </div>
  </div>
</template>
