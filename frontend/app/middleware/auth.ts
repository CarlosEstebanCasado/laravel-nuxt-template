export default defineNuxtRouteMiddleware(async (to) => {
  const auth = useAuth()

  await auth.fetchUser()

  if (auth.isAuthenticated.value) {
    return
  }

  const redirect = to.fullPath && to.fullPath !== '/login'
    ? `?redirect=${encodeURIComponent(to.fullPath)}`
    : ''

  return navigateTo(`/login${redirect}`)
})
