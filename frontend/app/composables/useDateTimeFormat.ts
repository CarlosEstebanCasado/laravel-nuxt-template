export const useDateTimeFormat = () => {
  const auth = useAuth()
  const { locale } = useI18n()

  const timeZone = computed(
    () => auth.preferences.value?.timezone ?? Intl.DateTimeFormat().resolvedOptions().timeZone ?? 'UTC'
  )

  const toDate = (value: Date | string | number) => (value instanceof Date ? value : new Date(value))

  const formatWithTimeZone = (
    value: Date | string | number,
    options: Intl.DateTimeFormatOptions,
    localeOverride?: string
  ) => {
    return new Intl.DateTimeFormat(localeOverride ?? locale.value, {
      timeZone: timeZone.value,
      ...options
    }).format(toDate(value))
  }

  const formatDate = (value: Date | string | number) =>
    formatWithTimeZone(value, { year: 'numeric', month: 'short', day: 'numeric' })

  const formatDateTime = (value: Date | string | number) =>
    formatWithTimeZone(value, { dateStyle: 'medium', timeStyle: 'short' })

  const formatTime = (value: Date | string | number) =>
    formatWithTimeZone(value, { hour: '2-digit', minute: '2-digit', hour12: false })

  const formatDateKey = (value: Date | string | number) =>
    formatWithTimeZone(value, { year: 'numeric', month: '2-digit', day: '2-digit' }, 'en-CA')

  return {
    timeZone,
    formatWithTimeZone,
    formatDate,
    formatDateTime,
    formatTime,
    formatDateKey
  }
}
