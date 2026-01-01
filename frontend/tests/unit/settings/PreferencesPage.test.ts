import { mount } from '@vue/test-utils'
import { ref } from 'vue'
import { __setAuthMock, __setToastMock } from '#imports'
import PreferencesPage from '~/pages/dashboard/settings/preferences.vue'

describe('PreferencesPage', () => {
  it('loads and submits preferences', async () => {
    const fetchUser = vi.fn().mockResolvedValue(undefined)
    const fetchPreferences = vi.fn().mockResolvedValue(undefined)
    const updatePreferences = vi.fn().mockResolvedValue(undefined)

    __setAuthMock({
      fetchUser,
      fetchPreferences,
      updatePreferences,
      preferences: ref({
        locale: 'en',
        theme: 'dark',
        primary_color: 'blue',
        neutral_color: 'slate'
      }),
      preferenceOptions: ref({
        locales: [{ label: 'English', value: 'en' }],
        themes: [{ label: 'Dark', value: 'dark' }],
        primary_colors: [{ label: 'Blue', value: 'blue' }],
        neutral_colors: [{ label: 'Slate', value: 'slate' }]
      })
    })
    __setToastMock({ add: vi.fn() })

    const wrapper = mount(PreferencesPage)

    await Promise.resolve()
    await wrapper.vm.$nextTick()

    expect(fetchUser).toHaveBeenCalled()
    expect(fetchPreferences).toHaveBeenCalled()

    const form = wrapper.findComponent({ name: 'UForm' })
    form.vm.$emit('submit', {
      data: {
        locale: 'en',
        theme: 'dark',
        primary_color: 'blue',
        neutral_color: 'slate'
      }
    })

    await Promise.resolve()

    expect(updatePreferences).toHaveBeenCalledWith({
      locale: 'en',
      theme: 'dark',
      primary_color: 'blue',
      neutral_color: 'slate'
    })
  })
})
