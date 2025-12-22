<script setup lang="ts">
import * as z from 'zod'
import type { FormError, FormSubmitEvent } from '@nuxt/ui'
import { format, formatDistanceToNow } from 'date-fns'

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})

const auth = useAuth()
const toast = useToast()
const router = useRouter()

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
const isAuditsLoading = ref(true)
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

const formatLastActivity = (unixSeconds: number) => format(new Date(unixSeconds * 1000), 'yyyy-MM-dd HH:mm:ss')
const formatLastActivityRelative = (unixSeconds: number) =>
  formatDistanceToNow(new Date(unixSeconds * 1000), { addSuffix: true })

const refreshSessions = async () => {
  isSessionsLoading.value = true
  sessionsError.value = null
  try {
    sessions.value = await auth.listSessions()
  } catch (error: any) {
    // If sessions are not enabled (SESSION_DRIVER not database/redis) or user not verified, show a friendly message.
    sessions.value = []
    sessionsError.value = error?.data?.message || error?.message || 'Unable to load sessions.'
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
      title: 'Sessions updated',
      description: `Signed out from ${result.revoked} other session${result.revoked === 1 ? '' : 's'}.`,
      color: 'success',
      icon: 'i-lucide-check',
    })

    isRevokeOthersOpen.value = false
    await refreshSessions()
  } catch (error) {
    toast.add({
      title: 'Action failed',
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
      title: 'Session closed',
      description: 'That session has been signed out.',
      color: 'success',
      icon: 'i-lucide-check',
    })

    isRevokeSessionOpen.value = false
    sessionToRevoke.value = null
    await refreshSessions()
  } catch (error) {
    toast.add({
      title: 'Action failed',
      description: extractErrorMessage(error),
      color: 'error',
    })
  } finally {
    isRevokingSession.value = false
  }
}

const formatAuditTime = (iso: string) => format(new Date(iso), 'yyyy-MM-dd HH:mm:ss')
const formatAuditRelative = (iso: string) => formatDistanceToNow(new Date(iso), { addSuffix: true })

const auditTitle = (audit: AuditEntry) => {
  const event = audit.event

  if (event === 'sessions_revoked') {
    const n = Number((audit.new_values as any)?.revoked ?? 0)
    return n > 0 ? `Closed ${n} other session${n === 1 ? '' : 's'}` : 'Closed other sessions'
  }
  if (event === 'session_revoked') {
    return 'Closed a session'
  }
  if (event === 'account_deleted') {
    return 'Account deleted'
  }

  // Model events
  if (event === 'updated') {
    if ((audit.new_values as any)?.password_set_at) return 'Password changed'
    if ((audit.new_values as any)?.email) return 'Email changed'
    if ((audit.new_values as any)?.name) return 'Profile updated'
    return 'Account updated'
  }
  if (event === 'created') return 'Account created'
  if (event === 'deleted') return 'Account deleted'

  return event
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
    const message = error?.data?.message || error?.message || 'Unable to load activity.'

    // For "Load more", keep already-loaded items visible and just surface the error.
    if (isLoadMore) {
      toast.add({
        title: 'Unable to load more activity',
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
  current_password: z.string().min(8, 'Must be at least 8 characters'),
  password: z.string().min(8, 'Must be at least 8 characters'),
  password_confirmation: z.string().min(8, 'Must be at least 8 characters')
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
    errors.push({ name: 'password', message: 'Passwords must be different' })
  }
  if (state.password && state.password_confirmation && state.password !== state.password_confirmation) {
    errors.push({ name: 'password_confirmation', message: 'Passwords do not match' })
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
  return (error as any)?.message || 'Unable to update your password, please try again.'
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
      title: 'Password updated',
      description: 'Your password has been changed successfully.',
      color: 'success',
      icon: 'i-lucide-check'
    })

    password.current_password = undefined
    password.password = undefined
    password.password_confirmation = undefined
  } catch (error) {
    toast.add({
      title: 'Update failed',
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
  return (error as any)?.message || 'Unable to delete your account, please try again.'
}

const confirmDelete = async () => {
  if (isDeleting.value) {
    return
  }

  if (deleteState.confirmation !== 'DELETE') {
    toast.add({
      title: 'Confirmation required',
      description: 'Type DELETE to confirm account deletion.',
      color: 'error'
    })
    return
  }

  if (requiresPasswordForSensitiveActions.value && !deleteState.password?.trim()) {
    toast.add({
      title: 'Password required',
      description: 'Please confirm your password to delete your account.',
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
      title: 'Account deleted',
      description: 'Your account has been removed.',
      color: 'success'
    })

    isDeleteOpen.value = false
    await router.replace('/signup')
  } catch (error) {
    toast.add({
      title: 'Delete failed',
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
    title="Password"
    description="Confirm your current password before setting a new one."
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
          placeholder="Current password"
          class="w-full"
        />
      </UFormField>

      <UFormField name="password">
        <UInput
          v-model="password.password"
          type="password"
          placeholder="New password"
          class="w-full"
        />
      </UFormField>

      <UFormField name="password_confirmation">
        <UInput
          v-model="password.password_confirmation"
          type="password"
          placeholder="Confirm new password"
          class="w-full"
        />
      </UFormField>

      <UButton label="Update" class="w-fit" type="submit" :loading="isSubmitting" />
    </UForm>
  </UPageCard>

  <UPageCard
    title="Sessions"
    description="Where your account is currently signed in."
    variant="subtle"
    class="mt-6"
  >
    <div class="flex flex-col gap-3 max-w-2xl">
      <div class="flex flex-wrap items-center justify-between gap-2">
        <UButton
          label="Close other sessions"
          icon="i-lucide-log-out"
          color="neutral"
          variant="subtle"
          size="sm"
          :disabled="!hasOtherSessions || isSessionsLoading"
          @click="isRevokeOthersOpen = true"
        />

        <UButton
          label="Refresh"
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
        title="Unable to load sessions"
        :description="sessionsError"
        color="warning"
        variant="subtle"
      />

      <UAlert
        v-else-if="sessions.length === 0"
        title="No active sessions"
        description="We couldn't find any active sessions for your account."
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
                Current session
              </UBadge>
            </div>

            <UTooltip :text="session.user_agent || 'Unknown device'" :content="{ align: 'start', collisionPadding: 16 }">
              <div class="text-xs text-muted line-clamp-2">
                {{ session.user_agent || 'Unknown device' }}
              </div>
            </UTooltip>

            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 text-xs text-muted">
              <div class="flex items-center gap-2 min-w-0">
                <UIcon name="i-lucide-network" class="size-4 shrink-0" />
                <span class="truncate">
                  IP: <span class="font-medium text-default">{{ session.ip_address || 'Unknown' }}</span>
                </span>
              </div>
              <div class="flex items-center gap-2 min-w-0">
                <UIcon name="i-lucide-clock" class="size-4 shrink-0" />
                <span class="truncate">
                  Last active: <span class="font-medium text-default">{{ formatLastActivityRelative(session.last_activity) }}</span>
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
              label="Sign out"
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
      title="Close other sessions"
      description="This will sign you out from other browsers/devices where your account is currently active."
    >
      <template #body>
        <div class="space-y-4">
          <UAlert
            title="You will stay signed in on this device"
            description="If you think someone else has access, closing other sessions is a good first step."
            icon="i-lucide-shield"
            color="neutral"
            variant="subtle"
          />

          <div class="flex justify-end gap-2">
            <UButton
              label="Cancel"
              color="neutral"
              variant="subtle"
              :disabled="isRevokingOthers"
              @click="isRevokeOthersOpen = false"
            />
            <UButton
              label="Close other sessions"
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
      title="Sign out session"
      description="This will sign out the selected session."
    >
      <template #body>
        <div class="space-y-4">
          <UAlert
            title="This session will be signed out"
            :description="sessionToRevoke?.user_agent || 'Unknown device'"
            icon="i-lucide-log-out"
            color="neutral"
            variant="subtle"
          />

          <div class="flex justify-end gap-2">
            <UButton
              label="Cancel"
              color="neutral"
              variant="subtle"
              :disabled="isRevokingSession"
              @click="isRevokeSessionOpen = false"
            />
            <UButton
              label="Sign out session"
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
    title="Recent activity"
    description="Security-related actions on your account."
    variant="subtle"
    class="mt-6"
  >
    <div class="flex flex-col gap-3 max-w-2xl">
      <div class="flex justify-end">
        <UButton
          label="Refresh"
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
        title="Unable to load activity"
        :description="auditsError"
        color="warning"
        variant="subtle"
      />

      <UAlert
        v-else-if="audits.length === 0"
        title="No activity yet"
        description="When you perform actions like changing your password or closing sessions, they will appear here."
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
            label="Load more"
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
    title="Account"
    description="No longer want to use our service? You can delete your account here. This action is not reversible. All information related to this account will be deleted permanently."
    class="bg-linear-to-tl from-error/10 from-5% to-default"
  >
    <template #footer>
      <UModal v-model:open="isDeleteOpen" title="Delete account" description="This action is permanent.">
        <UButton label="Delete account" color="error" />

        <template #body>
          <div class="space-y-4">
            <UAlert
              title="This cannot be undone"
              description="Type DELETE to confirm. If your account has a password, you'll be asked to confirm it."
              color="error"
              variant="subtle"
            />

            <UFormField name="confirmation" label="Type DELETE to confirm">
              <UInput v-model="deleteState.confirmation" placeholder="DELETE" />
            </UFormField>

            <UFormField
              v-if="requiresPasswordForSensitiveActions"
              name="password"
              label="Password"
              required
            >
              <UInput v-model="deleteState.password" type="password" placeholder="Your current password" />
            </UFormField>

            <div class="flex justify-end gap-2">
              <UButton
                label="Cancel"
                color="neutral"
                variant="subtle"
                :disabled="isDeleting"
                @click="isDeleteOpen = false"
              />
              <UButton
                label="Delete permanently"
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
