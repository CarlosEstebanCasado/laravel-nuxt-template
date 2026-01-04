<script setup lang="ts">
import SecurityAccountSection from '~/components/security/SecurityAccountSection.vue'
import SecurityActivitySection from '~/components/security/SecurityActivitySection.vue'
import SecurityPasswordSection from '~/components/security/SecurityPasswordSection.vue'
import SecuritySessionsSection from '~/components/security/SecuritySessionsSection.vue'
import SecurityTwoFactorSection from '~/components/security/SecurityTwoFactorSection.vue'

defineI18nRoute(false)

definePageMeta({
  layout: 'dashboard',
  middleware: 'auth'
})
</script>

<template>
  <SecurityPasswordSection />
  <SecurityTwoFactorSection />
  <SecuritySessionsSection />
  <SecurityActivitySection />
  <SecurityAccountSection />
</template>
