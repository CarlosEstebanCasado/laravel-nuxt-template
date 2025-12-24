<script setup lang="ts">
// Auth pages should not be indexed by search engines.
useSeoMeta({
  robots: 'noindex, nofollow'
})
</script>

<template>
  <div class="h-screen flex items-center justify-center px-4">
    <UButton
      icon="i-lucide-chevron-left"
      to="/"
      size="xl"
      color="neutral"
      variant="subtle"
      class="absolute left-8 top-8 rounded-full z-10"
    />

    <UPageCard
      variant="subtle"
      class="max-w-sm w-full"
    >
      <slot />
    </UPageCard>
  </div>
</template>
