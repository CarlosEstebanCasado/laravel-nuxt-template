import { mount } from '@vue/test-utils'
import { ref } from 'vue'
import { __setAuthMock, __setRouteMock, __setRouterMock, __setToastMock } from '#imports'
import EmailVerifiedPage from '~/pages/auth/email-verified.vue'

describe('EmailVerifiedPage', () => {
  it('redirects verified users to target', async () => {
    const fetchUser = vi.fn().mockResolvedValue(undefined)
    const replace = vi.fn()
    const add = vi.fn()

    __setAuthMock({
      fetchUser,
      user: ref({ email_verified_at: '2024-01-01T00:00:00Z' })
    })
    __setRouteMock({ query: { redirect: '/dashboard' } })
    __setRouterMock({ replace })
    __setToastMock({ add })

    mount(EmailVerifiedPage)
    await Promise.resolve()

    expect(fetchUser).toHaveBeenCalled()
    expect(add).toHaveBeenCalled()
    expect(replace).toHaveBeenCalledWith('/dashboard')
  })

  it('redirects unverified users to verify flow', async () => {
    const fetchUser = vi.fn().mockResolvedValue(undefined)
    const replace = vi.fn()

    __setAuthMock({
      fetchUser,
      user: ref({ email_verified_at: null })
    })
    __setRouteMock({ query: { redirect: '/dashboard' } })
    __setRouterMock({ replace })
    __setToastMock({ add: vi.fn() })

    mount(EmailVerifiedPage)
    await Promise.resolve()

    expect(replace).toHaveBeenCalledWith('/auth/verify-email?redirect=%2Fdashboard')
  })
})
