<template>
  <div class="space-y-3">
    <!-- Loading -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 3" :key="i" class="skeleton h-16 rounded-xl" />
    </div>

    <!-- Empty -->
    <div v-else-if="!comments.length" class="empty-state py-8">
      <p class="empty-state-title text-base">No comments yet</p>
      <p class="empty-state-desc">Be the first to leave a comment.</p>
    </div>

    <!-- Comments -->
    <TransitionGroup v-else name="comment" tag="div" class="space-y-3">
      <div
        v-for="comment in comments"
        :key="comment.commentid"
        class="bg-gray-700/40 rounded-xl border border-gray-700/50 p-4"
      >
        <div class="flex items-start gap-3">
          <!-- Avatar -->
          <div class="flex-shrink-0 w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-xs font-bold text-white uppercase">
            {{ initials(comment.user?.username || comment.username) }}
          </div>

          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-semibold text-sm text-white">{{ comment.user?.username || comment.username || 'Unknown' }}</span>
              <span v-if="comment.user?.rolename && comment.user.rolename !== 'student'" class="badge badge-officer text-xs">{{ comment.user.rolename }}</span>
              <span class="text-xs text-gray-500 ml-auto">{{ timeAgo(comment.createdat) }}</span>
            </div>
            <p class="text-sm text-gray-300 mt-1 break-words">{{ comment.comment }}</p>
          </div>
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
defineProps({
  comments: { type: Array,   default: () => [] },
  loading:  { type: Boolean, default: false }
})

function initials(name) {
  if (!name) return '?'
  return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()
}

function timeAgo(dateStr) {
  if (!dateStr) return ''
  const diff = Date.now() - new Date(dateStr)
  const minutes = Math.floor(diff / 60000)
  if (minutes < 1)   return 'just now'
  if (minutes < 60)  return `${minutes}m ago`
  const hours = Math.floor(minutes / 60)
  if (hours < 24)    return `${hours}h ago`
  const days = Math.floor(hours / 24)
  if (days < 7)      return `${days}d ago`
  return new Date(dateStr).toLocaleDateString('en-MY', { day: 'numeric', month: 'short' })
}
</script>

<style scoped>
.comment-enter-active { transition: all 0.3s ease; }
.comment-leave-active { transition: all 0.2s ease; }
.comment-enter-from   { opacity: 0; transform: translateY(-8px); }
.comment-leave-to     { opacity: 0; transform: translateY(-8px); }
</style>
