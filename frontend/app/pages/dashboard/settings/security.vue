<script setup lang="ts">
import SecurityAccountSection from '~/components/security/SecurityAccountSection.vue'
import SecurityActivitySection from '~/components/security/SecurityActivitySection.vue'
import SecurityPasswordSection from '~/components/security/SecurityPasswordSection.vue'
import SecuritySessionsSection from '~/components/security/SecuritySessionsSection.vue'

defineI18nRoute(false)

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})
</script>

<template>
  <SecurityPasswordSection />
  <SecuritySessionsSection />
  <SecurityActivitySection />
  <SecurityAccountSection />
</template>
