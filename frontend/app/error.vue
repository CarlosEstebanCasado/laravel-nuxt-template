<script setup lang="ts">
import type { NuxtError } from '#app'

const { locale, t } = useI18n()

defineProps({
  error: {
    type: Object as PropType<NuxtError>,
    required: true
  }
})

useHead({
  htmlAttrs: {
    lang: locale
  }
})

useSeoMeta({
  title: t('errors.page_not_found_title'),
  description: t('errors.page_not_found_description')
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
              :label="t('errors.back_home')"
            />
          </template>
        </UError>
      </UPage>
    </UContainer>
  </UMain>
</template>
