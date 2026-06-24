<template>
  <div class="page-container">
    <div class="page-header flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-white mb-1">Manage Users</h1>
        <p class="text-gray-500">View and remove registered users</p>
      </div>
      <span class="badge badge-admin">Admin</span>
    </div>

    <!-- Search -->
    <div class="mb-6">
      <input
        v-model.trim="search"
        type="text"
        placeholder="Search by username or full name..."
        class="w-full max-w-sm"
        @input="debouncedSearch"
      />
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
      :rows="users"
      :loading="loading"
      empty-message="No users found."
    >
      <!-- Role badge -->
      <template #cell-rolename="{ row }">
        <span :class="roleBadge(row.rolename || row.role?.rolename)">
          {{ row.rolename || row.role?.rolename || 'student' }}
        </span>
      </template>

      <!-- Joined date -->
      <template #cell-created_at="{ value }">
        {{ formatDate(value) }}
      </template>

      <!-- Actions -->
      <template #actions="{ row }">
        <div class="flex justify-end">
          <button
            @click="confirmDelete(row)"
            class="btn btn-danger btn-sm"
            :disabled="Number(row.uuid) === Number(currentUser?.uuid)"
          >
            {{ Number(row.uuid) === Number(currentUser?.uuid) ? 'You' : 'Delete' }}
          </button>
        </div>
      </template>
    </AdminTable>

    <!-- Pagination -->
    <div v-if="total > limit" class="flex items-center justify-between mt-6">
      <p class="text-sm text-gray-500">{{ total }} total users</p>
      <div class="flex gap-2">
        <button :disabled="page <= 1 || loading" @click="changePage(page - 1)" class="btn btn-secondary btn-sm">Prev</button>
        <span class="btn btn-secondary btn-sm cursor-default">{{ page }} / {{ totalPages }}</span>
        <button :disabled="page >= totalPages || loading" @click="changePage(page + 1)" class="btn btn-secondary btn-sm">Next</button>
      </div>
    </div>

    <!-- Confirm delete modal -->
    <AppModal
      v-model="showModal"
      title="Delete User"
      :message="`Delete user '@${selectedUser?.username}'? This will remove all their data permanently.`"
      confirm-label="Delete User"
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
import { useAuthStore } from '@/stores/auth'

const toast       = useToast()
const auth        = useAuthStore()
const currentUser = computed(() => auth.user)

const users       = ref([])
const total       = ref(0)
const page        = ref(1)
const limit       = ref(20)
const loading     = ref(true)
const error       = ref(null)
const search      = ref('')
const showModal   = ref(false)
const selectedUser= ref(null)
const deleting    = ref(false)

const totalPages  = computed(() => Math.max(1, Math.ceil(total.value / limit.value)))

const columns = [
  { key: 'username',   label: 'Username' },
  { key: 'fullname',   label: 'Full Name' },
  { key: 'contactno',  label: 'Contact' },
  { key: 'rolename',   label: 'Role',    thClass: 'w-28' },
  { key: 'created_at', label: 'Joined',  thClass: 'w-36' }
]

function roleBadge(r) {
  if (r === 'admin')   return 'badge badge-admin'
  if (r === 'officer') return 'badge badge-officer'
  return 'badge badge-student'
}

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
    if (search.value) params.search = search.value
    const res = await adminService.getUsers(params)
    users.value = res.users || []
    total.value = res.total || 0
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to load users'
  } finally {
    loading.value = false
  }
}

function changePage(p) { page.value = p; load() }

function confirmDelete(user) {
  selectedUser.value = user
  showModal.value = true
}

async function doDelete() {
  deleting.value = true
  try {
    await adminService.deleteUser(selectedUser.value.uuid)
    users.value = users.value.filter(u => Number(u.uuid) !== Number(selectedUser.value.uuid))
    total.value = Math.max(0, total.value - 1)
    showModal.value = false
    toast.success(`User @${selectedUser.value.username} deleted`)
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
