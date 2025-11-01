export default defineNuxtRouteMiddleware(async (to) => {
  const auth = useAuth()

  try {
    await auth.fetchUser()
  } catch (error) {
    console.warn('[guest middleware] Failed to fetch user session', error)
  }

  if (!auth.isAuthenticated.value) {
    return
  }

  const redirect = typeof to.query.redirect === 'string' ? to.query.redirect : '/dashboard'

  return navigateTo(redirect, { replace: true })
})
