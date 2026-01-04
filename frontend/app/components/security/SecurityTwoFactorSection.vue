<script setup lang="ts">
import { DialogDescription, DialogTitle } from 'reka-ui'

const auth = useAuth()
const toast = useToast()
const { t } = useI18n()

const isEnabled = computed(() => Boolean(auth.user.value?.two_factor_enabled))
const isConfirmed = computed(() => Boolean(auth.user.value?.two_factor_confirmed))

const qrCode = ref<string | null>(null)
const secretKey = ref<string | null>(null)
const recoveryCodes = ref<string[]>([])
const showRecoveryCodes = ref(false)
const confirmationCode = ref('')
const recoveryPassword = ref('')
const recoveryPasswordError = ref<string | null>(null)
const showRecoveryPasswordModal = ref(false)
const isRecoveryPasswordSubmitting = ref(false)
const pendingRecoveryAction = ref<'show' | 'regenerate' | 'disable' | null>(null)
const qrCodeDataUrl = computed(() => {
  if (!qrCode.value) {
    return null
  }

  return `data:image/svg+xml;utf8,${encodeURIComponent(qrCode.value)}`
})

const isEnabling = ref(false)
const isConfirming = ref(false)
const isDisabling = ref(false)
const isRegenerating = ref(false)

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
  return t('settings.security.errors.two_factor')
}

const loadSetup = async () => {
  const [qr, secret] = await Promise.all([
    auth.fetchTwoFactorQrCode(),
    auth.fetchTwoFactorSecret(),
  ])

  qrCode.value = qr.svg
  secretKey.value = secret.secretKey
}

const openRecoveryPasswordModal = (action: 'show' | 'regenerate' | 'disable') => {
  pendingRecoveryAction.value = action
  recoveryPassword.value = ''
  recoveryPasswordError.value = null
  showRecoveryPasswordModal.value = true
}

const closeRecoveryPasswordModal = () => {
  pendingRecoveryAction.value = null
  recoveryPassword.value = ''
  recoveryPasswordError.value = null
  showRecoveryPasswordModal.value = false
}

const handleEnable = async () => {
  if (isEnabling.value) {
    return
  }

  isEnabling.value = true
  try {
    await auth.enableTwoFactor()
    await loadSetup()
    toast.add({
      title: t('settings.security.toasts.two_factor_enabled'),
      description: t('settings.security.toasts.two_factor_enabled_description'),
      color: 'success',
    })
  } catch (error) {
    toast.add({
      title: t('settings.security.toasts.action_failed'),
      description: extractErrorMessage(error),
      color: 'error',
    })
  } finally {
    isEnabling.value = false
  }
}

const handleConfirm = async () => {
  if (isConfirming.value) {
    return
  }

  isConfirming.value = true
  try {
    await auth.confirmTwoFactorEnrollment(confirmationCode.value)
    confirmationCode.value = ''
    try {
      recoveryCodes.value = await auth.fetchTwoFactorRecoveryCodesAfterConfirm()
      showRecoveryCodes.value = true
    } catch (error) {
      toast.add({
        title: t('settings.security.toasts.action_failed'),
        description: extractErrorMessage(error),
        color: 'error',
      })
    }
    toast.add({
      title: t('settings.security.toasts.two_factor_confirmed'),
      description: t('settings.security.toasts.two_factor_confirmed_description'),
      color: 'success',
    })
  } catch (error) {
    toast.add({
      title: t('settings.security.toasts.action_failed'),
      description: extractErrorMessage(error),
      color: 'error',
    })
  } finally {
    isConfirming.value = false
  }
}

const handleDisable = async () => {
  if (isDisabling.value) {
    return
  }

  openRecoveryPasswordModal('disable')
}

const handleRegenerateCodes = async () => {
  if (isRegenerating.value) {
    return
  }

  openRecoveryPasswordModal('regenerate')
}

const handleRecoveryPasswordSubmit = async () => {
  if (isRecoveryPasswordSubmitting.value || !pendingRecoveryAction.value) {
    return
  }

  if (!recoveryPassword.value.trim()) {
    recoveryPasswordError.value = t('settings.security.two_factor_section.password_required')
    return
  }

  isRecoveryPasswordSubmitting.value = true
  recoveryPasswordError.value = null

  try {
    const password = recoveryPassword.value.trim()

    if (pendingRecoveryAction.value === 'show') {
      recoveryCodes.value = await auth.fetchTwoFactorRecoveryCodes(password)
      showRecoveryCodes.value = true
    }

    if (pendingRecoveryAction.value === 'regenerate') {
      isRegenerating.value = true
      recoveryCodes.value = await auth.regenerateTwoFactorRecoveryCodes(password)
      showRecoveryCodes.value = true
      toast.add({
        title: t('settings.security.toasts.two_factor_recovery_generated'),
        description: t('settings.security.toasts.two_factor_recovery_generated_description'),
        color: 'success',
      })
    }

    if (pendingRecoveryAction.value === 'disable') {
      isDisabling.value = true
      await auth.disableTwoFactor(password)
      qrCode.value = null
      secretKey.value = null
      recoveryCodes.value = []
      showRecoveryCodes.value = false
      confirmationCode.value = ''
      toast.add({
        title: t('settings.security.toasts.two_factor_disabled'),
        description: t('settings.security.toasts.two_factor_disabled_description'),
        color: 'success',
      })
    }

    closeRecoveryPasswordModal()
  } catch (error) {
    recoveryPasswordError.value = extractErrorMessage(error)
  } finally {
    isRecoveryPasswordSubmitting.value = false
    isRegenerating.value = false
    isDisabling.value = false
  }
}

const passwordModalDescription = computed(() => {
  if (pendingRecoveryAction.value === 'disable') {
    return t('settings.security.two_factor_section.password_modal_description_disable')
  }

  return t('settings.security.two_factor_section.password_modal_description')
})

const copyRecoveryCode = async (code: string) => {
  try {
    await navigator.clipboard.writeText(code)
    toast.add({
      title: t('settings.security.toasts.recovery_code_copied'),
      color: 'success',
    })
  } catch {
    toast.add({
      title: t('settings.security.toasts.action_failed'),
      description: t('settings.security.errors.copy'),
      color: 'error',
    })
  }
}

const handleToggleRecoveryCodes = () => {
  if (showRecoveryCodes.value) {
    showRecoveryCodes.value = false
    recoveryCodes.value = []
    return
  }

  openRecoveryPasswordModal('show')
}

onMounted(async () => {
  if (!isEnabled.value || isConfirmed.value) {
    return
  }

  try {
    await loadSetup()
  } catch (error) {
    toast.add({
      title: t('settings.security.toasts.action_failed'),
      description: extractErrorMessage(error),
      color: 'error',
    })
  }
})
</script>

<template>
  <UPageCard
    :title="t('settings.security.two_factor_section.title')"
    :description="t('settings.security.two_factor_section.description')"
    variant="subtle"
  >
    <div class="space-y-6">
      <div class="flex items-center justify-between gap-4">
        <div class="text-sm text-slate-600 dark:text-slate-400">
          <span v-if="!isEnabled">{{ t('settings.security.two_factor_section.status_disabled') }}</span>
          <span v-else-if="!isConfirmed">{{ t('settings.security.two_factor_section.status_pending') }}</span>
          <span v-else>{{ t('settings.security.two_factor_section.status_enabled') }}</span>
        </div>
        <div class="flex items-center gap-2">
          <UButton
            v-if="!isEnabled"
            :label="t('settings.security.two_factor_section.enable_button')"
            :loading="isEnabling"
            @click="handleEnable"
          />
          <UButton
            v-else
            color="error"
            variant="soft"
            :label="t('settings.security.two_factor_section.disable_button')"
            :loading="isDisabling"
            @click="handleDisable"
          />
        </div>
      </div>

      <div v-if="isEnabled && !isConfirmed && qrCodeDataUrl" class="grid gap-6 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
        <div class="space-y-3">
          <div class="text-sm font-medium text-slate-800 dark:text-slate-200">
            {{ t('settings.security.two_factor_section.qr_title') }}
          </div>
          <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <img
              :src="qrCodeDataUrl"
              :alt="t('settings.security.two_factor_section.qr_title')"
              class="h-40 w-40"
            >
          </div>
          <div v-if="secretKey" class="text-xs text-slate-500 dark:text-slate-400">
            {{ t('settings.security.two_factor_section.secret_label') }}: <span class="font-mono">{{ secretKey }}</span>
          </div>
        </div>

        <div class="space-y-4">
          <div class="text-sm font-medium text-slate-800 dark:text-slate-200">
            {{ t('settings.security.two_factor_section.confirm_title') }}
          </div>
          <UInput
            v-model="confirmationCode"
            inputmode="numeric"
            autocomplete="one-time-code"
            :placeholder="t('settings.security.two_factor_section.confirm_placeholder')"
          />
          <UButton
            :label="t('settings.security.two_factor_section.confirm_button')"
            :loading="isConfirming"
            @click="handleConfirm"
          />
        </div>
      </div>

      <div v-if="isEnabled" class="space-y-4">
        <div class="flex items-center justify-between gap-4">
          <div>
            <div class="text-sm font-medium text-slate-800 dark:text-slate-200">
              {{ t('settings.security.two_factor_section.recovery_title') }}
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              {{ t('settings.security.two_factor_section.recovery_description') }}
            </p>
          </div>
          <div class="flex items-center gap-2">
            <UButton
              variant="soft"
              :label="showRecoveryCodes ? t('settings.security.two_factor_section.hide_codes') : t('settings.security.two_factor_section.show_codes')"
              @click="handleToggleRecoveryCodes"
            />
            <UButton
              color="neutral"
              variant="outline"
              :label="t('settings.security.two_factor_section.regenerate_button')"
              :loading="isRegenerating"
              @click="handleRegenerateCodes"
            />
          </div>
        </div>

        <div v-if="showRecoveryCodes" class="grid gap-2 sm:grid-cols-2">
          <div
            v-for="code in recoveryCodes"
            :key="code"
            class="flex items-center justify-between gap-2 rounded-md border border-slate-200 px-3 py-2 text-xs font-mono text-slate-700 dark:border-slate-800 dark:text-slate-200"
          >
            <span class="truncate">{{ code }}</span>
            <UButton
              variant="ghost"
              color="neutral"
              size="xs"
              icon="i-lucide-clipboard"
              :aria-label="t('actions.copy')"
              class="hover:bg-slate-200/80 dark:hover:bg-slate-800"
              @click="copyRecoveryCode(code)"
            />
          </div>
        </div>
      </div>

      <UModal v-model:open="showRecoveryPasswordModal">
        <template #body>
          <div class="space-y-4">
            <DialogTitle class="text-base font-semibold text-slate-900 dark:text-slate-100">
              {{ t('settings.security.two_factor_section.password_modal_title') }}
            </DialogTitle>
            <DialogDescription class="text-sm text-slate-600 dark:text-slate-400">
              {{ passwordModalDescription }}
            </DialogDescription>
            <UInput
              v-model="recoveryPassword"
              type="password"
              autocomplete="current-password"
              :placeholder="t('settings.security.two_factor_section.password_placeholder')"
            />
            <p v-if="recoveryPasswordError" class="text-sm text-rose-600">
              {{ recoveryPasswordError }}
            </p>
            <div class="flex justify-end gap-2">
              <UButton
                variant="soft"
                color="neutral"
                :label="t('actions.cancel')"
                @click="closeRecoveryPasswordModal"
              />
              <UButton
                :label="t('actions.confirm')"
                :loading="isRecoveryPasswordSubmitting"
                @click="handleRecoveryPasswordSubmit"
              />
            </div>
          </div>
        </template>
      </UModal>
    </div>
  </UPageCard>
</template>
