<script setup lang="ts">
import { DateFormatter, CalendarDate, today } from '@internationalized/date'
import type { Range } from '~/types'

const selected = defineModel<Range>({ required: true })

const { t, locale } = useI18n()
const { timeZone } = useDateTimeFormat()

const dateFormatter = computed(() => new DateFormatter(locale.value, {
  dateStyle: 'medium',
  timeZone: timeZone.value
}))

const formatDate = (date: Date) => dateFormatter.value.format(date)

const rangeOptions = computed(() => [
  { label: t('dashboard.ranges.last_7'), days: 7 },
  { label: t('dashboard.ranges.last_14'), days: 14 },
  { label: t('dashboard.ranges.last_30'), days: 30 },
  { label: t('dashboard.ranges.last_3_months'), months: 3 },
  { label: t('dashboard.ranges.last_6_months'), months: 6 },
  { label: t('dashboard.ranges.last_year'), years: 1 }
])

const toCalendarDate = (date: Date) => {
  const parts = new Intl.DateTimeFormat('en-CA', {
    timeZone: timeZone.value,
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  }).formatToParts(date)

  const getPart = (type: string) => parts.find((part) => part.type === type)?.value
  const year = Number(getPart('year'))
  const month = Number(getPart('month'))
  const day = Number(getPart('day'))

  if (!Number.isFinite(year) || !Number.isFinite(month) || !Number.isFinite(day)) {
    return new CalendarDate(date.getUTCFullYear(), date.getUTCMonth() + 1, date.getUTCDate())
  }

  return new CalendarDate(year, month, day)
}

const calendarRange = computed({
  get: () => ({
    start: selected.value.start ? toCalendarDate(selected.value.start) : undefined,
    end: selected.value.end ? toCalendarDate(selected.value.end) : undefined
  }),
  set: (newValue: { start: CalendarDate | null, end: CalendarDate | null }) => {
    selected.value = {
      start: newValue.start ? newValue.start.toDate(timeZone.value) : new Date(),
      end: newValue.end ? newValue.end.toDate(timeZone.value) : new Date()
    }
  }
})

const isRangeSelected = (range: { days?: number, months?: number, years?: number }) => {
  if (!selected.value.start || !selected.value.end) return false

  const currentDate = today(timeZone.value)
  let startDate = currentDate.copy()

  if (range.days) {
    startDate = startDate.subtract({ days: range.days })
  } else if (range.months) {
    startDate = startDate.subtract({ months: range.months })
  } else if (range.years) {
    startDate = startDate.subtract({ years: range.years })
  }

  const selectedStart = toCalendarDate(selected.value.start)
  const selectedEnd = toCalendarDate(selected.value.end)

  return selectedStart.compare(startDate) === 0 && selectedEnd.compare(currentDate) === 0
}

const selectRange = (range: { days?: number, months?: number, years?: number }) => {
  const endDate = today(timeZone.value)
  let startDate = endDate.copy()

  if (range.days) {
    startDate = startDate.subtract({ days: range.days })
  } else if (range.months) {
    startDate = startDate.subtract({ months: range.months })
  } else if (range.years) {
    startDate = startDate.subtract({ years: range.years })
  }

  selected.value = {
    start: startDate.toDate(timeZone.value),
    end: endDate.toDate(timeZone.value)
  }
}
</script>

<template>
  <UPopover :content="{ align: 'start' }" :modal="true">
    <UButton
      color="neutral"
      variant="ghost"
      icon="i-lucide-calendar"
      class="data-[state=open]:bg-elevated group"
    >
      <span class="truncate">
        <template v-if="selected.start">
          <template v-if="selected.end">
            {{ formatDate(selected.start) }} - {{ formatDate(selected.end) }}
          </template>
          <template v-else>
            {{ formatDate(selected.start) }}
          </template>
        </template>
        <template v-else>
          {{ t('dashboard.date_picker_placeholder') }}
        </template>
      </span>

      <template #trailing>
        <UIcon name="i-lucide-chevron-down" class="shrink-0 text-dimmed size-5 group-data-[state=open]:rotate-180 transition-transform duration-200" />
      </template>
    </UButton>

    <template #content>
      <div class="flex items-stretch sm:divide-x divide-default">
        <div class="hidden sm:flex flex-col justify-center">
          <UButton
            v-for="(range, index) in rangeOptions"
            :key="index"
            :label="range.label"
            color="neutral"
            variant="ghost"
            class="rounded-none px-4"
            :class="[isRangeSelected(range) ? 'bg-elevated' : 'hover:bg-elevated/50']"
            truncate
            @click="selectRange(range)"
          />
        </div>

        <UCalendar
          v-model="calendarRange"
          class="p-2"
          :number-of-months="2"
          range
        />
      </div>
    </template>
  </UPopover>
</template>
