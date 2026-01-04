import { mount } from '@vue/test-utils'
import { __setAuthMock, __setToastMock, __setRouterMock, __setRouteMock } from '#imports'
import TwoFactorPage from '~/pages/auth/two-factor.vue'

describe('TwoFactorPage', () => {
  it('renders the two factor prompt', () => {
    __setAuthMock({ completeTwoFactorLogin: vi.fn() })
    __setToastMock({ add: vi.fn() })
    __setRouterMock({ push: vi.fn() })
    __setRouteMock({ query: {} })

    const wrapper = mount(TwoFactorPage)

    expect(wrapper.text()).toContain('auth.two_factor.title')
  })
})
