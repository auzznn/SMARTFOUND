<template>
  <div class="page-container">
    <div class="page-header flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-white mb-1">Manage Reports</h1>
        <p class="text-gray-500">View and moderate all reports</p>
      </div>
      <span class="badge badge-admin">Admin</span>
    </div>

    <!-- Search + Filter row -->
    <div class="flex flex-wrap gap-3 mb-6">
      <input
        v-model.trim="search"
        type="text"
        placeholder="Search by item name..."
        class="flex-1 min-w-[200px]"
        @input="debouncedSearch"
      />
      <select v-model="statusFilter" class="w-36" @change="load">
        <option value="">All Status</option>
        <option value="open">Open</option>
        <option value="closed">Closed</option>
      </select>
      <select v-model="typeFilter" class="w-36" @change="load">
        <option value="">All Types</option>
        <option value="lost">Lost</option>
        <option value="found">Found</option>
      </select>
    </div>

    <!-- Error -->
    <div v-if="error && !loading" class="card p-6 text-center">
      <p class="text-red-400 mb-3">{{ error }}</p>
      <button @click="load" class="btn btn-secondary btn-sm">Retry</button>
    </div>

    <!-- Table -->
    <AdminTable
      v-else
      :columns="columns"
      :rows="reports"
      :loading="loading"
      empty-message="No reports found."
    >
      <!-- Type -->
      <template #cell-reporttype="{ value }">
        <span :class="value === 'lost' ? 'badge badge-lost' : 'badge badge-found'">{{ value }}</span>
      </template>

      <!-- Status -->
      <template #cell-status="{ value }">
        <span :class="value === 'open' ? 'badge badge-open' : 'badge badge-closed'">{{ value }}</span>
      </template>

      <!-- Date -->
      <template #cell-date="{ value }">
        {{ formatDate(value) }}
      </template>

      <!-- Actions -->
      <template #actions="{ row }">
        <div class="flex gap-2 justify-end">
          <RouterLink :to="`/reports/${row.reportid}`" class="btn btn-secondary btn-sm no-underline">View</RouterLink>
          <button @click="confirmDelete(row)" class="btn btn-danger btn-sm">Delete</button>
        </div>
      </template>
    </AdminTable>

    <!-- Pagination -->
    <div v-if="total > limit" class="flex items-center justify-between mt-6">
      <p class="text-sm text-gray-500">{{ total }} total reports</p>
      <div class="flex gap-2">
        <button :disabled="page <= 1 || loading" @click="changePage(page - 1)" class="btn btn-secondary btn-sm">Prev</button>
        <span class="btn btn-secondary btn-sm cursor-default">{{ page }} / {{ totalPages }}</span>
        <button :disabled="page >= totalPages || loading" @click="changePage(page + 1)" class="btn btn-secondary btn-sm">Next</button>
      </div>
    </div>

    <!-- Confirm delete modal -->
    <AppModal
      v-model="showModal"
      title="Delete Report"
      :message="`Delete the report for '${selectedReport?.item?.itemname || selectedReport?.itemname || 'this item'}'? This cannot be undone.`"
      confirm-label="Delete"
      variant="danger"
      :loading="deleting"
      @confirm="doDelete"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import adminService from '@/services/adminService'
import AdminTable   from '@/components/admin/AdminTable.vue'
import AppModal     from '@/components/common/AppModal.vue'
import { useToast } from '@/composables/useToast'

const toast = useToast()

const reports        = ref([])
const total          = ref(0)
const page           = ref(1)
const limit          = ref(15)
const loading        = ref(true)
const error          = ref(null)
const search         = ref('')
const statusFilter   = ref('')
const typeFilter     = ref('')
const showModal      = ref(false)
const selectedReport = ref(null)
const deleting       = ref(false)

const totalPages = computed(() => Math.max(1, Math.ceil(total.value / limit.value)))

const columns = [
  { key: 'reportid',    label: '#',        thClass: 'w-12' },
  { key: 'item.itemname', label: 'Item' },
  { key: 'reporttype',  label: 'Type',     thClass: 'w-28' },
  { key: 'status',      label: 'Status',   thClass: 'w-28' },
  { key: 'user.username', label: 'Reporter' },
  { key: 'date',        label: 'Date',     thClass: 'w-36' }
]

let searchTimer = null
function debouncedSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { page.value = 1; load() }, 400)
}

async function load() {
  loading.value = true
  error.value   = null
  try {
    const params = { page: page.value, limit: limit.value }
    if (search.value)      params.search     = search.value
    if (statusFilter.value) params.status    = statusFilter.value
    if (typeFilter.value)   params.reporttype= typeFilter.value
    const res = await adminService.getAllReports(params)
    reports.value = res.reports || res.data || []
    total.value   = res.total  || 0
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to load reports'
  } finally {
    loading.value = false
  }
}

function changePage(p) { page.value = p; load() }

function confirmDelete(row) {
  selectedReport.value = row
  showModal.value = true
}

async function doDelete() {
  deleting.value = true
  try {
    await adminService.deleteReport(selectedReport.value.reportid)
    reports.value = reports.value.filter(r => r.reportid !== selectedReport.value.reportid)
    total.value = Math.max(0, total.value - 1)
    showModal.value = false
    toast.success('Report deleted')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Delete failed')
  } finally {
    deleting.value = false
  }
}

function formatDate(d) {
  const date = new Date(d)
  return isNaN(date) ? '—' : date.toLocaleDateString('en-MY', { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(load)
</script>
