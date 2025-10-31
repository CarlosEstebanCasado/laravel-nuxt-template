<script setup lang="ts">
definePageMeta({
  layout: 'auth',
})

useSeoMeta({
  title: 'Connecting your account',
})

const route = useRoute()
const router = useRouter()
const toast = useToast()
const auth = useAuth()

const provider = computed(
  () => (route.query.provider as string | undefined) ?? 'provider'
)
const status = computed(() => route.query.status as string | undefined)
const errorCode = computed(() => route.query.error as string | undefined)

const isProcessing = ref(true)

const readableProvider = computed(() => {
  const value = provider.value
  if (!value) {
    return 'your account'
  }

  return value.charAt(0).toUpperCase() + value.slice(1)
})

const resolveErrorMessage = () => {
  if (!errorCode.value) {
    return 'Authentication failed. Please try again.'
  }

  switch (errorCode.value) {
    case 'email_missing':
      return 'Your provider account did not return an email address. Please ensure your email is publicly available and try again.'
    default:
      return 'Authentication failed. Please try again.'
  }
}

const finish = (path: string) => {
  setTimeout(() => {
    router.replace(path)
  }, 800)
}

onMounted(async () => {
  if (status.value !== 'success') {
    toast.add({
      title: 'Sign in failed',
      description: resolveErrorMessage(),
      color: 'error',
    })
    isProcessing.value = false
    finish('/login')
    return
  }

  try {
    await auth.fetchUser(true)

    toast.add({
      title: 'Signed in successfully',
      description: `Welcome back!`,
    })

    finish('/dashboard')
  } catch (error) {
    toast.add({
      title: 'Sign in failed',
      description: resolveErrorMessage(),
      color: 'error',
    })
    finish('/login')
  } finally {
    isProcessing.value = false
  }
})
</script>

<template>
  <div class="flex flex-col items-center gap-4 py-6 text-center">
    <UIcon
      name="i-lucide-loader-circle"
      class="h-10 w-10 text-primary animate-spin"
    />

    <div>
      <h2 class="text-lg font-semibold">
        Finishing {{ readableProvider }} sign-in
      </h2>
      <p class="text-sm text-neutral-500">
        Please wait while we complete the authentication process.
      </p>
    </div>

    <div
      v-if="!isProcessing"
      class="text-xs text-neutral-400"
    >
      Redirecting…
    </div>
  </div>
</template>
