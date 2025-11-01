export default defineNuxtRouteMiddleware(async (to) => {
  const auth = useAuth()

  try {
    await auth.fetchUser()
  } catch (error) {
    console.warn('[auth middleware] Failed to fetch user session', error)
  }

  if (auth.isAuthenticated.value) {
    return
  }

  const redirect = to.fullPath && to.fullPath !== '/login'
    ? `?redirect=${encodeURIComponent(to.fullPath)}`
    : ''

  return navigateTo(`/login${redirect}`)
})
