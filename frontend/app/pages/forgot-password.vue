<script setup lang="ts">
import * as z from 'zod'
import type { FormSubmitEvent } from '@nuxt/ui'

definePageMeta({
  layout: 'auth',
  middleware: 'guest'
})

const { t } = useI18n()

useSeoMeta({
  title: t('auth.forgot.seo_title'),
  description: t('auth.forgot.seo_description')
})

const toast = useToast()
const auth = useAuth()
const router = useRouter()

const isSubmitting = ref(false)
const isCompleted = ref(false)

const requiredField = () => t('messages.validation.required')

const schema = z.object({
  email: z.preprocess(
    (value) => (typeof value === 'string' ? value : ''),
    z
      .string()
      .min(1, requiredField())
      .email(t('messages.validation.invalid_email'))
  )
})

const fields = computed(() => [{
  name: 'email',
  type: 'text' as const,
  label: t('auth.fields.email_label'),
  placeholder: t('auth.fields.email_placeholder'),
  autocomplete: 'email',
  required: true
}])

async function onSubmit(event?: FormSubmitEvent<Record<string, unknown>>) {
  const data = event?.data ?? {}
  const payload = {
    email: String(data.email ?? '')
  }

  if (isSubmitting.value || isCompleted.value) {
    return
  }

  isSubmitting.value = true
  try {
    await auth.requestPasswordReset(payload)

    isCompleted.value = true
    toast.add({
      title: t('auth.forgot.toast_success_title'),
      description: t('auth.forgot.toast_success_description')
    })
  } catch (error: any) {
    const message = error?.data?.message || error?.message || t('auth.forgot.toast_error_description')
    toast.add({
      title: t('auth.forgot.toast_error_title'),
      description: message,
      color: 'error'
    })
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div>
    <UAuthForm
      v-if="!isCompleted"
      :fields="fields"
      :schema="schema"
      :loading="isSubmitting"
      :title="t('auth.forgot.title')"
      icon="i-lucide-mail"
      :submit="{ label: t('auth.forgot.submit') }"
      @submit="onSubmit"
    >
      <template #description>
        {{ t('auth.forgot.description') }}
      </template>

      <template #footer>
        {{ t('auth.forgot.remembered') }}
        <ULink
          to="/login"
          class="text-primary font-medium"
        >{{ t('auth.forgot.return_login') }}</ULink>.
      </template>
    </UAuthForm>

    <UPageCard
      v-else
      :title="t('auth.forgot.completed_title')"
      icon="i-lucide-mail-check"
      class="text-center"
    >
      <p class="text-sm text-muted">
        {{ t('auth.forgot.completed_description') }}
      </p>

      <div class="mt-6 flex flex-col gap-2">
        <UButton
          :label="t('auth.forgot.return_login')"
          color="neutral"
          variant="outline"
          block
          @click="router.push('/login')"
        />
        <UButton
          :label="t('auth.forgot.resend')"
          color="neutral"
          variant="ghost"
          block
          @click="isCompleted = false"
        />
      </div>
    </UPageCard>
  </div>
</template>
