<template>
  <div class="page-container">
    <!-- Header -->
    <div class="page-header">
      <h1 class="text-3xl font-bold text-white mb-1">All Reports</h1>
      <p class="text-gray-500">Browse lost and found items reported at UTM</p>
    </div>

    <!-- Filter bar -->
    <div class="mb-6">
      <ReportFilter
        v-model="store.filters"
        :categories="store.categories"
        :locations="store.locations"
        @filter-change="onFilterChange"
      />
    </div>

    <!-- Error state -->
    <div v-if="store.error && !store.loading" class="card p-8 text-center">
      <p class="text-red-400 mb-4">{{ store.error }}</p>
      <button @click="load" class="btn btn-primary">Retry</button>
    </div>

    <!-- Loading skeleton -->
    <div v-else-if="store.loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
      <div v-for="i in 12" :key="i" class="skeleton h-64 rounded-xl" />
    </div>

    <!-- Empty state -->
    <div v-else-if="!store.reports.length" class="empty-state mt-16">
      <div class="empty-state-icon">🔍</div>
      <p class="empty-state-title">No reports found</p>
      <p class="empty-state-desc">Try adjusting your filters or be the first to report an item.</p>
      <RouterLink to="/reports/make" class="btn btn-primary mt-4 no-underline">Make a Report</RouterLink>
    </div>

    <!-- Grid -->
    <div v-else>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <ReportCard
          v-for="report in store.reports"
          :key="report.reportid"
          :report="report"
        />
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-between mt-8">
        <p class="text-sm text-gray-500">
          Showing {{ (store.page - 1) * store.limit + 1 }}–{{ Math.min(store.page * store.limit, store.total) }}
          of {{ store.total }} reports
        </p>
        <div class="flex gap-2">
          <button
            :disabled="store.page <= 1 || store.loading"
            @click="changePage(store.page - 1)"
            class="btn btn-secondary btn-sm"
          >
            &larr; Previous
          </button>
          <span class="btn btn-secondary btn-sm cursor-default">{{ store.page }} / {{ totalPages }}</span>
          <button
            :disabled="store.page >= totalPages || store.loading"
            @click="changePage(store.page + 1)"
            class="btn btn-secondary btn-sm"
          >
            Next &rarr;
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue'
import { useReportsStore } from '@/stores/reports'
import ReportCard   from '@/components/reports/ReportCard.vue'
import ReportFilter from '@/components/reports/ReportFilter.vue'

const store = useReportsStore()

const totalPages = computed(() => Math.max(1, Math.ceil(store.total / store.limit)))

function load() {
  store.fetchReports()
}

function onFilterChange() {
  store.page = 1
  load()
}

function changePage(p) {
  store.setPage(p)
  load()
}

onMounted(async () => {
  await Promise.all([
    store.fetchCategories(),
    store.fetchLocations()
  ])
  load()
})
</script>
