<template>
  <div class="page-container max-w-4xl mx-auto">

    <!-- Loading -->
    <div v-if="loading" class="space-y-6">
      <div class="skeleton h-8 w-48 rounded" />
      <div class="card p-6">
        <div class="flex gap-6">
          <div class="skeleton w-64 aspect-square rounded-xl" />
          <div class="flex-1 space-y-4">
            <div class="skeleton h-8 rounded" />
            <div class="skeleton h-4 w-3/4 rounded" />
            <div class="skeleton h-4 w-1/2 rounded" />
          </div>
        </div>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="card p-10 text-center">
      <p class="text-red-400 text-lg mb-4">{{ error }}</p>
      <div class="flex gap-3 justify-center">
        <button @click="loadReport" class="btn btn-primary">Retry</button>
        <RouterLink to="/reports" class="btn btn-secondary no-underline">Back to Reports</RouterLink>
      </div>
    </div>

    <!-- Content -->
    <div v-else-if="report">
      <!-- Breadcrumb -->
      <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <RouterLink to="/reports" class="hover:text-gray-300 no-underline">Reports</RouterLink>
        <span>/</span>
        <span class="text-gray-300 truncate">{{ itemName }}</span>
      </div>

      <!-- Report detail card -->
      <ReportDetail :report="report">
        <template #actions>
          <!-- Close Report (owner only, if open) -->
          <button
            v-if="canClose"
            @click="showCloseModal = true"
            class="btn btn-success"
          >
            Mark as Closed
          </button>

          <!-- Delete (admin / officer) -->
          <button
            v-if="canDelete"
            @click="showDeleteModal = true"
            class="btn btn-danger"
          >
            Delete Report
          </button>
        </template>
      </ReportDetail>

      <!-- Comments section -->
      <div class="card p-6 mt-6">
        <h2 class="text-xl font-semibold text-white mb-5">
          Comments
          <span class="text-gray-500 text-base font-normal ml-2">({{ comments.length }})</span>
        </h2>

        <CommentList :comments="comments" :loading="commentsLoading" />

        <div class="divider" />

        <CommentForm
          :is-closed="report.status === 'closed'"
          :submitting="commentSubmitting"
          @submit="handleComment"
        />
      </div>
    </div>

    <!-- Confirm close -->
    <AppModal
      v-model="showCloseModal"
      title="Close Report"
      message="Mark this report as closed? Comments will be disabled and the item will be archived."
      confirm-label="Close Report"
      variant="warning"
      :loading="actionLoading"
      @confirm="doCloseReport"
    />

    <!-- Confirm delete -->
    <AppModal
      v-model="showDeleteModal"
      title="Delete Report"
      message="This will permanently delete the report and all its comments. This action cannot be undone."
      confirm-label="Delete"
      variant="danger"
      :loading="actionLoading"
      @confirm="doDeleteReport"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore }    from '@/stores/auth'
import { useToast }        from '@/composables/useToast'
import reportService   from '@/services/reportService'
import commentService  from '@/services/commentService'
import ReportDetail    from '@/components/reports/ReportDetail.vue'
import CommentList     from '@/components/comments/CommentList.vue'
import CommentForm     from '@/components/comments/CommentForm.vue'
import AppModal        from '@/components/common/AppModal.vue'

const route  = useRoute()
const router = useRouter()
const auth   = useAuthStore()
const toast  = useToast()

const report           = ref(null)
const comments         = ref([])
const loading          = ref(true)
const error            = ref(null)
const commentsLoading  = ref(false)
const commentSubmitting= ref(false)
const actionLoading    = ref(false)
const showCloseModal   = ref(false)
const showDeleteModal  = ref(false)

const itemName = computed(() => report.value?.item?.itemname || report.value?.itemname || 'Report')

const canClose = computed(() => {
  if (!report.value || report.value.status !== 'open') return false
  if (!auth.isAuthenticated) return false
  return Number(report.value.uuid) === Number(auth.user?.uuid)
})

const canDelete = computed(() => {
  if (!report.value || !auth.isAuthenticated) return false
  return auth.isAdmin || auth.isOfficer
})

async function loadReport() {
  loading.value = true
  error.value   = null
  try {
    const res   = await reportService.getReport(route.params.id)
    report.value = res.report || res
  } catch (e) {
    error.value = e.response?.status === 404
      ? 'Report not found.'
      : (e.response?.data?.message || 'Failed to load report.')
  } finally {
    loading.value = false
  }
}

async function loadComments() {
  commentsLoading.value = true
  try {
    const res = await commentService.getComments(route.params.id)
    comments.value = res.comments || res.data || []
  } catch {}
  finally { commentsLoading.value = false }
}

async function handleComment(text) {
  commentSubmitting.value = true
  try {
    const res = await commentService.createComment(route.params.id, text)
    const newComment = res.comment || res
    comments.value.unshift(newComment)
    toast.success('Comment posted')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to post comment')
  } finally {
    commentSubmitting.value = false
  }
}

async function doCloseReport() {
  actionLoading.value = true
  try {
    await reportService.closeReport(route.params.id)
    report.value.status = 'closed'
    showCloseModal.value = false
    toast.success('Report closed successfully')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to close report')
  } finally {
    actionLoading.value = false
  }
}

async function doDeleteReport() {
  actionLoading.value = true
  try {
    await reportService.deleteReport(route.params.id)
    showDeleteModal.value = false
    toast.success('Report deleted')
    router.push('/reports')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to delete report')
  } finally {
    actionLoading.value = false
  }
}

onMounted(async () => {
  await loadReport()
  if (report.value) await loadComments()
})
</script>
