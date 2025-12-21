<script setup lang="ts">
import * as z from 'zod'
import type { FormSubmitEvent } from '@nuxt/ui'

definePageMeta({
  layout: 'auth',
  middleware: 'guest'
})

useSeoMeta({
  title: 'Sign up',
  description: 'Create an account to get started'
})

const toast = useToast()
const auth = useAuth()
const router = useRouter()
const route = useRoute()
const isSubmitting = ref(false)

const fields = [{
  name: 'name',
  type: 'text' as const,
  label: 'Name',
  placeholder: 'Enter your name'
}, {
  name: 'email',
  type: 'text' as const,
  label: 'Email',
  placeholder: 'Enter your email'
}, {
  name: 'password',
  label: 'Password',
  type: 'password' as const,
  placeholder: 'Enter your password'
}, {
  name: 'password_confirmation',
  label: 'Confirm Password',
  type: 'password' as const,
  placeholder: 'Confirm your password'
}]

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
  name: z.string().min(1, 'Name is required'),
  email: z.string().email('Invalid email'),
  password: z.string().min(8, 'Must be at least 8 characters'),
  password_confirmation: z.string().min(8, 'Must be at least 8 characters')
}).refine((data) => data.password === data.password_confirmation, {
  message: 'Passwords do not match',
  path: ['password_confirmation'],
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

  return 'Unable to create your account, please try again.'
}

async function onSubmit(event: FormSubmitEvent<Schema>) {
  if (isSubmitting.value) {
    return
  }

  isSubmitting.value = true

  try {
    const user = await auth.register(event.data)

    toast.add({
      title: 'Account created',
      description: 'Welcome aboard!',
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
      title: 'Sign up failed',
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
    title="Create an account"
    :submit="{ label: 'Create account' }"
    @submit="onSubmit"
  >
    <template #description>
      Already have an account? <ULink
        to="/login"
        class="text-primary font-medium"
      >Login</ULink>.
    </template>

    <template #footer>
      By signing up, you agree to our <ULink
        to="/"
        class="text-primary font-medium"
      >Terms of Service</ULink>.
    </template>
  </UAuthForm>
</template>
