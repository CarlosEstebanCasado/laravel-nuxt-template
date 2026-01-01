import { mount } from '@vue/test-utils'
import { __setAuthMock, __setToastMock } from '#imports'
import SecuritySessionsSection from '~/app/components/security/SecuritySessionsSection.vue'

describe('SecuritySessionsSection', () => {
  it('renders sessions after loading', async () => {
    __setAuthMock({
      listSessions: vi.fn().mockResolvedValue([
        {
          id: 'session-1',
          ip_address: '127.0.0.1',
          user_agent: 'Mozilla/5.0 (Mac OS X) Chrome/120.0',
          last_activity: 1700000000,
          is_current: true
        }
      ])
    })
    __setToastMock({ add: vi.fn() })

    const wrapper = mount(SecuritySessionsSection)

    await Promise.resolve()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('settings.security.sessions.title')
    expect(wrapper.text()).toContain('Chrome')
    expect(wrapper.text()).toContain('macOS')
  })
})
