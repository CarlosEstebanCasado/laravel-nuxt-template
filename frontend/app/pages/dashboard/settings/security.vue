<script setup lang="ts">
import * as z from 'zod'
import type { FormError, FormSubmitEvent } from '@nuxt/ui'
import { formatDistanceToNow } from 'date-fns'
import { ca, enUS, es } from 'date-fns/locale'

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})

const auth = useAuth()
const toast = useToast()
const router = useRouter()
const { t, locale } = useI18n()

type SessionInfo = {
  id: string
  ip_address: string | null
  user_agent: string | null
  last_activity: number
  is_current: boolean
}

const sessions = ref<SessionInfo[]>([])
const isSessionsLoading = ref(true)
const sessionsError = ref<string | null>(null)

type AuditEntry = {
  id: number
  event: string
  created_at: string
  old_values: Record<string, any> | null
  new_values: Record<string, any> | null
  ip_address: string | null
  user_agent: string | null
  tags: string | null
}

const audits = ref<AuditEntry[]>([])
const auditsMeta = ref<{ current_page: number, last_page: number, total: number } | null>(null)
// Start as not-loading; `refreshAudits()` flips this while the request is in flight.
// If this starts as true, the first `refreshAudits()` call will early-return and never load.
const isAuditsLoading = ref(false)
const auditsError = ref<string | null>(null)

const isAuditsInitialLoading = computed(() => isAuditsLoading.value && audits.value.length === 0)
const isAuditsLoadingMore = computed(() => isAuditsLoading.value && audits.value.length > 0)

const requiresPasswordForSensitiveActions = computed(() =>
  auth.user.value?.auth_provider === 'password' || !!auth.user.value?.password_set_at
)
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

const formatDateTime = (date: Date) =>
  new Intl.DateTimeFormat(locale.value, { dateStyle: 'medium', timeStyle: 'short' }).format(date)

const formatLastActivity = (unixSeconds: number) => formatDateTime(new Date(unixSeconds * 1000))
const formatLastActivityRelative = (unixSeconds: number) =>
  formatDistanceToNow(new Date(unixSeconds * 1000), { addSuffix: true, locale: dateLocale.value })

const refreshSessions = async () => {
  isSessionsLoading.value = true
  sessionsError.value = null
  try {
    sessions.value = await auth.listSessions()
  } catch (error: any) {
    // If sessions are not enabled (SESSION_DRIVER not database/redis) or user not verified, show a friendly message.
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

const formatAuditTime = (iso: string) => formatDateTime(new Date(iso))
const formatAuditRelative = (iso: string) =>
  formatDistanceToNow(new Date(iso), { addSuffix: true, locale: dateLocale.value })
const tWithPlural = (key: string, count: number, params: Record<string, unknown> = {}) =>
  (t as unknown as (key: string, count: number, params?: Record<string, unknown>) => string)(
    key,
    count,
    { ...params, count }
  )

const auditTitle = (audit: AuditEntry) => {
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

  // Model events
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

const refreshAudits = async (page = 1) => {
  // Prevent accidental double-clicks from interleaving pages and causing duplicates.
  if (isAuditsLoading.value) {
    return
  }

  const isLoadMore = page > 1

  isAuditsLoading.value = true
  if (!isLoadMore) {
    auditsError.value = null
  }

  try {
    const response = await auth.listAudits(page)

    if (isLoadMore) {
      const existingIds = new Set(audits.value.map((a) => a.id))
      const next = (response.data || []).filter((a) => !existingIds.has(a.id))
      audits.value = [...audits.value, ...next]
    } else {
      audits.value = response.data
    }

    auditsMeta.value = response.meta
  } catch (error: any) {
    const message = error?.data?.message || error?.message || t('settings.security.errors.activity')

    // For "Load more", keep already-loaded items visible and just surface the error.
    if (isLoadMore) {
      toast.add({
        title: t('settings.security.toasts.load_more_failed'),
        description: message,
        color: 'warning',
        icon: 'i-lucide-alert-triangle',
      })
      return
    }

    audits.value = []
    auditsMeta.value = null
    auditsError.value = message
  } finally {
    isAuditsLoading.value = false
  }
}

onMounted(() => {
  refreshSessions()
  refreshAudits()
})

const passwordSchema = z.object({
  current_password: z.string().min(8, t('messages.validation.password_min')),
  password: z.string().min(8, t('messages.validation.password_min')),
  password_confirmation: z.string().min(8, t('messages.validation.password_min'))
})

type PasswordSchema = z.output<typeof passwordSchema>

const password = reactive<Partial<PasswordSchema>>({
  current_password: undefined,
  password: undefined,
  password_confirmation: undefined
})

const validate = (state: Partial<PasswordSchema>): FormError[] => {
  const errors: FormError[] = []
  if (state.current_password && state.password && state.current_password === state.password) {
    errors.push({ name: 'password', message: t('messages.validation.passwords_different') })
  }
  if (state.password && state.password_confirmation && state.password !== state.password_confirmation) {
    errors.push({ name: 'password_confirmation', message: t('messages.validation.passwords_mismatch') })
  }
  return errors
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
  return (error as any)?.message || t('settings.security.errors.password')
}

const isSubmitting = ref(false)
const isDeleteOpen = ref(false)
const isDeleting = ref(false)

const deleteState = reactive<{ confirmation: string, password?: string }>({
  confirmation: '',
  password: ''
})

async function onSubmit(event: FormSubmitEvent<PasswordSchema>) {
  if (isSubmitting.value) {
    return
  }

  isSubmitting.value = true
  try {
    await auth.updatePassword(event.data)
    toast.add({
      title: t('settings.security.toasts.password_updated'),
      description: t('settings.security.toasts.password_description'),
      color: 'success',
      icon: 'i-lucide-check'
    })

    password.current_password = undefined
    password.password = undefined
    password.password_confirmation = undefined
  } catch (error) {
    toast.add({
      title: t('settings.security.toasts.action_failed'),
      description: extractErrorMessage(error),
      color: 'error',
    })
  } finally {
    isSubmitting.value = false
  }
}

const extractDeleteErrorMessage = (error: unknown) => {
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
  return (error as any)?.message || t('settings.security.errors.delete')
}

const confirmDelete = async () => {
  if (isDeleting.value) {
    return
  }

  if (deleteState.confirmation !== 'DELETE') {
    toast.add({
      title: t('settings.security.toasts.delete_confirmation_required'),
      description: t('settings.security.toasts.delete_confirmation_description'),
      color: 'error'
    })
    return
  }

  if (requiresPasswordForSensitiveActions.value && !deleteState.password?.trim()) {
    toast.add({
      title: t('settings.security.toasts.delete_password_required'),
      description: t('settings.security.toasts.delete_password_description'),
      color: 'error',
    })
    return
  }

  isDeleting.value = true
  try {
    await auth.deleteAccount({
      confirmation: 'DELETE',
      password: deleteState.password?.trim() ? deleteState.password.trim() : undefined,
    })

    toast.add({
      title: t('settings.security.toasts.account_deleted'),
      description: t('settings.security.toasts.account_deleted_description'),
      color: 'success'
    })

    isDeleteOpen.value = false
    await router.replace('/signup')
  } catch (error) {
    toast.add({
      title: t('settings.security.toasts.delete_failed'),
      description: extractDeleteErrorMessage(error),
      color: 'error'
    })
  } finally {
    isDeleting.value = false
  }
}
</script>

<template>
  <UPageCard
    :title="t('settings.security.password_section.title')"
    :description="t('settings.security.password_section.description')"
    variant="subtle"
  >
    <UForm
      :schema="passwordSchema"
      :state="password"
      :validate="validate"
      class="flex flex-col gap-4 max-w-xs"
      @submit="onSubmit"
    >
      <UFormField name="current_password">
        <UInput
          v-model="password.current_password"
          type="password"
          :placeholder="t('settings.security.password_section.current_placeholder')"
          class="w-full"
        />
      </UFormField>

      <UFormField name="password">
        <UInput
          v-model="password.password"
          type="password"
          :placeholder="t('settings.security.password_section.new_placeholder')"
          class="w-full"
        />
      </UFormField>

      <UFormField name="password_confirmation">
        <UInput
          v-model="password.password_confirmation"
          type="password"
          :placeholder="t('settings.security.password_section.confirm_placeholder')"
          class="w-full"
        />
      </UFormField>

      <UButton :label="t('settings.security.password_section.button')" class="w-fit" type="submit" :loading="isSubmitting" />
    </UForm>
  </UPageCard>

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
                  {{ t('settings.security.sessions.ip_label') }}: <span class="font-medium text-default">{{ session.ip_address || t('settings.security.sessions.unknown_device') }}</span>
                </span>
              </div>
              <div class="flex items-center gap-2 min-w-0">
                <UIcon name="i-lucide-clock" class="size-4 shrink-0" />
                <span class="truncate">
                  {{ t('settings.security.sessions.last_active_label') }}: <span class="font-medium text-default">{{ formatLastActivityRelative(session.last_activity) }}</span>
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

  <UPageCard
    :title="t('settings.security.activity.title')"
    :description="t('settings.security.activity.description')"
    variant="subtle"
    class="mt-6"
  >
    <div class="flex flex-col gap-3 max-w-2xl">
      <div class="flex justify-end">
        <UButton
          :label="t('actions.refresh')"
          icon="i-lucide-refresh-cw"
          color="neutral"
          variant="ghost"
          size="sm"
          :loading="isAuditsLoading"
          @click="refreshAudits()"
        />
      </div>

      <div v-if="isAuditsInitialLoading" class="space-y-2">
        <div class="h-12 rounded-lg border border-default bg-elevated/30 animate-pulse" />
        <div class="h-12 rounded-lg border border-default bg-elevated/20 animate-pulse" />
        <div class="h-12 rounded-lg border border-default bg-elevated/10 animate-pulse" />
      </div>

      <UAlert
        v-else-if="auditsError"
        :title="t('settings.security.activity.unable_title')"
        :description="auditsError"
        color="warning"
        variant="subtle"
      />

      <UAlert
        v-else-if="audits.length === 0"
        :title="t('settings.security.activity.empty_title')"
        :description="t('settings.security.activity.empty_description')"
        icon="i-lucide-activity"
        color="neutral"
        variant="subtle"
      />

      <div v-else class="space-y-2">
        <div
          v-for="audit in audits"
          :key="audit.id"
          class="flex min-w-0 items-start justify-between gap-4 rounded-lg border border-default p-4 bg-elevated/10 overflow-hidden"
        >
          <div class="min-w-0">
            <div class="font-medium truncate">
              {{ auditTitle(audit) }}
            </div>
            <div class="mt-1 text-xs text-muted flex flex-wrap gap-x-4 gap-y-1">
              <span class="inline-flex items-center gap-1">
                <UIcon name="i-lucide-clock" class="size-3.5" />
                {{ formatAuditRelative(audit.created_at) }}
              </span>
              <span class="inline-flex items-center gap-1">
                <UIcon name="i-lucide-calendar" class="size-3.5" />
                {{ formatAuditTime(audit.created_at) }}
              </span>
              <span v-if="audit.ip_address" class="inline-flex items-center gap-1">
                <UIcon name="i-lucide-network" class="size-3.5" />
                {{ audit.ip_address }}
              </span>
            </div>
          </div>

          <UTooltip v-if="audit.user_agent" :text="audit.user_agent" :content="{ align: 'end', collisionPadding: 16 }">
            <div class="shrink-0 text-xs text-muted max-w-40 truncate">
              {{ audit.user_agent }}
            </div>
          </UTooltip>
        </div>

        <div v-if="auditsMeta && auditsMeta.total > audits.length" class="flex justify-end">
          <UButton
            :label="t('actions.load_more')"
            color="neutral"
            variant="subtle"
            size="sm"
            :loading="isAuditsLoadingMore"
            :disabled="isAuditsLoading || auditsMeta.current_page >= auditsMeta.last_page"
            @click="refreshAudits((auditsMeta?.current_page || 1) + 1)"
          />
        </div>
      </div>
    </div>
  </UPageCard>

  <UPageCard
    :title="t('settings.security.account.title')"
    :description="t('settings.security.account.description')"
    class="bg-linear-to-tl from-error/10 from-5% to-default"
  >
    <template #footer>
      <UModal v-model:open="isDeleteOpen" :title="t('settings.security.modals.account.title')" :description="t('settings.security.modals.account.description')">
        <UButton :label="t('actions.delete_account')" color="error" />

        <template #body>
          <div class="space-y-4">
            <UAlert
              :title="t('settings.security.modals.account.alert_title')"
              :description="t('settings.security.modals.account.alert_description')"
              color="error"
              variant="subtle"
            />

            <UFormField name="confirmation" :label="t('settings.security.modals.account.confirm_label')">
              <UInput v-model="deleteState.confirmation" placeholder="DELETE" />
            </UFormField>

            <UFormField
              v-if="requiresPasswordForSensitiveActions"
              name="password"
              :label="t('settings.security.modals.account.password_label')"
              required
            >
              <UInput v-model="deleteState.password" type="password" :placeholder="t('settings.security.modals.account.password_placeholder')" />
            </UFormField>

            <div class="flex justify-end gap-2">
              <UButton
                :label="t('actions.cancel')"
                color="neutral"
                variant="subtle"
                :disabled="isDeleting"
                @click="isDeleteOpen = false"
              />
              <UButton
                :label="t('actions.delete_permanently')"
                color="error"
                :loading="isDeleting"
                @click="confirmDelete"
              />
            </div>
          </div>
        </template>
      </UModal>
    </template>
  </UPageCard>
</template>
