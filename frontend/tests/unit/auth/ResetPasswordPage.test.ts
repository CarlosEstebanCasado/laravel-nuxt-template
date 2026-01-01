import { mount } from '@vue/test-utils'
import { __setAuthMock, __setRouteMock, __setRouterMock, __setToastMock } from '#imports'
import ResetPasswordPage from '~/pages/reset-password/[token].vue'

describe('ResetPasswordPage', () => {
  it('submits reset password payload', async () => {
    const resetPassword = vi.fn().mockResolvedValue(undefined)
    const push = vi.fn()

    __setAuthMock({ resetPassword })
    __setRouteMock({ params: { token: 'token-123' }, query: { email: 'user@example.com' } })
    __setRouterMock({ push })
    __setToastMock({ add: vi.fn() })

    const wrapper = mount(ResetPasswordPage)
    const form = wrapper.findComponent({ name: 'UAuthForm' })

    form.vm.$emit('submit', {
      data: {
        email: 'user@example.com',
        password: 'password123',
        password_confirmation: 'password123'
      }
    })
    await Promise.resolve()

    expect(resetPassword).toHaveBeenCalledWith({
      token: 'token-123',
      email: 'user@example.com',
      password: 'password123',
      password_confirmation: 'password123'
    })
  })
})
