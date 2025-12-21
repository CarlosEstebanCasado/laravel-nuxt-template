<script setup lang="ts">
definePageMeta({
  layout: 'auth',
  middleware: 'auth'
})

useSeoMeta({
  title: 'Email verified',
  description: 'Your email address has been verified'
})

const auth = useAuth()
const route = useRoute()
const router = useRouter()
const toast = useToast()

const redirectTo = computed(() => {
  const redirect = route.query.redirect
  return typeof redirect === 'string' ? redirect : '/dashboard'
})

onMounted(async () => {
  try {
    await auth.fetchUser(true)
    toast.add({
      title: 'Email verified',
      description: 'Thanks! Your account is now active.'
    })
    await router.replace(redirectTo.value)
  } catch {
    // If something goes wrong, let the user navigate manually.
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
        Verifying your session…
      </h2>
      <p class="text-sm text-neutral-500">
        Redirecting you to the app.
      </p>
    </div>
  </div>
</template>


