import { joinURL } from 'ufo'
import { createRequestId } from '~/utils/request-id'

type ApiFetchOptions = {
  csrf?: boolean
  base?: 'api' | 'root'
}

const readCookie = (name: string) => {
  if (typeof document === 'undefined') {
    return null
  }

  const prefix = `${name}=`
  const parts = document.cookie.split('; ')
  for (const part of parts) {
    if (part.startsWith(prefix)) {
      return part.slice(prefix.length)
    }
  }

  return null
}

const getCsrfHeader = () => {
  if (import.meta.server) {
    return null
  }

  const raw = readCookie('XSRF-TOKEN')
  if (!raw) {
    return null
  }

  try {
    return decodeURIComponent(raw)
  } catch {
    return raw
  }
}

export function useApi() {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase
  const apiPrefix = config.public.apiPrefix
  const nuxtApp = useNuxtApp()

  const apiFetch = <T>(
    path: string,
    options: Parameters<typeof $fetch<T>>[1] = {},
    opts: ApiFetchOptions = {}
  ) => {
    const headers: Record<string, string> = {
      Accept: 'application/json',
      ...(options?.headers as Record<string, string> | undefined),
    }

    if (!headers['X-Request-Id']) {
      headers['X-Request-Id'] = createRequestId()
    }

    const locale = nuxtApp.$i18n?.locale?.value
    if (locale) {
      headers['X-Locale'] = locale
    }

    if (opts.csrf) {
      const csrf = getCsrfHeader()
      if (csrf) {
        headers['X-XSRF-TOKEN'] = csrf
      }
    }

    const url = path.startsWith('http')
      ? path
      : (opts.base === 'root'
          ? joinURL(apiBase, path)
          : joinURL(apiBase, apiPrefix, path))

    return $fetch<T>(url, {
      credentials: 'include',
      headers,
      ...options,
    })
  }

  return { apiFetch }
}
