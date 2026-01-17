import { joinURL } from 'ufo'
import type { AuthResponse, AuthUser, TwoFactorChallengeResponse } from '~/types/auth'
import type { PreferencesResponse, UserPreferencesPayload } from '~/types/preferences'
import { createRequestId } from '~/utils/request-id'

type SupportedLocale = 'es' | 'en' | 'ca'

const CSRF_ENDPOINT = '/sanctum/csrf-cookie'

const useAuthUserState = () => useState<AuthUser | null>('auth:user', () => null)

const useAuthFetchedState = () => useState<boolean>('auth:fetched', () => false)

const usePreferencesState = () => useState<UserPreferencesPayload | null>('auth:preferences', () => null)

const usePreferencesFetchedState = () => useState<boolean>('auth:preferences:fetched', () => false)

const usePreferenceOptionsState = () =>
  useState<{
    locales: { value: string, label: string }[]
    themes: { value: string, label: string }[]
    primary_colors: { value: string, label: string }[]
    neutral_colors: { value: string, label: string }[]
    timezones: { value: string, label: string }[]
  }>(
    'auth:preferences:options',
    () => ({
      locales: [],
      themes: [],
      primary_colors: [],
      neutral_colors: [],
      timezones: [],
    }),
  )

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

export function useAuth() {
  const config = useRuntimeConfig()
  const apiBase = config.public.apiBase
  const apiPrefix = config.public.apiPrefix
  const authPrefix = config.public.authPrefix
  const nuxtApp = useNuxtApp()
  const router = useRouter()
  const appConfig = useAppConfig()

  const user = useAuthUserState()
  const hasFetched = useAuthFetchedState()
  const preferences = usePreferencesState()
  const preferencesFetched = usePreferencesFetchedState()
  const preferenceOptions = usePreferenceOptionsState()
  const localeState = useState<SupportedLocale>('auth:active-locale', () => (nuxtApp.$i18n?.locale.value as SupportedLocale) ?? 'es')
  const colorMode = useColorMode()

  const getActiveLocale = () => nuxtApp.$i18n?.locale ?? localeState

  const setActiveLocale = (newLocale: SupportedLocale) => {
    localeState.value = newLocale
    if (nuxtApp.$i18n?.setLocale) {
      void nuxtApp.$i18n.setLocale(newLocale)
      return
    }
    if (nuxtApp.$i18n?.locale) {
      nuxtApp.$i18n.locale.value = newLocale
    }
  }

  const applyPreferenceEffects = (prefs: UserPreferencesPayload | null) => {
    if (prefs?.locale) {
      setActiveLocale(prefs.locale)
    }
    if (prefs?.theme) {
      colorMode.preference = prefs.theme
    }
    if (prefs?.primary_color) {
      appConfig.ui.colors.primary = prefs.primary_color
    }
    if (prefs?.neutral_color) {
      appConfig.ui.colors.neutral = prefs.neutral_color
    }
  }

  const resetPreferences = () => {
    preferences.value = null
    preferenceOptions.value = { locales: [], themes: [], primary_colors: [], neutral_colors: [], timezones: [] }
    preferencesFetched.value = false
  }

  const setPreferenceState = (payload: PreferencesResponse) => {
    preferences.value = payload.data
    preferenceOptions.value = {
      locales: payload.available_locales,
      themes: payload.available_themes,
      primary_colors: payload.available_primary_colors,
      neutral_colors: payload.available_neutral_colors,
      timezones: payload.available_timezones,
    }
    preferencesFetched.value = true
    applyPreferenceEffects(preferences.value)
  }

  const handleInvalidSession = async () => {
    const hadUser = Boolean(user.value)
    user.value = null
    hasFetched.value = true
    resetPreferences()

    if (import.meta.server) {
      return
    }

    // Avoid redirect loops on guest/auth pages.
    const path = router.currentRoute.value?.path ?? ''
    const isGuestPage =
      path === '/login' ||
      path === '/signup' ||
      path === '/forgot-password' ||
      path.startsWith('/reset-password') ||
      path.startsWith('/auth/')

    // Only force-redirect when we believed the user was authenticated (zombie dashboard case).
    // For guest flows (forgot/reset password), a 401 is expected and should not hijack navigation.
    if (hadUser && !isGuestPage) {
      await router.replace('/login')
    }
  }

  const withCredentials = <T>(
    path: string,
    options: Parameters<typeof $fetch<T>>[1] = {},
    opts: { csrf?: boolean } = {}
  ) => {
    const headers: Record<string, string> = {
      Accept: 'application/json',
      ...(options?.headers as Record<string, string> | undefined),
    }

    if (!headers['X-Request-Id']) {
      headers['X-Request-Id'] = createRequestId()
    }

    const preferredLocale = preferences.value?.locale || getActiveLocale().value || localeState.value
    if (preferredLocale) {
      headers['X-Locale'] = preferredLocale
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
    }).catch(async (error: any) => {
      const status = error?.status ?? error?.response?.status

      // If the session was revoked elsewhere, the SPA can be left in a "zombie" state:
      // user state is still set, but API calls start failing with 401/419.
      if (status === 401 || status === 419) {
        await handleInvalidSession()
      }

      throw error
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
    if (payload.data.preferences) {
      preferences.value = payload.data.preferences
      applyPreferenceEffects(preferences.value)
    }
    return user.value
  }

  const fetchPreferences = async (force = false) => {
    if (!user.value) {
      resetPreferences()
      return null
    }

    if (preferencesFetched.value && !force) {
      return preferences.value
    }

    const response = await withCredentials<PreferencesResponse>(
      joinURL(apiPrefix, '/preferences'),
      {
        method: 'GET',
      }
    )

    setPreferenceState(response)

    return preferences.value
  }

  const updatePreferences = async (payload: Partial<UserPreferencesPayload>) => {
    await ensureCsrfCookie()

    const response = await withCredentials<PreferencesResponse>(
      joinURL(apiPrefix, '/preferences'),
      {
        method: 'PUT',
        body: payload,
      },
      { csrf: true }
    )

    setPreferenceState(response)

    return preferences.value
  }

  const isAuthResponse = (value: AuthResponse | TwoFactorChallengeResponse): value is AuthResponse => {
    return 'data' in value
  }

  const login = async (payload: {
    email: string
    password: string
    remember?: boolean
  }): Promise<AuthUser | { twoFactorRequired: true }> => {
    await ensureCsrfCookie()

    const response = await withCredentials<AuthResponse | TwoFactorChallengeResponse>(
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

    if (!isAuthResponse(response)) {
      return { twoFactorRequired: true }
    }

    const authUser = handleAuthSuccess(response)
    await fetchPreferences(true).catch((error) => {
      console.error(error)
      return null
    })
    return authUser
  }

  const completeTwoFactorLogin = async (payload: { code?: string, recovery_code?: string }) => {
    await ensureCsrfCookie()

    const response = await withCredentials<AuthResponse>(
      joinURL(authPrefix, '/two-factor-challenge'),
      {
        method: 'POST',
        body: payload,
      },
      { csrf: true }
    )

    const authUser = handleAuthSuccess(response)
    await fetchPreferences(true).catch((error) => {
      console.error(error)
      return null
    })
    return authUser
  }

  const register = async (payload: {
    name: string
    email: string
    password: string
    password_confirmation: string
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

    const authUser = handleAuthSuccess(response)
    await fetchPreferences(true).catch((error) => {
      console.error(error)
      return null
    })
    return authUser
  }

  const updateProfile = async (payload: { name: string, email: string, current_password?: string }) => {
    await ensureCsrfCookie()

    // Fortify's profile update endpoint returns an empty response by default.
    // Refresh the session user via /api/v1/me after a successful update.
    await withCredentials(
      joinURL(authPrefix, '/user/profile-information'),
      {
        method: 'PUT',
        body: {
          name: payload.name,
          email: payload.email,
          ...(payload.current_password?.trim()
            ? { current_password: payload.current_password.trim() }
            : {}),
        },
      },
      { csrf: true }
    )

    return await fetchUser(true)
  }

  const updatePassword = async (payload: {
    current_password: string
    password: string
    password_confirmation: string
  }) => {
    await ensureCsrfCookie()

    // Fortify's default password update response is empty, but we don't rely on it.
    await withCredentials(
      joinURL(authPrefix, '/user/password'),
      {
        method: 'PUT',
        body: payload,
      },
      { csrf: true }
    )

    return true
  }

  const resendEmailVerification = async () => {
    await ensureCsrfCookie()

    return withCredentials(
      joinURL(authPrefix, '/email/verification-notification'),
      {
        method: 'POST',
      },
      { csrf: true }
    )
  }

  const deleteAccount = async (payload: { confirmation: 'DELETE', password?: string }) => {
    await ensureCsrfCookie()

    await withCredentials(
      joinURL(apiPrefix, '/account'),
      {
        method: 'DELETE',
        body: payload,
      },
      { csrf: true }
    )

    // Server invalidates the session; clear client state too.
    user.value = null
    hasFetched.value = true
    resetPreferences()
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
    resetPreferences()
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

  const enableTwoFactor = async () => {
    await ensureCsrfCookie()

    await withCredentials(
      joinURL(authPrefix, '/user/two-factor-authentication'),
      { method: 'POST' },
      { csrf: true }
    )

    await fetchUser(true)
  }

  const confirmTwoFactorEnrollment = async (code: string) => {
    await ensureCsrfCookie()

    await withCredentials(
      joinURL(authPrefix, '/user/confirmed-two-factor-authentication'),
      {
        method: 'POST',
        body: { code },
      },
      { csrf: true }
    )

    await fetchUser(true)
  }

  const disableTwoFactor = async (password: string) => {
    await ensureCsrfCookie()

    await withCredentials(
      joinURL(apiPrefix, '/two-factor/disable'),
      {
        method: 'POST',
        body: { password },
      },
      { csrf: true }
    )

    await fetchUser(true)
  }

  const fetchTwoFactorQrCode = async () => {
    return withCredentials<{ svg: string, url: string }>(
      joinURL(authPrefix, '/user/two-factor-qr-code'),
      { method: 'GET' }
    )
  }

  const fetchTwoFactorSecret = async () => {
    return withCredentials<{ secretKey: string }>(
      joinURL(authPrefix, '/user/two-factor-secret-key'),
      { method: 'GET' }
    )
  }

  const fetchTwoFactorRecoveryCodes = async (password: string) => {
    await ensureCsrfCookie()

    const response = await withCredentials<{ codes: unknown }>(
      joinURL(apiPrefix, '/two-factor/recovery-codes'),
      {
        method: 'POST',
        body: { password },
      },
      { csrf: true }
    )

    return normalizeRecoveryCodes(response.codes)
  }

  const regenerateTwoFactorRecoveryCodes = async (password: string) => {
    await ensureCsrfCookie()

    const response = await withCredentials<{ codes: unknown }>(
      joinURL(apiPrefix, '/two-factor/recovery-codes/regenerate'),
      {
        method: 'POST',
        body: { password },
      },
      { csrf: true }
    )

    return normalizeRecoveryCodes(response.codes)
  }

  const fetchTwoFactorRecoveryCodesAfterConfirm = async () => {
    const response = await withCredentials<unknown>(
      joinURL(authPrefix, '/user/two-factor-recovery-codes'),
      { method: 'GET' }
    )

    return normalizeRecoveryCodes(response)
  }

  const normalizeRecoveryCodes = (codes: unknown): string[] => {
    if (Array.isArray(codes)) {
      if (codes.length === 1 && typeof codes[0] === 'string') {
        const trimmed = codes[0].trim()
        if (trimmed.startsWith('[')) {
          try {
            const parsed = JSON.parse(trimmed)
            if (Array.isArray(parsed)) {
              return parsed.filter((value): value is string => typeof value === 'string')
            }
          } catch {
            return codes.filter((value): value is string => typeof value === 'string')
          }
        }
      }

      return codes.filter((value): value is string => typeof value === 'string')
    }

    if (typeof codes === 'string') {
      try {
        const parsed = JSON.parse(codes)
        if (Array.isArray(parsed)) {
          return parsed.filter((value): value is string => typeof value === 'string')
        }
      } catch {
        return []
      }
    }

    return []
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
      if (user.value) {
        await fetchPreferences(true).catch((error) => {
          console.error(error)
          return null
        })
      } else {
        resetPreferences()
      }
      return user.value
    } catch (error: any) {
      // Treat unauthenticated responses as a valid "no user" state.
      if (
        error?.status === 401 ||
        error?.status === 403 ||
        error?.status === 419 ||
        error?.response?.status === 401
      ) {
        user.value = null
        resetPreferences()
        return null
      }

      throw error
    } finally {
      hasFetched.value = true
    }
  }

  type SessionInfo = {
    id: string
    ip_address: string | null
    user_agent: string | null
    last_activity: number
    is_current: boolean
  }

  const listSessions = async () => {
    const response = await withCredentials<{ data: SessionInfo[] }>(
      joinURL(apiPrefix, '/sessions'),
      { method: 'GET' }
    )
    return response.data
  }

  const revokeOtherSessions = async () => {
    await ensureCsrfCookie()

    const response = await withCredentials<{ data: { revoked: number } }>(
      joinURL(apiPrefix, '/sessions/revoke-others'),
      {
        method: 'POST',
        body: {},
      },
      { csrf: true }
    )

    return response.data
  }

  const revokeSession = async (id: string) => {
    await ensureCsrfCookie()

    await withCredentials(
      joinURL(apiPrefix, `/sessions/${encodeURIComponent(id)}`),
      {
        method: 'DELETE',
        body: {},
      },
      { csrf: true }
    )

    return true
  }

  type AuditEntry = {
    id: number
    event: string
    created_at: string
    old_values: Record<string, any> | null
    new_values: Record<string, any> | null
    ip_address: string | null
    user_agent: string | null
    tags: string | null
  }

  const listAudits = async (page = 1) => {
    const response = await withCredentials<{ data: AuditEntry[], meta: any }>(
      joinURL(apiPrefix, '/audits'),
      { method: 'GET', query: { page } }
    )
    return response
  }

  const isAuthenticated = computed(() => Boolean(user.value))

  const loginWithProvider = (provider: 'google' | 'github') => {
    if (import.meta.server) {
      return
    }

    const location = joinURL(apiBase, '/auth/oauth', provider)
    window.location.href = location
  }

  return {
    user,
    preferences,
    preferenceOptions,
    isAuthenticated,
    login,
    completeTwoFactorLogin,
    register,
    updateProfile,
    updatePassword,
    listSessions,
    revokeOtherSessions,
    revokeSession,
    listAudits,
    resendEmailVerification,
    deleteAccount,
    logout,
    requestPasswordReset,
    resetPassword,
    enableTwoFactor,
    confirmTwoFactorEnrollment,
    disableTwoFactor,
    fetchTwoFactorQrCode,
    fetchTwoFactorSecret,
    fetchTwoFactorRecoveryCodes,
    fetchTwoFactorRecoveryCodesAfterConfirm,
    regenerateTwoFactorRecoveryCodes,
    fetchUser,
    fetchPreferences,
    updatePreferences,
    loginWithProvider,
  }
}
