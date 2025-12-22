<script setup lang="ts">
import * as z from 'zod'
import type { FormError, FormSubmitEvent } from '@nuxt/ui'

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})

const auth = useAuth()
const router = useRouter()
const toast = useToast()
const isSubmitting = ref(false)
const isLoading = ref(true)

const profileSchema = z.object({
  name: z.string().min(2, 'Too short'),
  email: z.string().email('Invalid email'),
})

type ProfileSchema = z.output<typeof profileSchema>

type ProfileState = Partial<ProfileSchema> & { current_password?: string }

const profile = reactive<ProfileState>({
  name: undefined,
  email: undefined,
  current_password: undefined,
})

const requiresPasswordForEmailChange = computed(() => {
  const user = auth.user.value
  if (!user) return false

  const hasPassword = user.auth_provider === 'password' || !!user.password_set_at
  const currentEmail = user.email ?? ''
  const nextEmail = profile.email ?? ''

  return hasPassword && nextEmail !== '' && nextEmail !== currentEmail
})

const validateProfile = (state: ProfileState): FormError[] => {
  const errors: FormError[] = []

  if (requiresPasswordForEmailChange.value && !state.current_password?.trim()) {
    errors.push({
      name: 'current_password',
      message: 'Please confirm your password to change email',
    })
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
  return (error as any)?.message || 'Unable to update your profile, please try again.'
}

const syncFromUser = () => {
  const user = auth.user.value
  profile.name = user?.name ?? ''
  profile.email = user?.email ?? ''
  profile.current_password = ''
}

onMounted(async () => {
  try {
    await auth.fetchUser(true)
    syncFromUser()
  } finally {
    isLoading.value = false
  }
})

async function onSubmit(event: FormSubmitEvent<ProfileSchema>) {
  if (isSubmitting.value) {
    return
  }

  isSubmitting.value = true
  try {
    const user = await auth.updateProfile({
      name: event.data.name,
      email: event.data.email,
      current_password: requiresPasswordForEmailChange.value ? profile.current_password : undefined,
    })

    toast.add({
      title: 'Profile updated',
      description: 'Your settings have been saved.',
      icon: 'i-lucide-check',
      color: 'success'
    })

    // If the email changed, Fortify will set email_verified_at = null and send a new verification email.
    if (!user?.email_verified_at) {
      toast.add({
        title: 'Verify your email',
        description: 'We sent a verification link to your email. Please verify to continue.',
      })
      await router.push(`/auth/verify-email?redirect=${encodeURIComponent('/dashboard/settings')}`)
      return
    }

    profile.current_password = ''
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
</script>

<template>
  <UForm
    id="settings"
    :schema="profileSchema"
    :state="profile"
    :validate="validateProfile"
    @submit="onSubmit"
  >
    <UPageCard
      title="Profile"
      description="These informations will be displayed publicly."
      variant="naked"
      orientation="horizontal"
      class="mb-4"
    >
      <UButton
        form="settings"
        label="Save changes"
        color="neutral"
        :loading="isSubmitting || isLoading"
        type="submit"
        class="w-fit lg:ms-auto"
      />
    </UPageCard>

    <UPageCard variant="subtle">
      <UFormField
        name="name"
        label="Name"
        description="Will appear on receipts, invoices, and other communication."
        required
        class="flex max-sm:flex-col justify-between items-start gap-4"
      >
        <UInput
          v-model="profile.name"
          autocomplete="off"
          :disabled="isLoading"
        />
      </UFormField>
      <USeparator />
      <UFormField
        name="email"
        label="Email"
        description="Used to sign in, for email receipts and product updates."
        required
        class="flex max-sm:flex-col justify-between items-start gap-4"
      >
        <UInput
          v-model="profile.email"
          type="email"
          autocomplete="off"
          :disabled="isLoading"
        />
      </UFormField>

      <USeparator v-if="requiresPasswordForEmailChange" />
      <UFormField
        v-if="requiresPasswordForEmailChange"
        name="current_password"
        label="Confirm password"
        description="Required to change your email."
        required
        class="flex max-sm:flex-col justify-between items-start gap-4"
      >
        <UInput
          v-model="profile.current_password"
          type="password"
          autocomplete="current-password"
          :disabled="isLoading"
        />
      </UFormField>
    </UPageCard>
  </UForm>
</template>
