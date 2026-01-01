import { mount } from '@vue/test-utils'
import { __setAuthMock, __setToastMock, __setRouterMock } from '#imports'
import SecurityAccountSection from '~/app/components/security/SecurityAccountSection.vue'

describe('SecurityAccountSection', () => {
  it('renders the delete account action', () => {
    __setAuthMock({
      deleteAccount: vi.fn().mockResolvedValue(undefined),
      user: { value: { auth_provider: 'password', password_set_at: null } }
    })
    __setToastMock({ add: vi.fn() })
    __setRouterMock({ replace: vi.fn() })

    const wrapper = mount(SecurityAccountSection)

    expect(wrapper.text()).toContain('actions.delete_account')
  })
})
