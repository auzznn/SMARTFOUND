<template>
  <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
    <div class="flex flex-wrap gap-3 items-end">

      <!-- Type filter -->
      <div class="form-group flex-1 min-w-[140px]">
        <label for="filterType">Report Type</label>
        <select
          id="filterType"
          v-model="localFilters.reporttype"
          class="w-full"
        >
          <option value="">All Types</option>
          <option value="lost">Lost</option>
          <option value="found">Found</option>
        </select>
      </div>

      <!-- Category filter -->
      <div class="form-group flex-1 min-w-[160px]">
        <label for="filterCategory">Category</label>
        <select id="filterCategory" v-model="localFilters.categoryid" class="w-full">
          <option value="">All Categories</option>
          <option
            v-for="cat in categories"
            :key="cat.categoryid"
            :value="cat.categoryid"
          >
            {{ cat.category_name }}
          </option>
        </select>
      </div>

      <!-- Location filter -->
      <div class="form-group flex-1 min-w-[160px]">
        <label for="filterLocation">Location</label>
        <select id="filterLocation" v-model="localFilters.locationid" class="w-full">
          <option value="">All Locations</option>
          <option
            v-for="loc in locations"
            :key="loc.locationid"
            :value="loc.locationid"
          >
            {{ loc.location_name }}
          </option>
        </select>
      </div>

      <!-- Reset -->
      <button @click="resetFilters" class="btn btn-secondary self-end">
        Reset
      </button>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch, onMounted } from 'vue'
import $ from 'jquery'

const props = defineProps({
  categories: { type: Array, default: () => [] },
  locations:  { type: Array, default: () => [] },
  modelValue: {
    type: Object,
    default: () => ({ reporttype: '', categoryid: '', locationid: '' })
  }
})

const emit = defineEmits(['update:modelValue', 'filter-change'])

const localFilters = reactive({ ...props.modelValue })

watch(localFilters, (val) => {
  emit('update:modelValue', { ...val })
  emit('filter-change', { ...val })
})

watch(() => props.modelValue, (newVal) => {
  Object.assign(localFilters, newVal)
}, { deep: true })

function resetFilters() {
  localFilters.reporttype = ''
  localFilters.categoryid = ''
  localFilters.locationid = ''
}

// jQuery .change() handlers (course requirement)
onMounted(() => {
  $('#filterType').on('change', function () {
    localFilters.reporttype = $(this).val()
  })

  $('#filterCategory').on('change', function () {
    localFilters.categoryid = $(this).val()
  })

  $('#filterLocation').on('change', function () {
    localFilters.locationid = $(this).val()
  })
})
</script>
