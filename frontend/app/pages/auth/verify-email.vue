<script setup lang="ts">
defineI18nRoute(false)

definePageMeta({
  layout: 'auth',
  middleware: 'auth'
})

const { t } = useI18n()

useSeoMeta({
  title: t('auth.verify.seo_title'),
  description: t('auth.verify.seo_description')
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
    const message = error?.data?.message || error?.message || t('auth.verify.toast_refresh_error')
    toast.add({
      title: t('auth.verify.toast_refresh_error'),
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
      title: t('auth.verify.toast_resend_success'),
      description: t('auth.verify.toast_resend_description')
    })
  } catch (error: any) {
    const message = error?.data?.message || error?.message || t('auth.verify.toast_resend_error')
    toast.add({
      title: t('auth.verify.toast_resend_error'),
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
    :title="t('auth.verify.title')"
    icon="i-lucide-mail-check"
    class="text-center"
  >
    <p class="text-sm text-muted">
      {{ t('auth.verify.message', { email: userEmail || t('auth.verify.fallback_email') }) }}
    </p>

    <div class="mt-6 flex flex-col gap-2">
      <UButton
        :label="t('auth.verify.cta_verified')"
        color="primary"
        block
        :loading="isSubmitting"
        @click="refreshUser"
      />
      <UButton
        :label="t('auth.verify.cta_resend')"
        color="neutral"
        variant="outline"
        block
        :loading="isSubmitting"
        @click="resend"
      />
      <UButton
        :label="t('auth.verify.cta_logout')"
        color="neutral"
        variant="ghost"
        block
        :disabled="isSubmitting"
        @click="auth.logout().then(() => router.replace('/login'))"
      />
    </div>
  </UPageCard>
</template>
