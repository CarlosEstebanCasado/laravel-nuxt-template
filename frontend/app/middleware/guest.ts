export default defineNuxtRouteMiddleware(async (to) => {
  const auth = useAuth()

  await auth.fetchUser()

  if (!auth.isAuthenticated.value) {
    return
  }

  const redirect = typeof to.query.redirect === 'string' ? to.query.redirect : '/dashboard'

  return navigateTo(redirect, { replace: true })
})
