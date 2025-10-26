interface AuthUser {
  id: number
  name: string
  email: string
  email_verified_at: string | null
  created_at: string
  updated_at: string
}

interface AuthResponse {
  data: AuthUser
}

interface LoginPayload {
  email: string
  password: string
  remember?: boolean
}

interface RegisterPayload extends LoginPayload {
  name: string
  password_confirmation: string
}

export const useAuth = () => {
  const user = useState<AuthUser | null>('auth:user', () => null)
  const fetchingUser = useState<boolean>('auth:fetching', () => false)
  const { request, requestApi, requestAuth } = useApi()

  const csrf = () => request('/sanctum/csrf-cookie', { method: 'GET' })

  const fetchUser = async (options: { force?: boolean; silent?: boolean } = {}) => {
    if (user.value && !options.force) {
      return user.value
    }

    if (fetchingUser.value) {
      return user.value
    }

    fetchingUser.value = true

    try {
      const response = await requestApi<AuthResponse>('/me', { method: 'GET' })
      user.value = response.data
      return user.value
    } catch (error) {
      user.value = null

      if (!options.silent) {
        throw error
      }

      return null
    } finally {
      fetchingUser.value = false
    }
  }

  const login = async (payload: LoginPayload) => {
    await csrf()
    await requestAuth<AuthResponse>('/login', {
      method: 'POST',
      body: payload
    })

    return fetchUser({ force: true })
  }

  const register = async (payload: RegisterPayload) => {
    await csrf()
    await requestAuth<AuthResponse>('/register', {
      method: 'POST',
      body: payload
    })

    return fetchUser({ force: true })
  }

  const logout = async () => {
    await csrf()
    await requestAuth('/logout', {
      method: 'POST'
    })

    user.value = null
  }

  const resendEmailVerification = async () => {
    await csrf()
    await requestAuth('/email/verification-notification', {
      method: 'POST'
    })
  }

  const isAuthenticated = computed(() => Boolean(user.value))
  const isVerified = computed(() => Boolean(user.value?.email_verified_at))

  return {
    user,
    isAuthenticated,
    isVerified,
    fetchUser,
    login,
    register,
    logout,
    resendEmailVerification
  }
}
