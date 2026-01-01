import { mount } from '@vue/test-utils'
import { __setAuthMock, __setRouterMock, __setToastMock } from '#imports'
import SignupPage from '~/pages/signup.vue'

describe('SignupPage', () => {
  it('submits signup payload and redirects', async () => {
    const register = vi.fn().mockResolvedValue({ email_verified_at: '2024-01-01T00:00:00Z' })
    const push = vi.fn()

    __setAuthMock({ register })
    __setRouterMock({ push })
    __setToastMock({ add: vi.fn() })

    const wrapper = mount(SignupPage)
    const form = wrapper.find('[data-stub="UAuthForm"]')

    await form.trigger('click')

    expect(register).toHaveBeenCalledWith({
      name: '',
      email: '',
      password: '',
      password_confirmation: ''
    })
    expect(push).toHaveBeenCalled()
  })
})
