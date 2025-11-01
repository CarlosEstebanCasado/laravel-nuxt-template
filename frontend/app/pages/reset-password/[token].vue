<script setup lang="ts">
import * as z from 'zod'
import type { FormSubmitEvent } from '@nuxt/ui'

definePageMeta({
  layout: 'auth',
  middleware: 'guest'
})

useSeoMeta({
  title: 'Reset password',
  description: 'Set a new password for your account'
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
  email: z.string().email('Invalid email'),
  password: z.string().min(8, 'Must be at least 8 characters'),
  password_confirmation: z.string().min(8, 'Must be at least 8 characters')
}).superRefine((data, ctx) => {
  if (data.password !== data.password_confirmation) {
    ctx.addIssue({
      code: z.ZodIssueCode.custom,
      message: 'Passwords do not match',
      path: ['password_confirmation']
    })
  }
})

type Schema = z.output<typeof schema>

const fields = [{
  name: 'email',
  label: 'Email',
  type: 'text' as const,
  placeholder: 'Enter your email',
  autocomplete: 'email',
  required: true
}, {
  name: 'password',
  label: 'New password',
  type: 'password' as const,
  placeholder: 'Enter your new password',
  autocomplete: 'new-password',
  required: true
}, {
  name: 'password_confirmation',
  label: 'Confirm password',
  type: 'password' as const,
  placeholder: 'Re-enter your new password',
  autocomplete: 'new-password',
  required: true
}]

const formState = reactive({
  email: initialEmail,
  password: '',
  password_confirmation: ''
})

async function onSubmit(event: FormSubmitEvent<Schema>) {
  if (isSubmitting.value || !token.value) {
    if (!token.value) {
      toast.add({
        title: 'Reset link invalid',
        description: 'Please request a new password reset link.',
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
      title: 'Password updated',
      description: 'You can now sign in with your new password.'
    })
  } catch (error: any) {
    const message = error?.data?.message || error?.message || 'Unable to reset password, please try again.'
    toast.add({
      title: 'Reset failed',
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
      title="Reset password"
      icon="i-lucide-lock"
      :submit="{ label: 'Update password' }"
      @submit="onSubmit"
    >
      <template #description>
        Choose a new password for your account. Make sure it's strong and unique.
      </template>

      <template #footer>
        Remembered your password? <ULink
          to="/login"
          class="text-primary font-medium"
        >Return to login</ULink>.
      </template>
    </UAuthForm>

    <UPageCard
      v-else
      title="Password updated"
      icon="i-lucide-lock-check"
      class="text-center"
    >
      <p class="text-sm text-muted">
        Your password has been changed. You can now use it to sign in.
      </p>

      <UButton
        class="mt-6"
        label="Go to login"
        color="neutral"
        block
        @click="router.push('/login')"
      />
    </UPageCard>
  </div>
</template>
