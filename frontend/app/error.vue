<script setup lang="ts">
import type { NuxtError } from '#app'

defineProps({
  error: {
    type: Object as PropType<NuxtError>,
    required: true
  }
})

useHead({
  htmlAttrs: {
    lang: 'en'
  }
})

useSeoMeta({
  title: 'Page not found',
  description: 'We are sorry but this page could not be found.'
})
</script>

<template>
  <UMain>
    <UContainer>
      <UPage>
        <UError :error="error" :clear="false">
          <template #links>
            <UButton
              to="/"
              size="lg"
              color="primary"
              variant="solid"
              label="Back to home"
            />
          </template>
        </UError>
      </UPage>
    </UContainer>
  </UMain>
</template>
