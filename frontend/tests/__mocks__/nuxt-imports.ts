import { computed, onMounted, reactive, ref, watch } from 'vue'
import { vi } from 'vitest'

let authMock = {
  listSessions: vi.fn(),
  revokeOtherSessions: vi.fn(),
  revokeSession: vi.fn(),
  listAudits: vi.fn(),
  updatePassword: vi.fn(),
  deleteAccount: vi.fn(),
  updateProfile: vi.fn(),
  login: vi.fn(),
  register: vi.fn(),
  loginWithProvider: vi.fn(),
  fetchUser: vi.fn(),
  fetchPreferences: vi.fn(),
  updatePreferences: vi.fn(),
  requestPasswordReset: vi.fn(),
  resetPassword: vi.fn(),
  resendEmailVerification: vi.fn(),
  logout: vi.fn().mockResolvedValue(undefined),
  preferences: ref(null),
  preferenceOptions: ref({
    locales: [],
    themes: [],
    primary_colors: [],
    neutral_colors: []
  }),
  user: ref({ auth_provider: 'password', password_set_at: null })
}

let toastMock = {
  add: vi.fn()
}

let routerMock = {
  replace: vi.fn(),
  push: vi.fn()
}

let routeMock = {
  query: {},
  params: {}
}

const localeRef = ref('es')
const tMock = (key: string) => key

export const __setAuthMock = (next: Partial<typeof authMock>) => {
  authMock = { ...authMock, ...next }
}

export const __setToastMock = (next: Partial<typeof toastMock>) => {
  toastMock = { ...toastMock, ...next }
}

export const __setRouterMock = (next: Partial<typeof routerMock>) => {
  routerMock = { ...routerMock, ...next }
}

export const __setRouteMock = (next: Partial<typeof routeMock>) => {
  routeMock = { ...routeMock, ...next }
}

export const __setLocale = (next: string) => {
  localeRef.value = next
}

export const useAuth = () => authMock
export const useToast = () => toastMock
export const useRouter = () => routerMock
export const useRoute = () => routeMock
export const useI18n = () => ({ t: tMock, locale: localeRef })
export const definePageMeta = () => {}
export const useSeoMeta = () => {}
export const useHead = () => {}

export { computed, onMounted, reactive, ref, watch }
