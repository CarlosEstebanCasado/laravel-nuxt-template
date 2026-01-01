<script setup lang="ts">
const auth = useAuth()
const toast = useToast()
const router = useRouter()
const { t } = useI18n()

const requiresPasswordForSensitiveActions = computed(() =>
  auth.user.value?.auth_provider === 'password' || !!auth.user.value?.password_set_at
)

const isDeleteOpen = ref(false)
const isDeleting = ref(false)

const deleteState = reactive<{ confirmation: string, password?: string }>({
  confirmation: '',
  password: ''
})

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
