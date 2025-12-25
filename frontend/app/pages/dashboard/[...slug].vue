<script setup lang="ts">
definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})

const route = useRoute()

const error = computed(() => ({
  statusCode: 404,
  statusMessage: 'Page not found',
  message: `Page not found: ${route.path}`
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
              label="Back to dashboard"
            />
          </div>
        </template>
      </UError>
    </template>
  </UDashboardPanel>
</template>


