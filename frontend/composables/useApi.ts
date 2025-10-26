import type { FetchOptions } from 'ofetch'

type MaybeHeaders = Record<string, string>

const sanitizeBase = (value: string) => value.replace(/\/$/, '')
const sanitizePrefix = (value: string) => {
  if (!value) {
    return ''
  }

  if (!value.startsWith('/')) {
    return `/${value}`
  }

  return value.replace(/\/$/, '')
}

export const useApi = () => {
  const config = useRuntimeConfig()
  const clientBaseUrl = sanitizeBase(config.public.apiBase || '')
  const internalBaseUrl = sanitizeBase(config.public.internalApiBase || clientBaseUrl)
  const apiPrefix = sanitizePrefix(config.public.apiPrefix || '/api/v1')
  const authPrefix = sanitizePrefix(config.public.authPrefix || '/auth')

  const usingInternalBase = () => process.server && internalBaseUrl !== clientBaseUrl
  const buildHeaders = (): MaybeHeaders => {
    const headers: MaybeHeaders = {}

    if (process.server) {
      const requestHeaders = useRequestHeaders([
        'cookie',
        'host',
        'x-forwarded-host',
        'x-forwarded-proto',
        'x-forwarded-for'
      ])

      if (requestHeaders.cookie) {
        headers.cookie = requestHeaders.cookie
      }

      // No extra headers needed when hitting gateway aliases.
    }

    return headers
  }

  const request = async <T>(path: string, options: FetchOptions<'json'> = {}) => {
    const base = process.server ? internalBaseUrl : clientBaseUrl
    if (process.server) {
      console.info('[useApi] SSR fetch ->', `${base}${path}`)
    }

    return $fetch<T>(`${base}${path}`, {
      credentials: 'include',
      headers: {
        Accept: 'application/json',
        ...buildHeaders(),
        ...(options.headers as MaybeHeaders ?? {})
      },
      ...options
    })
  }

  const requestApi = async <T>(path: string, options: FetchOptions<'json'> = {}) => {
    return request<T>(`${apiPrefix}${path}`, options)
  }

  const requestAuth = async <T>(path: string, options: FetchOptions<'json'> = {}) => {
    return request<T>(`${authPrefix}${path}`, options)
  }

  return {
    request,
    requestApi,
    requestAuth,
    baseUrl: clientBaseUrl,
    apiPrefix,
    authPrefix
  }
}
