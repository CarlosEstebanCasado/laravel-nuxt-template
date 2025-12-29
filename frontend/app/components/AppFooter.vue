<script setup lang="ts">
const { t } = useI18n()

const columns = computed(() => [{
  label: t('footer.resources'),
  children: [{
    label: t('footer.help_center')
  }, {
    label: t('footer.docs')
  }, {
    label: t('footer.roadmap')
  }, {
    label: t('footer.changelog')
  }]
}, {
  label: t('footer.features'),
  children: [{
    label: t('footer.affiliates')
  }, {
    label: t('footer.portal')
  }, {
    label: t('footer.jobs')
  }, {
    label: t('footer.sponsors')
  }]
}, {
  label: t('footer.company'),
  children: [{
    label: t('footer.about')
  }, {
    label: t('footer.pricing')
  }, {
    label: t('footer.careers')
  }, {
    label: t('footer.blog')
  }]
}])

const toast = useToast()

const email = ref('')
const loading = ref(false)

function onSubmit() {
  loading.value = true

  toast.add({
    title: t('footer.subscribe_success_title'),
    description: t('footer.subscribe_success_description')
  })
}
</script>

<template>
  <USeparator
    icon="i-simple-icons-nuxtdotjs"
    class="h-px"
  />

  <UFooter :ui="{ top: 'border-b border-default' }">
    <template #top>
      <UContainer>
        <UFooterColumns :columns="columns">
          <template #right>
            <form @submit.prevent="onSubmit">
              <UFormField
                name="email"
                :label="t('footer.newsletter_label')"
                size="lg"
              >
                <UInput
                  v-model="email"
                  type="email"
                  class="w-full"
                  :placeholder="t('footer.newsletter_placeholder')"
                >
                  <template #trailing>
                    <UButton
                      type="submit"
                      size="xs"
                      color="neutral"
                      :label="t('actions.subscribe')"
                    />
                  </template>
                </UInput>
              </UFormField>
            </form>
          </template>
        </UFooterColumns>
      </UContainer>
    </template>

    <template #left>
      <p class="text-muted text-sm">
        {{ t('footer.copyright', { year: new Date().getFullYear() }) }}
      </p>
    </template>

    <template #right>
      <UButton
        to="https://go.nuxt.com/discord"
        target="_blank"
        icon="i-simple-icons-discord"
        aria-label="Nuxt on Discord"
        color="neutral"
        variant="ghost"
      />
      <UButton
        to="https://go.nuxt.com/x"
        target="_blank"
        icon="i-simple-icons-x"
        aria-label="Nuxt on X"
        color="neutral"
        variant="ghost"
      />
      <UButton
        to="https://github.com/nuxt-ui-templates/saas"
        target="_blank"
        icon="i-simple-icons-github"
        aria-label="Nuxt UI on GitHub"
        color="neutral"
        variant="ghost"
      />
    </template>
  </UFooter>
</template>
