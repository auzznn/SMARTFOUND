<template>
  <div class="card p-4 flex items-center gap-4">
    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-sm font-bold text-white uppercase">
      {{ initials(user.fullname || user.username) }}
    </div>
    <div class="flex-1 min-w-0">
      <p class="text-white font-medium truncate">{{ user.fullname || user.username }}</p>
      <p class="text-gray-400 text-sm truncate">@{{ user.username }}</p>
      <p v-if="user.contactno" class="text-gray-500 text-xs">{{ user.contactno }}</p>
    </div>
    <div class="flex-shrink-0 flex items-center gap-2">
      <span :class="roleBadgeClass">{{ user.rolename || user.role?.rolename || 'student' }}</span>
      <button
        v-if="canDelete"
        @click="$emit('delete', user)"
        class="btn btn-danger btn-sm"
        title="Delete user"
      >
        Delete
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  user:      { type: Object,  required: true },
  canDelete: { type: Boolean, default: true }
})

defineEmits(['delete'])

function initials(name) {
  if (!name) return '?'
  return name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()
}

const roleBadgeClass = computed(() => {
  const r = props.user.rolename || props.user.role?.rolename
  if (r === 'admin')   return 'badge badge-admin'
  if (r === 'officer') return 'badge badge-officer'
  return 'badge badge-student'
})
</script>
