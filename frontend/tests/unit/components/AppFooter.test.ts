import { mount } from '@vue/test-utils'
import { __setToastMock } from '#imports'
import AppFooter from '~/components/AppFooter.vue'

describe('AppFooter', () => {
  it('renders footer columns', () => {
    const wrapper = mount(AppFooter)
    const columns = (wrapper.vm as { columns: unknown[] }).columns

    expect(columns).toHaveLength(3)
  })

  it('shows a toast when subscribing', async () => {
    const add = vi.fn()
    __setToastMock({ add })

    const wrapper = mount(AppFooter)
    await wrapper.find('form').trigger('submit.prevent')

    expect(add).toHaveBeenCalled()
  })
})
