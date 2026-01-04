import { mount } from '@vue/test-utils'
import { __setAuthMock, __setToastMock } from '#imports'
import SecurityTwoFactorSection from '~/components/security/SecurityTwoFactorSection.vue'

describe('SecurityTwoFactorSection', () => {
  it('renders the disabled state copy', () => {
    __setAuthMock({
      user: { value: { two_factor_enabled: false, two_factor_confirmed: false } }
    })
    __setToastMock({ add: vi.fn() })

    const wrapper = mount(SecurityTwoFactorSection)

    expect(wrapper.text()).toContain('settings.security.two_factor_section.title')
    expect(wrapper.text()).toContain('settings.security.two_factor_section.status_disabled')
  })

  it('renders enabled status when active', () => {
    __setAuthMock({
      user: { value: { two_factor_enabled: true, two_factor_confirmed: true } }
    })
    __setToastMock({ add: vi.fn() })

    const wrapper = mount(SecurityTwoFactorSection)

    expect(wrapper.text()).toContain('settings.security.two_factor_section.status_enabled')
  })
})
