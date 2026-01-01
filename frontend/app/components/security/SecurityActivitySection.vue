<script setup lang="ts">
import type { SecurityAuditEntry } from '~/composables/useSecurityAuditFormat'

const auth = useAuth()
const toast = useToast()
const { t } = useI18n()

const { auditTitle, formatAuditRelative, formatAuditTime } = useSecurityAuditFormat()

const audits = ref<SecurityAuditEntry[]>([])
const auditsMeta = ref<{ current_page: number, last_page: number, total: number } | null>(null)
const isAuditsLoading = ref(false)
const auditsError = ref<string | null>(null)

const isAuditsInitialLoading = computed(() => isAuditsLoading.value && audits.value.length === 0)
const isAuditsLoadingMore = computed(() => isAuditsLoading.value && audits.value.length > 0)

const refreshAudits = async (page = 1) => {
  if (isAuditsLoading.value) {
    return
  }

  const isLoadMore = page > 1

  isAuditsLoading.value = true
  if (!isLoadMore) {
    auditsError.value = null
  }

  try {
    const response = await auth.listAudits(page)

    if (isLoadMore) {
      const existingIds = new Set(audits.value.map((a) => a.id))
      const next = (response.data || []).filter((a) => !existingIds.has(a.id))
      audits.value = [...audits.value, ...next]
    } else {
      audits.value = response.data
    }

    auditsMeta.value = response.meta
  } catch (error: any) {
    const message = error?.data?.message || error?.message || t('settings.security.errors.activity')

    if (isLoadMore) {
      toast.add({
        title: t('settings.security.toasts.load_more_failed'),
        description: message,
        color: 'warning',
        icon: 'i-lucide-alert-triangle',
      })
      return
    }

    audits.value = []
    auditsMeta.value = null
    auditsError.value = message
  } finally {
    isAuditsLoading.value = false
  }
}

onMounted(() => {
  refreshAudits()
})
</script>

<template>
  <UPageCard
    :title="t('settings.security.activity.title')"
    :description="t('settings.security.activity.description')"
    variant="subtle"
    class="mt-6"
  >
    <div class="flex flex-col gap-3 max-w-2xl">
      <div class="flex justify-end">
        <UButton
          :label="t('actions.refresh')"
          icon="i-lucide-refresh-cw"
          color="neutral"
          variant="ghost"
          size="sm"
          :loading="isAuditsLoading"
          @click="refreshAudits()"
        />
      </div>

      <div v-if="isAuditsInitialLoading" class="space-y-2">
        <div class="h-12 rounded-lg border border-default bg-elevated/30 animate-pulse" />
        <div class="h-12 rounded-lg border border-default bg-elevated/20 animate-pulse" />
        <div class="h-12 rounded-lg border border-default bg-elevated/10 animate-pulse" />
      </div>

      <UAlert
        v-else-if="auditsError"
        :title="t('settings.security.activity.unable_title')"
        :description="auditsError"
        color="warning"
        variant="subtle"
      />

      <UAlert
        v-else-if="audits.length === 0"
        :title="t('settings.security.activity.empty_title')"
        :description="t('settings.security.activity.empty_description')"
        icon="i-lucide-activity"
        color="neutral"
        variant="subtle"
      />

      <div v-else class="space-y-2">
        <div
          v-for="audit in audits"
          :key="audit.id"
          class="flex min-w-0 items-start justify-between gap-4 rounded-lg border border-default p-4 bg-elevated/10 overflow-hidden"
        >
          <div class="min-w-0">
            <div class="font-medium truncate">
              {{ auditTitle(audit) }}
            </div>
            <div class="mt-1 text-xs text-muted flex flex-wrap gap-x-4 gap-y-1">
              <span class="inline-flex items-center gap-1">
                <UIcon name="i-lucide-clock" class="size-3.5" />
                {{ formatAuditRelative(audit.created_at) }}
              </span>
              <span class="inline-flex items-center gap-1">
                <UIcon name="i-lucide-calendar" class="size-3.5" />
                {{ formatAuditTime(audit.created_at) }}
              </span>
              <span v-if="audit.ip_address" class="inline-flex items-center gap-1">
                <UIcon name="i-lucide-network" class="size-3.5" />
                {{ audit.ip_address }}
              </span>
            </div>
          </div>

          <UTooltip v-if="audit.user_agent" :text="audit.user_agent" :content="{ align: 'end', collisionPadding: 16 }">
            <div class="shrink-0 text-xs text-muted max-w-40 truncate">
              {{ audit.user_agent }}
            </div>
          </UTooltip>
        </div>

        <div v-if="auditsMeta && auditsMeta.total > audits.length" class="flex justify-end">
          <UButton
            :label="t('actions.load_more')"
            color="neutral"
            variant="subtle"
            size="sm"
            :loading="isAuditsLoadingMore"
            :disabled="isAuditsLoading || auditsMeta.current_page >= auditsMeta.last_page"
            @click="refreshAudits((auditsMeta?.current_page || 1) + 1)"
          />
        </div>
      </div>
    </div>
  </UPageCard>
</template>
