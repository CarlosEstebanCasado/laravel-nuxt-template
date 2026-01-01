import { mount } from '@vue/test-utils'
import { __setAuthMock, __setToastMock } from '#imports'
import SecurityActivitySection from '~/components/security/SecurityActivitySection.vue'

describe('SecurityActivitySection', () => {
  it('renders audit entries', async () => {
    __setAuthMock({
      listAudits: vi.fn().mockResolvedValue({
        data: [
          {
            id: 1,
            event: 'session_revoked',
            created_at: '2024-01-01T12:00:00Z',
            old_values: null,
            new_values: null,
            ip_address: '10.0.0.1',
            user_agent: 'Mozilla/5.0 Chrome/120.0',
            tags: null
          }
        ],
        meta: { current_page: 1, last_page: 1, total: 1 }
      })
    })
    __setToastMock({ add: vi.fn() })

    const wrapper = mount(SecurityActivitySection)

    await Promise.resolve()
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('settings.security.activity.title')
    expect(wrapper.text()).toContain('settings.security.activity.events.session_revoked')
  })
})
