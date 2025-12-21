<script setup lang="ts">
import * as z from 'zod'
import type { FormSubmitEvent } from '@nuxt/ui'

definePageMeta({
  layout: 'auth',
  middleware: 'guest'
})

useSeoMeta({
  title: 'Login',
  description: 'Login to your account to continue'
})

const toast = useToast()
const auth = useAuth()
const router = useRouter()
const route = useRoute()
const isSubmitting = ref(false)

const fields = [{
  name: 'email',
  type: 'text' as const,
  label: 'Email',
  placeholder: 'Enter your email',
  required: true
}, {
  name: 'password',
  label: 'Password',
  type: 'password' as const,
  placeholder: 'Enter your password'
}, {
  name: 'remember',
  label: 'Remember me',
  type: 'checkbox' as const
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
  remember: z.boolean().optional(),
  email: z.string().email('Invalid email'),
  password: z.string().min(8, 'Must be at least 8 characters')
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

  return 'Unable to sign in, please try again.'
}

async function onSubmit(event: FormSubmitEvent<Schema>) {
  if (isSubmitting.value) {
    return
  }

  isSubmitting.value = true

  try {
    const user = await auth.login(event.data)

    toast.add({
      title: 'Welcome back!',
      description: 'You are now signed in.',
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
      title: 'Unable to sign in',
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
    title="Welcome back"
    icon="i-lucide-lock"
    @submit="onSubmit"
  >
    <template #description>
      Don't have an account? <ULink
        to="/signup"
        class="text-primary font-medium"
      >Sign up</ULink>.
    </template>

    <template #password-hint>
      <ULink
        to="/forgot-password"
        class="text-primary font-medium"
        tabindex="-1"
      >Forgot password?</ULink>
    </template>

    <template #footer>
      By signing in, you agree to our <ULink
        to="/"
        class="text-primary font-medium"
      >Terms of Service</ULink>.
    </template>
  </UAuthForm>
</template>
