export default defineNuxtRouteMiddleware((to) => {
  const config = useRuntimeConfig()
  const appBaseUrl = config.public.appBaseUrl
  const siteBaseUrl = config.public.siteBaseUrl

  if (!appBaseUrl || !siteBaseUrl) {
    return
  }

  const requestUrl = import.meta.server ? useRequestURL() : new URL(window.location.href)
  const currentHost = requestUrl.host
  const appHost = new URL(appBaseUrl).host
  const siteHost = new URL(siteBaseUrl).host

  if (currentHost !== appHost && currentHost !== siteHost) {
    return
  }

  const localeCodes = ['es', 'en', 'ca']
  const localePattern = new RegExp(`^/(${localeCodes.join('|')})(/|$)`)
  const localeCookie = useCookie<string | null>('i18n_redirected')
  const preferredLocale = localeCodes.includes(localeCookie.value || '')
    ? (localeCookie.value as string)
    : localeCodes[0]

  const splitFullPath = () => {
    const url = new URL(to.fullPath, 'http://local')
    return { path: url.pathname, search: url.search, hash: url.hash }
  }

  const stripLocalePrefix = (path: string) => {
    if (!localePattern.test(path)) {
      return path
    }
    const withoutLocale = path.replace(localePattern, '/')
    return withoutLocale === '' ? '/' : withoutLocale
  }

  const ensureLocalePrefix = (path: string) => {
    if (localePattern.test(path)) {
      return path
    }
    const suffix = path === '/' ? '' : path
    return `/${preferredLocale}${suffix}`
  }

  const isPrivatePath = (path: string) => {
    const clean = stripLocalePrefix(path)
    return (
      clean.startsWith('/dashboard') ||
      clean.startsWith('/auth') ||
      clean === '/login' ||
      clean === '/signup' ||
      clean === '/forgot-password' ||
      clean.startsWith('/reset-password')
    )
  }

  const isPublicPath = (path: string) => !isPrivatePath(path)

  const { path, search, hash } = splitFullPath()

  if (currentHost === siteHost && isPrivatePath(path)) {
    const targetPath = stripLocalePrefix(path)
    const target = `${appBaseUrl}${targetPath}${search}${hash}`
    return navigateTo(target, { redirectCode: 302, external: true })
  }

  if (currentHost === appHost && isPublicPath(path)) {
    const targetPath = ensureLocalePrefix(path)
    const target = `${siteBaseUrl}${targetPath}${search}${hash}`
    return navigateTo(target, { redirectCode: 302, external: true })
  }

  if (currentHost === siteHost && isPublicPath(path) && path !== '/' && !localePattern.test(path)) {
    const targetPath = ensureLocalePrefix(path)
    return navigateTo(`${targetPath}${search}${hash}`, { redirectCode: 302 })
  }
})
