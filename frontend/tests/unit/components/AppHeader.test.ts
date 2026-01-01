import { mount } from '@vue/test-utils'
import AppHeader from '~/components/AppHeader.vue'

describe('AppHeader', () => {
  it('builds navigation items', () => {
    const wrapper = mount(AppHeader)
    const menus = wrapper.findAllComponents({ name: 'UNavigationMenu' })
    const items = menus[0].props('items') as Array<{ label: string; to: string }>

    expect(items).toEqual(
      expect.arrayContaining([
        expect.objectContaining({ to: '/docs' }),
        expect.objectContaining({ to: '/pricing' }),
        expect.objectContaining({ to: '/blog' }),
        expect.objectContaining({ to: '/changelog' })
      ])
    )
  })
})
