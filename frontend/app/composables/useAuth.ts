import { joinURL } from 'ufo'
import type { AuthResponse, AuthUser } from '~/types/auth'

const CSRF_ENDPOINT = '/sanctum/csrf-cookie'

const useAuthUserState = () => useState<AuthUser | null>('auth:user', () => null)

const useAuthFetchedState = () => useState<boolean>('auth:fetched', () => false)

const getCsrfHeader = () => {
  if (process.server) {
    return null
  }

  const token = useCookie<string | null>('XSRF-TOKEN')

  if (!token.value) {
    return null
  }

  try {
    return decodeURIComponent(token.value)
  } catch {
    return token.value
  }
}

export function useAuth() {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase
  const apiPrefix = config.public.apiPrefix
  const authPrefix = config.public.authPrefix

  const user = useAuthUserState()
  const hasFetched = useAuthFetchedState()

  const withCredentials = <T>(
    path: string,
    options: Parameters<typeof $fetch<T>>[1] = {},
    opts: { csrf?: boolean } = {}
  ) => {
    const headers: Record<string, string> = {
      Accept: 'application/json',
      ...(options?.headers as Record<string, string> | undefined),
    }

    if (opts.csrf) {
      const csrf = getCsrfHeader()
      if (csrf) {
        headers['X-XSRF-TOKEN'] = csrf
      }
    }

    return $fetch<T>(path, {
      baseURL: apiBase,
      credentials: 'include',
      headers,
      ...options,
    })
  }

  const ensureCsrfCookie = () =>
    withCredentials(CSRF_ENDPOINT, {
      method: 'GET',
    }).catch((error) => {
      // The endpoint returns 204 with empty body when successful.
      // Treat any failure as fatal so the caller can surface it.
      throw error
    })

  const handleAuthSuccess = (payload: AuthResponse) => {
    user.value = payload.data
    return user.value
  }

  const login = async (payload: {
    email: string
    password: string
    remember?: boolean
  }) => {
    await ensureCsrfCookie()

    const response = await withCredentials<AuthResponse>(
      joinURL(authPrefix, '/login'),
      {
        method: 'POST',
        body: {
          ...payload,
          remember: payload.remember ?? false,
        },
      },
      { csrf: true }
    )

    return handleAuthSuccess(response)
  }

  const register = async (payload: {
    name: string
    email: string
    password: string
  }) => {
    await ensureCsrfCookie()

    const response = await withCredentials<AuthResponse>(
      joinURL(authPrefix, '/register'),
      {
        method: 'POST',
        body: payload,
      },
      { csrf: true }
    )

    return handleAuthSuccess(response)
  }

  const logout = async () => {
    await ensureCsrfCookie()

    await withCredentials(
      joinURL(authPrefix, '/logout'),
      {
        method: 'POST',
      },
      { csrf: true }
    )

    user.value = null
  }

  const requestPasswordReset = async (payload: { email: string }) => {
    await ensureCsrfCookie()

    return withCredentials(
      joinURL(authPrefix, '/forgot-password'),
      {
        method: 'POST',
        body: payload,
      },
      { csrf: true }
    )
  }

  const resetPassword = async (payload: {
    token: string
    email: string
    password: string
    password_confirmation: string
  }) => {
    await ensureCsrfCookie()

    return withCredentials(
      joinURL(authPrefix, '/reset-password'),
      {
        method: 'POST',
        body: payload,
      },
      { csrf: true }
    )
  }

  const fetchUser = async (force = false) => {
    if (hasFetched.value && !force) {
      return user.value
    }

    try {
      const response = await withCredentials<AuthResponse>(
        joinURL(apiPrefix, '/me'),
        {
          method: 'GET',
        }
      )
      user.value = response.data
      return user.value
    } catch (error: any) {
      user.value = null

      // Treat unauthenticated responses as a valid "no user" state.
      if (
        error?.status === 401 ||
        error?.status === 419 ||
        error?.response?.status === 401
      ) {
        return null
      }

      throw error
    } finally {
      hasFetched.value = true
    }
  }

  const isAuthenticated = computed(() => Boolean(user.value))

  const loginWithProvider = (provider: 'google' | 'github') => {
    if (process.server) {
      return
    }

    const location = joinURL(apiBase, '/auth/oauth', provider)
    window.location.href = location
  }

  return {
    user,
    isAuthenticated,
    login,
    register,
    logout,
    requestPasswordReset,
    resetPassword,
    fetchUser,
    loginWithProvider,
  }
}
