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

onMounted(() => {
  refreshSessions()
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

  <UPageCard variant="subtle" class="mt-6" :ui="{ container: 'p-0 sm:p-0 gap-y-0', wrapper: 'items-stretch', header: 'p-4 mb-0 border-b border-default' }">
    <template #header>
      <div class="flex items-start justify-between gap-4">
        <div>
          <h3 class="text-base font-semibold">
            Sessions
          </h3>
          <p class="text-sm text-muted">
            Where your account is currently signed in.
          </p>
        </div>

        <UButton
          label="Refresh"
          icon="i-lucide-refresh-cw"
          color="neutral"
          variant="ghost"
          size="sm"
          class="w-fit"
          :loading="isSessionsLoading"
          @click="refreshSessions"
        />
      </div>
    </template>

    <div class="p-4">
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
              <div class="text-xs text-muted truncate max-w-full">
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
        </div>
      </div>
    </div>
  </UPageCard>

  <UPageCard
    title="Account"
    description="No longer want to use our service? You can delete your account here. This action is not reversible. All information related to this account will be deleted permanently."
    class="bg-gradient-to-tl from-error/10 from-5% to-default"
  >
    <template #footer>
      <UModal v-model:open="isDeleteOpen" title="Delete account" description="This action is permanent.">
        <UButton label="Delete account" color="error" />

        <template #body>
          <div class="space-y-4">
            <UAlert
              title="This cannot be undone"
              description="Type DELETE to confirm. If you created your account with email/password, you can also enter your password for extra safety."
              color="error"
              variant="subtle"
            />

            <UFormField name="confirmation" label="Type DELETE to confirm">
              <UInput v-model="deleteState.confirmation" placeholder="DELETE" />
            </UFormField>

            <UFormField name="password" label="Password (optional)">
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
