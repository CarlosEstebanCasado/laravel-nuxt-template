import type { UserPreferencesPayload } from './preferences'

export interface AuthUser {
  id: number
  name: string
  email: string
  email_verified_at: string | null
  auth_provider: 'password' | 'google' | 'github' | string
  password_set_at: string | null
  two_factor_enabled: boolean
  two_factor_confirmed: boolean
  created_at: string
  updated_at: string
  preferences?: UserPreferencesPayload | null
}

export interface AuthResponse {
  data: AuthUser
}

export interface TwoFactorChallengeResponse {
  two_factor: true
}
