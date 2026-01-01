<script setup lang="ts">
import * as z from 'zod'
import type { FormSubmitEvent } from '@nuxt/ui'

defineI18nRoute(false)

definePageMeta({
  layout: 'auth',
  middleware: 'guest'
})

const { t } = useI18n()

useSeoMeta({
  title: t('auth.reset.seo_title'),
  description: t('auth.reset.seo_description')
})

const route = useRoute()
const router = useRouter()
const toast = useToast()
const auth = useAuth()

const token = computed(() => typeof route.params.token === 'string' ? route.params.token : '')
const initialEmail = typeof route.query.email === 'string' ? route.query.email : ''

const isSubmitting = ref(false)
const isCompleted = ref(false)

const schema = z.object({
  email: z.string().email(t('messages.validation.invalid_email')),
  password: z.string().min(8, t('messages.validation.password_min')),
  password_confirmation: z.string().min(8, t('messages.validation.password_min'))
}).superRefine((data, ctx) => {
  if (data.password !== data.password_confirmation) {
    ctx.addIssue({
      code: z.ZodIssueCode.custom,
      message: t('messages.validation.passwords_mismatch'),
      path: ['password_confirmation']
    })
  }
})

type Schema = z.output<typeof schema>

const fields = computed(() => [{
  name: 'email',
  label: t('auth.fields.email_label'),
  type: 'text' as const,
  placeholder: t('auth.fields.email_placeholder'),
  autocomplete: 'email',
  required: true
}, {
  name: 'password',
  label: t('auth.fields.password_label'),
  type: 'password' as const,
  placeholder: t('auth.fields.password_placeholder'),
  autocomplete: 'new-password',
  required: true
}, {
  name: 'password_confirmation',
  label: t('auth.fields.password_confirmation_label'),
  type: 'password' as const,
  placeholder: t('auth.fields.password_confirmation_placeholder'),
  autocomplete: 'new-password',
  required: true
}])

const formState = reactive({
  email: initialEmail,
  password: '',
  password_confirmation: ''
})

async function onSubmit(event: FormSubmitEvent<Schema>) {
  if (isSubmitting.value || !token.value) {
    if (!token.value) {
      toast.add({
        title: t('auth.reset.invalid_link_title'),
        description: t('auth.reset.invalid_link_description'),
        color: 'error'
      })
      router.push('/forgot-password')
    }
    return
  }

  isSubmitting.value = true
  try {
    await auth.resetPassword({
      token: token.value,
      email: event.data.email,
      password: event.data.password,
      password_confirmation: event.data.password_confirmation
    })

    isCompleted.value = true
    toast.add({
      title: t('auth.reset.toast_success_title'),
      description: t('auth.reset.toast_success_description')
    })
  } catch (error: any) {
    const message = error?.data?.message || error?.message || t('auth.reset.toast_error_description')
    toast.add({
      title: t('auth.reset.toast_error_title'),
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
      :state="formState"
      :loading="isSubmitting"
      :title="t('auth.reset.title')"
      icon="i-lucide-lock"
      :submit="{ label: t('auth.reset.submit') }"
      @submit="onSubmit"
    >
      <template #description>
        {{ t('auth.reset.description') }}
      </template>

      <template #footer>
        {{ t('auth.reset.remembered') }}
        <ULink
          to="/login"
          class="text-primary font-medium"
        >{{ t('auth.forgot.return_login') }}</ULink>.
      </template>
    </UAuthForm>

    <UPageCard
      v-else
      :title="t('auth.reset.toast_success_title')"
      icon="i-lucide-lock-check"
      class="text-center"
    >
      <p class="text-sm text-muted">
        {{ t('auth.reset.completed_message') }}
      </p>

      <UButton
        class="mt-6"
        :label="t('auth.reset.go_login')"
        color="neutral"
        block
        @click="router.push('/login')"
      />
    </UPageCard>
  </div>
</template>
