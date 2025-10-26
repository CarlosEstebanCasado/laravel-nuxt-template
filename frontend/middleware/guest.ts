export default defineNuxtRouteMiddleware(async () => {
  if (process.server) {
    return
  }

  const { user, fetchUser } = useAuth()

  if (!user.value) {
    await fetchUser({ silent: true }).catch(() => null)
  }

  if (user.value?.email_verified_at) {
    return navigateTo('/dashboard')
  }

  if (user.value && !user.value.email_verified_at) {
    return navigateTo('/auth/verify-email')
  }
})
