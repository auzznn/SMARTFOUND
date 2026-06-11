<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="text-3xl font-bold text-white mb-1">Closed Reports</h1>
      <p class="text-gray-500">Reports that have been resolved and closed</p>
    </div>
    <div v-if="store.error && !store.loading" class="card p-8 text-center">
      <p class="text-red-400 mb-4">{{ store.error }}</p>
      <button @click="load" class="btn btn-primary">Retry</button>
    </div>
    <div v-else-if="store.loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
      <div v-for="i in 12" :key="i" class="skeleton h-64 rounded-xl" />
    </div>
    <div v-else-if="!store.reports.length" class="empty-state mt-16">
      <div class="empty-state-icon">📭</div>
      <p class="empty-state-title">No closed reports</p>
      <p class="empty-state-desc">All reports are still open.</p>
    </div>
    <div v-else>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 opacity-80">
        <ReportCard v-for="report in store.reports" :key="report.reportid" :report="report" />
      </div>
      <div class="flex items-center justify-between mt-8">
        <p class="text-sm text-gray-500">{{ (store.page-1)*store.limit+1 }}-{{ Math.min(store.page*store.limit, store.total) }} of {{ store.total }}</p>
        <div class="flex gap-2">
          <button :disabled="store.page <= 1 || store.loading" @click="changePage(store.page - 1)" class="btn btn-secondary btn-sm">Prev</button>
          <span class="btn btn-secondary btn-sm cursor-default">{{ store.page }} / {{ totalPages }}</span>
          <button :disabled="store.page >= totalPages || store.loading" @click="changePage(store.page + 1)" class="btn btn-secondary btn-sm">Next</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from "vue"
import { useReportsStore } from "@/stores/reports"
import ReportCard from "@/components/reports/ReportCard.vue"
const store = useReportsStore()
const totalPages = computed(() => Math.max(1, Math.ceil(store.total / store.limit)))
function load() { store.fetchClosedReports() }
function changePage(p) { store.setPage(p); load() }
onMounted(() => { store.setPage(1); load() })
</script>