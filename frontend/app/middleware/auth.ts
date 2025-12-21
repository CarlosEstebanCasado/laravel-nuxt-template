export default defineNuxtRouteMiddleware(async (to) => {
  const auth = useAuth()

  try {
    await auth.fetchUser()
  } catch (error) {
    console.warn('[auth middleware] Failed to fetch user session', error)
  }

  if (auth.isAuthenticated.value) {
    const allowedUnverifiedPaths = new Set([
      '/auth/verify-email',
      '/auth/email-verified',
      '/auth/callback',
    ])

    const isVerified = Boolean(auth.user.value?.email_verified_at)
    if (!isVerified && !allowedUnverifiedPaths.has(to.path)) {
      const redirect = to.fullPath
        ? `?redirect=${encodeURIComponent(to.fullPath)}`
        : ''
      return navigateTo(`/auth/verify-email${redirect}`)
    }

    return
  }

  const redirect = to.fullPath && to.fullPath !== '/login'
    ? `?redirect=${encodeURIComponent(to.fullPath)}`
    : ''

  return navigateTo(`/login${redirect}`)
})
