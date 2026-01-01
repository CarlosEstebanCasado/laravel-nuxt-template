import { mount } from '@vue/test-utils'
import { __setAuthMock, __setRouteMock, __setRouterMock, __setToastMock } from '#imports'
import OAuthCallbackPage from '~/pages/auth/callback.vue'

describe('OAuthCallbackPage', () => {
  it('redirects to login on error status', async () => {
    vi.useFakeTimers()

    const replace = vi.fn()

    __setAuthMock({ fetchUser: vi.fn() })
    __setRouteMock({ query: { status: 'error', provider: 'google', error: 'email_missing' } })
    __setRouterMock({ replace })
    __setToastMock({ add: vi.fn() })

    mount(OAuthCallbackPage)

    vi.runAllTimers()
    await Promise.resolve()

    expect(replace).toHaveBeenCalledWith('/login')
    vi.useRealTimers()
  })
})
