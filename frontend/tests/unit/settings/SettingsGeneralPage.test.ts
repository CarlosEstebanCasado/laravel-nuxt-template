import { mount } from '@vue/test-utils'
import { ref } from 'vue'
import { __setAuthMock, __setRouterMock, __setToastMock } from '#imports'
import SettingsGeneralPage from '~/pages/dashboard/settings/index.vue'

describe('SettingsGeneralPage', () => {
  it('submits profile updates', async () => {
    const fetchUser = vi.fn().mockResolvedValue(undefined)
    const updateProfile = vi.fn().mockResolvedValue({ email_verified_at: '2024-01-01T00:00:00Z' })
    const add = vi.fn()

    __setAuthMock({
      fetchUser,
      updateProfile,
      user: ref({ name: 'Old', email: 'old@example.com', auth_provider: 'oauth', password_set_at: null })
    })
    __setRouterMock({ push: vi.fn() })
    __setToastMock({ add })

    const wrapper = mount(SettingsGeneralPage)
    await Promise.resolve()

    wrapper.findComponent({ name: 'UForm' }).vm.$emit('submit', {
      data: { name: 'New', email: 'new@example.com' }
    })
    await Promise.resolve()

    expect(updateProfile).toHaveBeenCalledWith({
      name: 'New',
      email: 'new@example.com',
      current_password: undefined
    })
    expect(add).toHaveBeenCalled()
  })
})
