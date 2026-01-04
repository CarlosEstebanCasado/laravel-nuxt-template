<script setup lang="ts">
import { formatDistanceToNow } from 'date-fns'
import { ca, enUS, es } from 'date-fns/locale'

type SessionInfo = {
  id: string
  ip_address: string | null
  user_agent: string | null
  last_activity: number
  is_current: boolean
}

const auth = useAuth()
const toast = useToast()
const { t, locale } = useI18n()

const sessions = ref<SessionInfo[]>([])
const isSessionsLoading = ref(true)
const sessionsError = ref<string | null>(null)

const hasOtherSessions = computed(() => sessions.value.some((session) => !session.is_current))

const isRevokeOthersOpen = ref(false)
const isRevokingOthers = ref(false)

const isRevokeSessionOpen = ref(false)
const isRevokingSession = ref(false)
const sessionToRevoke = ref<SessionInfo | null>(null)

const parseUserAgent = (ua: string | null) => {
  const value = ua ?? ''
  const browser = value.includes('Edg/')
    ? 'Edge'
    : value.includes('Chrome/')
      ? 'Chrome'
      : value.includes('Firefox/')
        ? 'Firefox'
        : value.includes('Safari/') && !value.includes('Chrome/')
          ? 'Safari'
          : 'Browser'

  const os = value.includes('Windows')
    ? 'Windows'
    : value.includes('Mac OS X') && !value.includes('iPhone') && !value.includes('iPad')
      ? 'macOS'
      : value.includes('Linux')
        ? 'Linux'
        : value.includes('Android')
          ? 'Android'
          : value.includes('iPhone') || value.includes('iPad')
            ? 'iOS'
            : 'OS'

  const isMobile = value.includes('Mobile') || value.includes('Android') || value.includes('iPhone') || value.includes('iPad')

  return { browser, os, isMobile }
}

const dateLocale = computed(() => {
  if (locale.value === 'ca') return ca
  if (locale.value === 'es') return es
  return enUS
})

const timeZone = computed(
  () => auth.preferences.value?.timezone ?? Intl.DateTimeFormat().resolvedOptions().timeZone ?? 'UTC'
)

const formatDateTime = (date: Date) =>
  new Intl.DateTimeFormat(locale.value, {
    dateStyle: 'medium',
    timeStyle: 'short',
    timeZone: timeZone.value
  }).format(date)

const formatLastActivity = (unixSeconds: number) => formatDateTime(new Date(unixSeconds * 1000))
const formatLastActivityRelative = (unixSeconds: number) =>
  formatDistanceToNow(new Date(unixSeconds * 1000), { addSuffix: true, locale: dateLocale.value })

const refreshSessions = async () => {
  isSessionsLoading.value = true
  sessionsError.value = null
  try {
    sessions.value = await auth.listSessions()
  } catch (error: any) {
    sessions.value = []
    sessionsError.value = error?.data?.message || error?.message || t('settings.security.errors.sessions')
  } finally {
    isSessionsLoading.value = false
  }
}

const confirmRevokeOtherSessions = async () => {
  if (isRevokingOthers.value) {
    return
  }

  isRevokingOthers.value = true
  try {
    const result = await auth.revokeOtherSessions()

    toast.add({
      title: t('settings.security.toasts.sessions_updated'),
      description: t('settings.security.toasts.sessions_description', { count: result.revoked }),
      color: 'success',
      icon: 'i-lucide-check',
    })

    isRevokeOthersOpen.value = false
    await refreshSessions()
  } catch (error) {
    toast.add({
      title: t('settings.security.toasts.action_failed'),
      description: extractErrorMessage(error),
      color: 'error',
    })
  } finally {
    isRevokingOthers.value = false
  }
}

const openRevokeSession = (session: SessionInfo) => {
  sessionToRevoke.value = session
  isRevokeSessionOpen.value = true
}

const confirmRevokeSession = async () => {
  if (isRevokingSession.value || !sessionToRevoke.value) {
    return
  }

  isRevokingSession.value = true
  try {
    await auth.revokeSession(sessionToRevoke.value.id)

    toast.add({
      title: t('settings.security.toasts.session_closed'),
      description: t('settings.security.toasts.session_closed_description'),
      color: 'success',
      icon: 'i-lucide-check',
    })

    isRevokeSessionOpen.value = false
    sessionToRevoke.value = null
    await refreshSessions()
  } catch (error) {
    toast.add({
      title: t('settings.security.toasts.action_failed'),
      description: extractErrorMessage(error),
      color: 'error',
    })
  } finally {
    isRevokingSession.value = false
  }
}

const extractErrorMessage = (error: unknown) => {
  const data = (error as any)?.data ?? (error as any)?.response?._data
  if (data?.errors) {
    const firstError = Object.values(data.errors).flat()[0]
    if (typeof firstError === 'string') {
      return firstError
    }
  }
  if (typeof data?.message === 'string') {
    return data.message
  }
  return (error as any)?.message || t('settings.security.errors.sessions')
}

onMounted(() => {
  refreshSessions()
})
</script>

<template>
  <UPageCard
    :title="t('settings.security.sessions.title')"
    :description="t('settings.security.sessions.description')"
    variant="subtle"
    class="mt-6"
  >
    <div class="flex flex-col gap-3 max-w-2xl">
      <div class="flex flex-wrap items-center justify-between gap-2">
        <UButton
          :label="t('actions.close_other_sessions')"
          icon="i-lucide-log-out"
          color="neutral"
          variant="subtle"
          size="sm"
          :disabled="!hasOtherSessions || isSessionsLoading"
          @click="isRevokeOthersOpen = true"
        />

        <UButton
          :label="t('actions.refresh')"
          icon="i-lucide-refresh-cw"
          color="neutral"
          variant="ghost"
          size="sm"
          :loading="isSessionsLoading"
          @click="refreshSessions"
        />
      </div>

      <div v-if="isSessionsLoading" class="space-y-2">
        <div class="h-16 rounded-lg border border-default bg-elevated/30 animate-pulse" />
        <div class="h-16 rounded-lg border border-default bg-elevated/20 animate-pulse" />
      </div>

      <UAlert
        v-else-if="sessionsError"
        :title="t('settings.security.sessions.unable_title')"
        :description="sessionsError"
        color="warning"
        variant="subtle"
      />

      <UAlert
        v-else-if="sessions.length === 0"
        :title="t('settings.security.sessions.none_title')"
        :description="t('settings.security.sessions.none_description')"
        icon="i-lucide-monitor"
        color="neutral"
        variant="subtle"
      />

      <div v-else class="space-y-2">
        <div
          v-for="session in sessions"
          :key="session.id"
          class="flex min-w-0 items-start gap-4 rounded-lg border border-default p-4 bg-elevated/10 overflow-hidden"
        >
          <div class="mt-0.5 shrink-0">
            <UIcon
              :name="parseUserAgent(session.user_agent).isMobile ? 'i-lucide-smartphone' : 'i-lucide-monitor'"
              class="size-5 text-muted"
            />
          </div>

          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <div class="font-medium truncate max-w-full">
                {{ parseUserAgent(session.user_agent).browser }} · {{ parseUserAgent(session.user_agent).os }}
              </div>
              <UBadge v-if="session.is_current" color="success" variant="subtle">
                {{ t('settings.security.sessions.current_badge') }}
              </UBadge>
            </div>

            <UTooltip :text="session.user_agent || t('settings.security.sessions.unknown_device')" :content="{ align: 'start', collisionPadding: 16 }">
              <div class="text-xs text-muted line-clamp-2">
                {{ session.user_agent || t('settings.security.sessions.unknown_device') }}
              </div>
            </UTooltip>

            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 text-xs text-muted">
              <div class="flex items-center gap-2 min-w-0">
                <UIcon name="i-lucide-network" class="size-4 shrink-0" />
                <span class="truncate">
                  {{ t('settings.security.sessions.ip_label') }}:
                  <span class="font-medium text-default">{{ session.ip_address || t('settings.security.sessions.unknown_device') }}</span>
                </span>
              </div>
              <div class="flex items-center gap-2 min-w-0">
                <UIcon name="i-lucide-clock" class="size-4 shrink-0" />
                <span class="truncate">
                  {{ t('settings.security.sessions.last_active_label') }}:
                  <span class="font-medium text-default">{{ formatLastActivityRelative(session.last_activity) }}</span>
                </span>
              </div>
              <div class="flex items-center gap-2 sm:col-span-2 min-w-0">
                <UIcon name="i-lucide-calendar" class="size-4 shrink-0" />
                <span class="truncate">
                  {{ formatLastActivity(session.last_activity) }}
                </span>
              </div>
            </div>
          </div>

          <div class="shrink-0">
            <UButton
              v-if="!session.is_current"
              :label="t('actions.sign_out')"
              icon="i-lucide-log-out"
              color="neutral"
              variant="ghost"
              size="sm"
              @click="openRevokeSession(session)"
            />
          </div>
        </div>
      </div>
    </div>

    <UModal
      v-model:open="isRevokeOthersOpen"
      :title="t('settings.security.modals.close_sessions.title')"
      :description="t('settings.security.modals.close_sessions.description')"
    >
      <template #body>
        <div class="space-y-4">
          <UAlert
            :title="t('settings.security.modals.close_sessions.alert_title')"
            :description="t('settings.security.modals.close_sessions.alert_description')"
            icon="i-lucide-shield"
            color="neutral"
            variant="subtle"
          />

          <div class="flex justify-end gap-2">
            <UButton
              :label="t('actions.cancel')"
              color="neutral"
              variant="subtle"
              :disabled="isRevokingOthers"
              @click="isRevokeOthersOpen = false"
            />
            <UButton
              :label="t('actions.close_other_sessions')"
              color="neutral"
              :loading="isRevokingOthers"
              @click="confirmRevokeOtherSessions"
            />
          </div>
        </div>
      </template>
    </UModal>

    <UModal
      v-model:open="isRevokeSessionOpen"
      :title="t('settings.security.modals.sign_out.title')"
      :description="t('settings.security.modals.sign_out.description')"
    >
      <template #body>
        <div class="space-y-4">
          <UAlert
            :title="t('settings.security.modals.sign_out.alert_title')"
            :description="sessionToRevoke?.user_agent || t('settings.security.sessions.unknown_device')"
            icon="i-lucide-log-out"
            color="neutral"
            variant="subtle"
          />

          <div class="flex justify-end gap-2">
            <UButton
              :label="t('actions.cancel')"
              color="neutral"
              variant="subtle"
              :disabled="isRevokingSession"
              @click="isRevokeSessionOpen = false"
            />
            <UButton
              :label="t('settings.security.sessions.sign_out')"
              color="neutral"
              :loading="isRevokingSession"
              @click="confirmRevokeSession"
            />
          </div>
        </div>
      </template>
    </UModal>
  </UPageCard>
</template>
