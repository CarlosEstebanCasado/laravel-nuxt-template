import { mount } from '@vue/test-utils'
import { __setAuthMock, __setRouterMock, __setToastMock } from '#imports'
import ForgotPasswordPage from '~/pages/forgot-password.vue'

describe('ForgotPasswordPage', () => {
  it('submits password reset request', async () => {
    const requestPasswordReset = vi.fn().mockResolvedValue(undefined)

    __setAuthMock({ requestPasswordReset })
    __setRouterMock({ push: vi.fn() })
    __setToastMock({ add: vi.fn() })

    const wrapper = mount(ForgotPasswordPage)
    const form = wrapper.findComponent({ name: 'UAuthForm' })

    form.vm.$emit('submit', { data: { email: 'user@example.com' } })
    await Promise.resolve()

    expect(requestPasswordReset).toHaveBeenCalledWith({ email: 'user@example.com' })
  })
})
