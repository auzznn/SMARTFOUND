<template>
  <div class="page-container max-w-3xl mx-auto">
    <div class="page-header">
      <h1 class="text-3xl font-bold text-white mb-1">My Profile</h1>
      <p class="text-gray-500">Manage your account and view your reports</p>
    </div>

    <!-- Profile card -->
    <div class="card p-6 mb-8">
      <div class="flex items-start gap-4 mb-6">
        <div class="flex-shrink-0 w-16 h-16 rounded-2xl bg-indigo-600 flex items-center justify-center text-2xl font-bold text-white uppercase">
          {{ initials }}
        </div>
        <div class="flex-1">
          <h2 class="text-xl font-semibold text-white">{{ auth.user?.fullname || auth.user?.username }}</h2>
          <p class="text-gray-400 text-sm">@{{ auth.user?.username }}</p>
          <span :class="roleBadge" class="mt-1 inline-block">{{ auth.user?.rolename }}</span>
        </div>
        <button @click="editing = !editing" class="btn btn-secondary btn-sm">
          {{ editing ? 'Cancel' : 'Edit Profile' }}
        </button>
      </div>

      <!-- View mode -->
      <dl v-if="!editing" class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div>
          <dt class="text-gray-500 text-xs uppercase tracking-wide mb-0.5">Full Name</dt>
          <dd class="text-gray-200">{{ auth.user?.fullname || '—' }}</dd>
        </div>
        <div>
          <dt class="text-gray-500 text-xs uppercase tracking-wide mb-0.5">Username</dt>
          <dd class="text-gray-200">{{ auth.user?.username }}</dd>
        </div>
        <div>
          <dt class="text-gray-500 text-xs uppercase tracking-wide mb-0.5">Contact</dt>
          <dd class="text-gray-200 font-mono">{{ auth.user?.contactno || '—' }}</dd>
        </div>
        <div>
          <dt class="text-gray-500 text-xs uppercase tracking-wide mb-0.5">Role</dt>
          <dd class="text-gray-200 capitalize">{{ auth.user?.rolename }}</dd>
        </div>
      </dl>

      <!-- Edit form -->
      <form v-else @submit.prevent="saveProfile" class="space-y-4" novalidate>
        <div class="form-group">
          <label for="p-fullname">Full Name</label>
          <input id="p-fullname" v-model.trim="editForm.fullname" type="text"
            :class="{ 'input-error': editErrors.fullname }"
            @blur="touchEdit('fullname')" />
          <span v-if="editErrors.fullname" class="form-error">{{ editErrors.fullname }}</span>
        </div>
        <div class="form-group">
          <label for="p-contact">Contact Number</label>
          <input id="p-contact" v-model.trim="editForm.contactno" type="tel"
            :class="{ 'input-error': editErrors.contactno }"
            @blur="touchEdit('contactno')" />
          <span v-if="editErrors.contactno" class="form-error">{{ editErrors.contactno }}</span>
        </div>
        <div class="flex gap-3">
          <button type="submit" class="btn btn-primary" :disabled="saving">
            <span v-if="saving" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
            Save Changes
          </button>
          <button type="button" @click="editing = false" class="btn btn-secondary">Cancel</button>
        </div>
      </form>
    </div>

    <!-- My Reports section -->
    <div>
      <h2 class="text-xl font-semibold text-white mb-4">My Reports</h2>

      <div v-if="myLoading" class="space-y-3">
        <div v-for="i in 3" :key="i" class="skeleton h-16 rounded-xl" />
      </div>

      <div v-else-if="myError" class="card p-6 text-center">
        <p class="text-red-400 mb-3">{{ myError }}</p>
        <button @click="loadMine" class="btn btn-secondary btn-sm">Retry</button>
      </div>

      <div v-else-if="!myReports.length" class="empty-state py-10">
        <div class="empty-state-icon">📋</div>
        <p class="empty-state-title">No reports yet</p>
        <p class="empty-state-desc">You haven't submitted any reports.</p>
        <RouterLink to="/reports/make" class="btn btn-primary mt-4 no-underline">Make a Report</RouterLink>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="r in myReports"
          :key="r.reportid"
          class="card p-4 flex items-center gap-3 hover:border-indigo-700 cursor-pointer transition-colors"
          @click="router.push(`/reports/${r.reportid}`)"
        >
          <span :class="r.reporttype === 'lost' ? 'badge badge-lost' : 'badge badge-found'">
            {{ r.reporttype }}
          </span>
          <div class="flex-1 min-w-0">
            <p class="text-white text-sm font-medium truncate">{{ r.item?.itemname || r.itemname || 'Item' }}</p>
            <p class="text-gray-500 text-xs">{{ formatDate(r.date) }}</p>
          </div>
          <span :class="r.status === 'open' ? 'badge badge-open' : 'badge badge-closed'">
            {{ r.status }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast }     from '@/composables/useToast'
import reportService    from '@/services/reportService'
import { validate, required, minLength, validPhone } from '@/utils/validators'

const auth   = useAuthStore()
const toast  = useToast()
const router = useRouter()

const editing  = ref(false)
const saving   = ref(false)
const myLoading= ref(true)
const myError  = ref(null)
const myReports= ref([])

const editForm = reactive({
  fullname:  auth.user?.fullname  || '',
  contactno: auth.user?.contactno || ''
})
const editErrors = reactive({ fullname: '', contactno: '' })

const editRules = {
  fullname:  [required, minLength(2)],
  contactno: [required, validPhone]
}

function touchEdit(f) {
  editErrors[f] = validate(editForm[f], editRules[f]) || ''
}

const initials = computed(() => {
  const name = auth.user?.fullname || auth.user?.username || ''
  return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()
})

const roleBadge = computed(() => {
  const r = auth.user?.rolename
  if (r === 'admin')   return 'badge badge-admin'
  if (r === 'officer') return 'badge badge-officer'
  return 'badge badge-student'
})

async function saveProfile() {
  let valid = true
  for (const [f, rules] of Object.entries(editRules)) {
    editErrors[f] = validate(editForm[f], rules) || ''
    if (editErrors[f]) valid = false
  }
  if (!valid) return

  saving.value = true
  try {
    await auth.updateProfile(editForm)
    editing.value = false
    toast.success('Profile updated')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to update profile')
  } finally {
    saving.value = false
  }
}

async function loadMine() {
  myLoading.value = true
  myError.value   = null
  try {
    const res = await reportService.getMyReports()
    myReports.value = res.reports || res.data || []
  } catch (e) {
    myError.value = e.response?.data?.message || 'Failed to load your reports'
  } finally {
    myLoading.value = false
  }
}

function formatDate(d) {
  const date = new Date(d)
  return isNaN(date) ? '' : date.toLocaleDateString('en-MY', { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(loadMine)
</script>
