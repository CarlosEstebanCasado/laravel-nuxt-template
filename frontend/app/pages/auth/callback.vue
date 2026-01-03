<script setup lang="ts">
defineI18nRoute(false)

definePageMeta({
  layout: 'auth',
})

const { t } = useI18n()

useSeoMeta({
  title: t('auth.oauth.seo_title'),
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
    return t('auth.oauth.default_provider')
  }

  return value.charAt(0).toUpperCase() + value.slice(1)
})

const resolveErrorMessage = () => {
  if (!errorCode.value) {
    return t('auth.oauth.error_generic')
  }

  switch (errorCode.value) {
    case 'email_missing':
      return t('auth.oauth.error_missing_email')
    default:
      return t('auth.oauth.error_generic')
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
      title: t('auth.oauth.error_title'),
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
      title: t('auth.oauth.success_title'),
      description: t('auth.oauth.success_description'),
    })

    finish('/dashboard')
  } catch (_error) {
    toast.add({
      title: t('auth.oauth.error_title'),
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
        {{ t('auth.oauth.heading', { provider: readableProvider }) }}
      </h2>
      <p class="text-sm text-neutral-500">
        {{ t('auth.oauth.description') }}
      </p>
    </div>

    <div
      v-if="!isProcessing"
      class="text-xs text-neutral-400"
    >
      {{ t('auth.oauth.redirecting') }}
    </div>
  </div>
</template>
