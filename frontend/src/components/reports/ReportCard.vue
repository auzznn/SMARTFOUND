<template>
  <div
    class="card-hover group"
    @click="goToDetail"
    :class="{ 'opacity-60': isClosed }"
  >
    <!-- Image -->
    <div class="relative aspect-video overflow-hidden bg-gray-700">
      <img
        v-if="report.png || report.item?.png"
        :src="imageSrc"
        :alt="itemName"
        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
        @error="imgError = true"
      />
      <div v-else class="w-full h-full flex items-center justify-center text-5xl text-gray-600">
        {{ categoryEmoji }}
      </div>

      <!-- Type badge overlay -->
      <div class="absolute top-2 left-2">
        <span :class="typeBadgeClass">{{ report.reporttype }}</span>
      </div>

      <!-- Closed overlay -->
      <div v-if="isClosed" class="absolute inset-0 bg-gray-900/50 flex items-center justify-center">
        <span class="badge badge-closed text-sm px-3 py-1">CLOSED</span>
      </div>
    </div>

    <!-- Content -->
    <div class="p-4">
      <h3 class="font-semibold text-white text-base leading-snug mb-1 line-clamp-1">
        {{ itemName }}
      </h3>

      <div class="flex flex-wrap gap-1.5 mb-3">
        <span class="text-xs text-gray-400 bg-gray-700/50 px-2 py-0.5 rounded-full">
          {{ categoryName }}
        </span>
        <span class="text-xs text-gray-400 bg-gray-700/50 px-2 py-0.5 rounded-full">
          {{ locationName }}
        </span>
      </div>

      <div class="flex items-center justify-between text-xs text-gray-500">
        <span>{{ formattedDate }}</span>
        <span :class="statusBadgeClass">{{ report.status }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  report: { type: Object, required: true }
})

const router   = useRouter()
const imgError = ref(false)

const isClosed    = computed(() => props.report.status === 'closed')
const itemName    = computed(() => props.report.item?.itemname || props.report.itemname || 'Unknown Item')
const categoryName= computed(() => props.report.category?.category_name || props.report.category_name || 'N/A')
const locationName= computed(() => props.report.location?.location_name || props.report.location_name || 'N/A')
const imageSrc    = computed(() => {
  if (imgError.value) return null
  return props.report.png || props.report.item?.png
})

const categoryEmoji = computed(() => {
  const name = categoryName.value.toLowerCase()
  if (name.includes('electronics') || name.includes('phone')) return '📱'
  if (name.includes('bag') || name.includes('backpack'))       return '🎒'
  if (name.includes('key'))                                    return '🔑'
  if (name.includes('wallet') || name.includes('card'))        return '👛'
  if (name.includes('book') || name.includes('document'))      return '📚'
  if (name.includes('cloth') || name.includes('wear'))         return '👕'
  return '📦'
})

const typeBadgeClass = computed(() =>
  props.report.reporttype === 'lost' ? 'badge badge-lost' : 'badge badge-found'
)

const statusBadgeClass = computed(() =>
  props.report.status === 'open' ? 'badge badge-open' : 'badge badge-closed'
)

const formattedDate = computed(() => {
  const d = new Date(props.report.date || props.report.created_at)
  return isNaN(d) ? '—' : d.toLocaleDateString('en-MY', { day: 'numeric', month: 'short', year: 'numeric' })
})

function goToDetail() {
  router.push(`/reports/${props.report.reportid}`)
}
</script>
