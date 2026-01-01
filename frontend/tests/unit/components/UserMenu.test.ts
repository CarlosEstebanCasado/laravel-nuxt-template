import { mount } from '@vue/test-utils'
import { __setAuthMock, __setRouterMock } from '#imports'
import UserMenu from '~/components/UserMenu.vue'

describe('UserMenu', () => {
  it('logs out and redirects', async () => {
    const logout = vi.fn().mockResolvedValue(undefined)
    const push = vi.fn()

    __setAuthMock({ logout })
    __setRouterMock({ push })

    const wrapper = mount(UserMenu)
    const menu = wrapper.findComponent({ name: 'UDropdownMenu' })
    const groups = menu.props('items') as Array<Array<{ label: string; onSelect?: () => Promise<void> }>>
    const logoutItem = groups.flat().find((item) => item.label === 'userMenu.logout')

    await logoutItem?.onSelect?.()

    expect(logout).toHaveBeenCalled()
    expect(push).toHaveBeenCalledWith('/login')
  })
})
