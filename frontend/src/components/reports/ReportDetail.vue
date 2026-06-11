<template>
  <div class="space-y-6">
    <!-- Header card -->
    <div class="card p-6">
      <div class="flex flex-col md:flex-row gap-6">

        <!-- Image -->
        <div class="flex-shrink-0 w-full md:w-64">
          <div class="aspect-square rounded-xl overflow-hidden bg-gray-700 flex items-center justify-center">
            <img
              v-if="imageSrc"
              :src="imageSrc"
              :alt="itemName"
              class="w-full h-full object-cover"
              @error="imgError = true"
            />
            <span v-else class="text-7xl opacity-40">{{ categoryEmoji }}</span>
          </div>
        </div>

        <!-- Info -->
        <div class="flex-1 space-y-4">
          <div class="flex flex-wrap gap-2 items-start">
            <h1 class="text-2xl font-bold text-white flex-1">{{ itemName }}</h1>
            <div class="flex gap-2 flex-shrink-0">
              <span :class="typeBadgeClass">{{ report.reporttype }}</span>
              <span :class="statusBadgeClass">{{ report.status }}</span>
            </div>
          </div>

          <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
            <div>
              <dt class="text-gray-500 text-xs uppercase tracking-wide mb-0.5">Category</dt>
              <dd class="text-gray-200">{{ categoryName }}</dd>
            </div>
            <div>
              <dt class="text-gray-500 text-xs uppercase tracking-wide mb-0.5">Location</dt>
              <dd class="text-gray-200">{{ locationName }}</dd>
            </div>
            <div>
              <dt class="text-gray-500 text-xs uppercase tracking-wide mb-0.5">Date</dt>
              <dd class="text-gray-200">{{ formattedDate }}</dd>
            </div>
            <div>
              <dt class="text-gray-500 text-xs uppercase tracking-wide mb-0.5">Total Items</dt>
              <dd class="text-gray-200">{{ report.item?.totalitems || '—' }}</dd>
            </div>
            <div v-if="report.contactno || report.user?.contactno">
              <dt class="text-gray-500 text-xs uppercase tracking-wide mb-0.5">Contact</dt>
              <dd class="text-gray-200 font-mono">{{ report.contactno || report.user?.contactno }}</dd>
            </div>
            <div v-if="report.user?.username">
              <dt class="text-gray-500 text-xs uppercase tracking-wide mb-0.5">Reported by</dt>
              <dd class="text-gray-200">{{ report.user.username }}</dd>
            </div>
          </dl>

          <!-- Actions -->
          <div class="flex flex-wrap gap-3 pt-2">
            <slot name="actions" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  report: { type: Object, required: true }
})

const imgError = ref(false)

const itemName     = computed(() => props.report.item?.itemname || props.report.itemname || 'Unknown Item')
const categoryName = computed(() => props.report.category?.category_name || props.report.category_name || 'N/A')
const locationName = computed(() => props.report.location?.location_name  || props.report.location_name  || 'N/A')
const imageSrc     = computed(() => {
  if (imgError.value) return null
  return props.report.png || props.report.item?.png
})

const categoryEmoji = computed(() => {
  const name = categoryName.value.toLowerCase()
  if (name.includes('electronics') || name.includes('phone')) return '📱'
  if (name.includes('bag'))   return '🎒'
  if (name.includes('key'))   return '🔑'
  if (name.includes('wallet'))return '👛'
  if (name.includes('book'))  return '📚'
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
  return isNaN(d) ? '—' : d.toLocaleDateString('en-MY', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
})
</script>
