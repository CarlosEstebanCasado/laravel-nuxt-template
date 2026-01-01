import { formatDistanceToNow } from 'date-fns'
import { ca, enUS, es } from 'date-fns/locale'

export type SecurityAuditEntry = {
  id: number
  event: string
  created_at: string
  old_values: Record<string, any> | null
  new_values: Record<string, any> | null
  ip_address: string | null
  user_agent: string | null
  tags: string | null
}

export const useSecurityAuditFormat = () => {
  const { t, locale } = useI18n()

  const dateLocale = computed(() => {
    if (locale.value === 'ca') return ca
    if (locale.value === 'es') return es
    return enUS
  })

  const formatDateTime = (date: Date) =>
    new Intl.DateTimeFormat(locale.value, { dateStyle: 'medium', timeStyle: 'short' }).format(date)

  const formatAuditTime = (iso: string) => formatDateTime(new Date(iso))
  const formatAuditRelative = (iso: string) =>
    formatDistanceToNow(new Date(iso), { addSuffix: true, locale: dateLocale.value })

  const tWithPlural = (key: string, count: number, params: Record<string, unknown> = {}) =>
    (t as unknown as (key: string, count: number, params?: Record<string, unknown>) => string)(
      key,
      count,
      { ...params, count }
    )

  const auditTitle = (audit: SecurityAuditEntry) => {
    const event = audit.event

    if (event === 'sessions_revoked') {
      const n = Number((audit.new_values as any)?.revoked ?? 0)
      return tWithPlural('settings.security.activity.events.sessions_revoked', n)
    }
    if (event === 'session_revoked') {
      return t('settings.security.activity.events.session_revoked')
    }
    if (event === 'account_deleted') {
      return t('settings.security.activity.events.account_deleted')
    }

    if (event === 'updated') {
      if ((audit.new_values as any)?.password_set_at) return t('settings.security.activity.events.password_changed')
      if ((audit.new_values as any)?.email) return t('settings.security.activity.events.email_changed')
      if ((audit.new_values as any)?.name) return t('settings.security.activity.events.profile_updated')
      return t('settings.security.activity.events.account_updated')
    }
    if (event === 'created') return t('settings.security.activity.events.account_created')
    if (event === 'deleted') return t('settings.security.activity.events.account_deleted')

    return t('settings.security.activity.events.generic', { event })
  }

  return {
    auditTitle,
    formatAuditRelative,
    formatAuditTime
  }
}
