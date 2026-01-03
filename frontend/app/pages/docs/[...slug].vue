<script setup lang="ts">
definePageMeta({
  layout: 'docs'
})

const route = useRoute()
const { locale } = useI18n()

const contentPath = computed(() => {
  const prefix = `/${locale.value}`
  if (route.path === prefix) {
    return '/'
  }
  if (route.path.startsWith(`${prefix}/`)) {
    const stripped = route.path.slice(prefix.length)
    return stripped === '' ? '/' : stripped
  }
  return route.path
})

const pageKey = computed(() => `docs:${contentPath.value}`)
const { data: page } = await useAsyncData(
  pageKey,
  () => queryCollection('docs').path(contentPath.value).first()
)
if (!page.value) {
  throw createError({ statusCode: 404, statusMessage: 'Page not found', fatal: true })
}

const surroundKey = computed(() => `docs:${contentPath.value}-surround`)
const { data: surround } = await useAsyncData(surroundKey, () => {
  return queryCollectionItemSurroundings('docs', contentPath.value, {
    fields: ['description']
  })
})
const localePath = useLocalePath()
const localizedSurround = computed(() => {
  return (surround.value ?? []).map((item) => {
    if (!item) return item
    const next = { ...item }
    if (next._path) {
      next._path = localePath(next._path)
    }
    if (next.path) {
      next.path = localePath(next.path)
    }
    return next
  })
})

const title = page.value.seo?.title || page.value.title
const description = page.value.seo?.description || page.value.description

useSeoMeta({
  title,
  ogTitle: title,
  description,
  ogDescription: description
})

defineOgImageComponent('Saas')
</script>

<template>
  <UPage v-if="page">
    <UPageHeader
      :title="page.title"
      :description="page.description"
    />

    <UPageBody>
      <ContentRenderer
        v-if="page.body"
        :value="page"
      />

      <USeparator v-if="surround?.length" />

      <UContentSurround :surround="localizedSurround" />
    </UPageBody>

    <template
      v-if="page?.body?.toc?.links?.length"
      #right
    >
      <UContentToc :links="page.body.toc.links" />
    </template>
  </UPage>
</template>
