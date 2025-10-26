export default defineNuxtRouteMiddleware(async (to) => {
  if (process.server) {
    return
  }

  const { user, fetchUser } = useAuth()

  if (!user.value) {
    await fetchUser({ silent: true }).catch(() => null)
  }

  if (!user.value) {
    return navigateTo('/auth/login')
  }

  if (!user.value.email_verified_at && to.path !== '/auth/verify-email') {
    return navigateTo('/auth/verify-email')
  }
})
