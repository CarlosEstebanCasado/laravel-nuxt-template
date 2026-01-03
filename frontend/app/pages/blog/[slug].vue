<script setup lang="ts">
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

const postKey = computed(() => `posts:${contentPath.value}`)
const { data: post } = await useAsyncData(
  postKey,
  () => queryCollection('posts').path(contentPath.value).first()
)
if (!post.value) {
  throw createError({ statusCode: 404, statusMessage: 'Post not found', fatal: true })
}

const surroundKey = computed(() => `posts:${contentPath.value}-surround`)
const { data: surround } = await useAsyncData(surroundKey, () => {
  return queryCollectionItemSurroundings('posts', contentPath.value, {
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

const title = post.value.seo?.title || post.value.title
const description = post.value.seo?.description || post.value.description

useSeoMeta({
  title,
  ogTitle: title,
  description,
  ogDescription: description
})

if (post.value.image?.src) {
  defineOgImage({
    url: post.value.image.src
  })
} else {
  defineOgImageComponent('Saas', {
    headline: 'Blog'
  })
}
</script>

<template>
  <UContainer v-if="post">
    <UPageHeader
      :title="post.title"
      :description="post.description"
    >
      <template #headline>
        <UBadge
          v-bind="post.badge"
          variant="subtle"
        />
        <span class="text-muted">&middot;</span>
        <time class="text-muted">{{ new Date(post.date).toLocaleDateString('en', { year: 'numeric', month: 'short', day: 'numeric' }) }}</time>
      </template>

      <div class="flex flex-wrap items-center gap-3 mt-4">
        <UButton
          v-for="(author, index) in post.authors"
          :key="index"
          :to="author.to"
          color="neutral"
          variant="subtle"
          target="_blank"
          size="sm"
        >
          <UAvatar
            v-bind="author.avatar"
            alt="Author avatar"
            size="2xs"
          />

          {{ author.name }}
        </UButton>
      </div>
    </UPageHeader>

    <UPage>
      <UPageBody>
        <ContentRenderer
          v-if="post"
          :value="post"
        />

        <USeparator v-if="surround?.length" />

        <UContentSurround :surround="localizedSurround" />
      </UPageBody>

      <template
        v-if="post?.body?.toc?.links?.length"
        #right
      >
        <UContentToc :links="post.body.toc.links" />
      </template>
    </UPage>
  </UContainer>
</template>
