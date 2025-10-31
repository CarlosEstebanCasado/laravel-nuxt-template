import { createSharedComposable } from '@vueuse/core'

const _useDashboard = () => {
  const route = useRoute()
  const router = useRouter()
  const isNotificationsSlideoverOpen = ref(false)

  const base = '/dashboard'

  defineShortcuts({
    'g-h': () => router.push(base),
    'g-i': () => router.push(`${base}/inbox`),
    'g-c': () => router.push(`${base}/customers`),
    'g-s': () => router.push(`${base}/settings`),
    'n': () => {
      isNotificationsSlideoverOpen.value = !isNotificationsSlideoverOpen.value
    }
  })

  watch(() => route.fullPath, () => {
    isNotificationsSlideoverOpen.value = false
  })

  return {
    isNotificationsSlideoverOpen
  }
}

export const useDashboard = createSharedComposable(_useDashboard)
