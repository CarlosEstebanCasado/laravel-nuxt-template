export interface AuthUser {
  id: number
  name: string
  email: string
  email_verified_at: string | null
  auth_provider: 'password' | 'google' | 'github' | string
  password_set_at: string | null
  created_at: string
  updated_at: string
}

export interface AuthResponse {
  data: AuthUser
}
