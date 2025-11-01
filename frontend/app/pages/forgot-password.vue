<script setup lang="ts">
import * as z from 'zod'
import type { FormSubmitEvent } from '@nuxt/ui'

definePageMeta({
  layout: 'auth',
  middleware: 'guest'
})

useSeoMeta({
  title: 'Forgot password',
  description: 'Request a password reset link'
})

const toast = useToast()
const auth = useAuth()
const router = useRouter()

const isSubmitting = ref(false)
const isCompleted = ref(false)

const schema = z.object({
  email: z.string().email('Invalid email')
})

type Schema = z.output<typeof schema>

const fields = [{
  name: 'email',
  type: 'text' as const,
  label: 'Email',
  placeholder: 'Enter your email',
  autocomplete: 'email',
  required: true
}]

async function onSubmit(event: FormSubmitEvent<Schema>) {
  if (isSubmitting.value || isCompleted.value) {
    return
  }

  isSubmitting.value = true
  try {
    await auth.requestPasswordReset({ email: event.data.email })

    isCompleted.value = true
    toast.add({
      title: 'Email sent',
      description: 'If your email exists in our records, you will receive a reset link shortly.'
    })
  } catch (error: any) {
    const message = error?.data?.message || error?.message || 'Unable to send reset link, please try again.'
    toast.add({
      title: 'Request failed',
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
      title="Forgot password"
      icon="i-lucide-mail"
      :submit="{ label: 'Send reset link' }"
      @submit="onSubmit"
    >
      <template #description>
        Enter the email associated with your account and we'll send you instructions to reset your password.
      </template>

      <template #footer>
        Remembered it? <ULink
          to="/login"
          class="text-primary font-medium"
        >Return to login</ULink>.
      </template>
    </UAuthForm>

    <UPageCard
      v-else
      title="Check your inbox"
      icon="i-lucide-mail-check"
      class="text-center"
    >
      <p class="text-sm text-muted">
        If the email you provided is registered, you'll receive a message with a reset link.
      </p>

      <div class="mt-6 flex flex-col gap-2">
        <UButton
          label="Return to login"
          color="neutral"
          variant="outline"
          block
          @click="router.push('/login')"
        />
        <UButton
          label="Resend email"
          color="neutral"
          variant="ghost"
          block
          @click="isCompleted = false"
        />
      </div>
    </UPageCard>
  </div>
</template>
