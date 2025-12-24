<script setup lang="ts">
definePageMeta({
  layout: 'auth',
  middleware: 'auth'
})

useSeoMeta({
  title: 'Verify your email',
  description: 'Verify your email address to continue'
})

const auth = useAuth()
const route = useRoute()
const router = useRouter()
const toast = useToast()

const isSubmitting = ref(false)

const userEmail = computed(() => auth.user.value?.email || '')

const redirectTo = computed(() => {
  const redirect = route.query.redirect
  return typeof redirect === 'string' ? redirect : '/dashboard'
})

const refreshUser = async () => {
  if (isSubmitting.value) {
    return
  }

  isSubmitting.value = true
  try {
    await auth.fetchUser(true)
    if (auth.user.value?.email_verified_at) {
      await router.replace(redirectTo.value)
    }
  } catch (error: any) {
    const message = error?.data?.message || error?.message || 'Unable to refresh your account status, please try again.'
    toast.add({
      title: 'Request failed',
      description: message,
      color: 'error'
    })
  } finally {
    isSubmitting.value = false
  }
}

const resend = async () => {
  if (isSubmitting.value) {
    return
  }

  isSubmitting.value = true
  try {
    await auth.resendEmailVerification()
    toast.add({
      title: 'Verification email sent',
      description: 'Please check your inbox (and spam folder).'
    })
  } catch (error: any) {
    const message = error?.data?.message || error?.message || 'Unable to send verification email, please try again.'
    toast.add({
      title: 'Request failed',
      description: message,
      color: 'error'
    })
  } finally {
    isSubmitting.value = false
  }
}

onMounted(async () => {
  try {
    await auth.fetchUser(true)
    if (auth.user.value?.email_verified_at) {
      await router.replace(redirectTo.value)
    }
  } catch {
    // If fetch fails, auth middleware will already handle redirecting.
  }
})
</script>

<template>
  <UPageCard
    title="Verify your email"
    icon="i-lucide-mail-check"
    class="text-center"
  >
    <p class="text-sm text-muted">
      We've sent a verification link to
      <span class="font-medium">{{ userEmail || 'your email address' }}</span>.
      Please open it to activate your account.
    </p>

    <div class="mt-6 flex flex-col gap-2">
      <UButton
        label="I've verified my email"
        color="primary"
        block
        :loading="isSubmitting"
        @click="refreshUser"
      />
      <UButton
        label="Resend verification email"
        color="neutral"
        variant="outline"
        block
        :loading="isSubmitting"
        @click="resend"
      />
      <UButton
        label="Logout"
        color="neutral"
        variant="ghost"
        block
        :disabled="isSubmitting"
        @click="auth.logout().then(() => router.replace('/login'))"
      />
    </div>
  </UPageCard>
</template>


