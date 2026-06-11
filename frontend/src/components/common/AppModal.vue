<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="$emit('update:modelValue', false)"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" />

        <!-- Dialog -->
        <div class="relative bg-gray-800 rounded-2xl border border-gray-700 shadow-2xl w-full max-w-md p-6 animate-fade-in">

          <!-- Header -->
          <div class="flex items-start gap-3 mb-4">
            <div
              class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-lg"
              :class="iconBg"
            >
              {{ iconChar }}
            </div>
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-white">{{ title }}</h3>
              <p class="text-gray-400 text-sm mt-1">{{ message }}</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-3 justify-end mt-6">
            <button
              @click="$emit('update:modelValue', false)"
              class="btn btn-secondary"
              :disabled="loading"
            >
              {{ cancelLabel }}
            </button>
            <button
              @click="onConfirm"
              class="btn"
              :class="confirmClass"
              :disabled="loading"
            >
              <span v-if="loading" class="inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin" />
              {{ confirmLabel }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue:   { type: Boolean, default: false },
  title:        { type: String,  default: 'Confirm Action' },
  message:      { type: String,  default: 'Are you sure you want to proceed?' },
  confirmLabel: { type: String,  default: 'Confirm' },
  cancelLabel:  { type: String,  default: 'Cancel' },
  variant:      { type: String,  default: 'danger' }, // danger | warning | info
  loading:      { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue', 'confirm'])

const iconBg = computed(() => {
  const map = {
    danger:  'bg-red-900/50 text-red-400',
    warning: 'bg-yellow-900/50 text-yellow-400',
    info:    'bg-indigo-900/50 text-indigo-400'
  }
  return map[props.variant] || map.danger
})

const iconChar = computed(() => {
  const map = { danger: '!', warning: '?', info: 'i' }
  return map[props.variant] || '!'
})

const confirmClass = computed(() => {
  const map = {
    danger:  'btn-danger',
    warning: 'btn-secondary',
    info:    'btn-primary'
  }
  return map[props.variant] || 'btn-danger'
})

function onConfirm() {
  emit('confirm')
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from,
.modal-leave-to     { opacity: 0; }
</style>
