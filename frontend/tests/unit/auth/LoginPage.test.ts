import { mount } from '@vue/test-utils'
import { __setAuthMock, __setRouterMock, __setToastMock } from '#imports'
import LoginPage from '~/pages/login.vue'

describe('LoginPage', () => {
  it('submits login payload and redirects', async () => {
    const login = vi.fn().mockResolvedValue({ email_verified_at: '2024-01-01T00:00:00Z' })
    const push = vi.fn()

    __setAuthMock({ login })
    __setRouterMock({ push })
    __setToastMock({ add: vi.fn() })

    const wrapper = mount(LoginPage)
    const form = wrapper.find('[data-stub="UAuthForm"]')
    await form.trigger('click')
    await Promise.resolve()

    expect(login).toHaveBeenCalledWith({
      email: '',
      password: '',
      remember: false
    })
    expect(push).toHaveBeenCalled()
  })
})
