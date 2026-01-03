<script setup lang="ts">
import type { ContentNavigationItem } from '@nuxt/content'

const navigation = inject<Ref<ContentNavigationItem[]>>('navigation')
const localePath = useLocalePath()

const localizeNavigation = (items?: ContentNavigationItem[]): ContentNavigationItem[] => {
  if (!items) return []

  return items.map((item) => {
    const next = { ...item }
    if (next._path) {
      next._path = localePath(next._path)
    }
    if (next.path) {
      next.path = localePath(next.path)
    }
    if (next.children?.length) {
      next.children = localizeNavigation(next.children)
    }
    return next
  })
}

const localizedNavigation = computed(() => localizeNavigation(navigation?.value))
</script>

<template>
  <div>
    <AppHeader />

    <UMain>
      <UContainer>
        <UPage>
          <template #left>
            <UPageAside>
              <template #top>
                <UContentSearchButton :collapsed="false" />
              </template>

              <UContentNavigation
                :navigation="localizedNavigation"
                highlight
              />
            </UPageAside>
          </template>

          <slot />
        </UPage>
      </UContainer>
    </UMain>

    <AppFooter />
  </div>
</template>
