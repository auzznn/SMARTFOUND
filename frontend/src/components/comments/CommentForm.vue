<template>
  <div>
    <div
      v-if="isClosed"
      class="bg-gray-700/30 rounded-xl border border-gray-700 p-4 text-center text-sm text-gray-500"
    >
      This report is <strong class="text-gray-400">closed</strong> — comments are disabled.
    </div>

    <form v-else @submit.prevent="submit" class="space-y-3">
      <div class="form-group">
        <label for="commentText">Add a comment</label>
        <textarea
          id="commentText"
          v-model.trim="text"
          rows="3"
          placeholder="Write your comment here..."
          class="w-full resize-none"
          :class="{ 'input-error': error }"
          maxlength="500"
        />
        <div class="flex justify-between items-center mt-1">
          <span v-if="error" class="form-error">{{ error }}</span>
          <span v-else class="text-xs text-gray-600 ml-auto">{{ text.length }}/500</span>
        </div>
      </div>

      <div class="flex justify-end">
        <button type="submit" class="btn btn-primary" :disabled="submitting || !text">
          <span v-if="submitting" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
          Post Comment
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  isClosed:   { type: Boolean, default: false },
  submitting: { type: Boolean, default: false }
})

const emit = defineEmits(['submit'])

const text  = ref('')
const error = ref('')

function submit() {
  error.value = ''
  if (!text.value.trim()) {
    error.value = 'Comment cannot be empty'
    return
  }
  if (text.value.trim().length > 500) {
    error.value = 'Comment too long (max 500 characters)'
    return
  }
  emit('submit', text.value.trim())
  text.value = ''
}
</script>
