<script setup lang="ts">
defineI18nRoute(false)

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})

const route = useRoute()
const { t } = useI18n()

const error = computed(() => ({
  statusCode: 404,
  statusMessage: t('errors.page_not_found_short'),
  message: t('errors.page_not_found_with_path', { path: route.path })
}))

// In the dashboard SPA area, unknown routes can otherwise render blank.
// Provide a friendly fallback + quick way back.
</script>

<template>
  <UDashboardPanel>
    <template #body>
      <UError :error="error" :clear="false">
        <template #links>
          <div class="flex items-center justify-center gap-3">
            <UButton
              to="/dashboard"
              size="lg"
              color="primary"
              variant="solid"
              :label="t('errors.back_dashboard')"
            />
          </div>
        </template>
      </UError>
    </template>
  </UDashboardPanel>
</template>
