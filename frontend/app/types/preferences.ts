export interface PreferenceOption {
  value: string
  label: string
}

export interface UserPreferencesPayload {
  locale: string
  theme: 'system' | 'light' | 'dark'
  primary_color: string
  neutral_color: string
}

export interface PreferencesResponse {
  data: UserPreferencesPayload
  available_locales: PreferenceOption[]
  available_themes: PreferenceOption[]
  available_primary_colors: PreferenceOption[]
  available_neutral_colors: PreferenceOption[]
}
