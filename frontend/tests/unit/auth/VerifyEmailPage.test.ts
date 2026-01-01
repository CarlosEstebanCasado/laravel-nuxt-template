import { mount } from '@vue/test-utils'
import { __setAuthMock, __setRouteMock, __setRouterMock, __setToastMock } from '#imports'
import VerifyEmailPage from '~/pages/auth/verify-email.vue'

describe('VerifyEmailPage', () => {
  it('redirects when user is already verified', async () => {
    const replace = vi.fn()
    const fetchUser = vi.fn().mockResolvedValue(undefined)

    __setAuthMock({
      fetchUser,
      user: { value: { email: 'user@example.com', email_verified_at: '2024-01-01T00:00:00Z' } }
    })
    __setRouteMock({ query: { redirect: '/dashboard' } })
    __setRouterMock({ replace })
    __setToastMock({ add: vi.fn() })

    mount(VerifyEmailPage)
    await Promise.resolve()

    expect(fetchUser).toHaveBeenCalled()
    expect(replace).toHaveBeenCalledWith('/dashboard')
  })
})
