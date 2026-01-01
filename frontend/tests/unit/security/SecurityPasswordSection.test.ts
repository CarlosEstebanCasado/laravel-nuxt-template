import { mount } from '@vue/test-utils'
import { __setAuthMock, __setToastMock } from '#imports'
import SecurityPasswordSection from '~/app/components/security/SecurityPasswordSection.vue'

describe('SecurityPasswordSection', () => {
  it('renders the password section title', () => {
    __setAuthMock({
      updatePassword: vi.fn().mockResolvedValue(undefined)
    })
    __setToastMock({ add: vi.fn() })

    const wrapper = mount(SecurityPasswordSection)

    expect(wrapper.text()).toContain('settings.security.password_section.title')
  })
})
