<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-[9999] flex flex-col gap-2 pointer-events-none" style="max-width: 380px;">
      <TransitionGroup name="toast" tag="div" class="flex flex-col gap-2">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-xl shadow-2xl border backdrop-blur-sm"
          :class="toastClass(toast.type)"
          role="alert"
        >
          <span class="flex-shrink-0 text-lg leading-none mt-0.5">{{ icon(toast.type) }}</span>
          <p class="flex-1 text-sm font-medium leading-snug">{{ toast.message }}</p>
          <button @click="removeToast(toast.id)" class="flex-shrink-0 opacity-60 hover:opacity-100 transition-opacity text-lg" aria-label="Close">&times;</button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { useToast } from '@/composables/useToast'
const { toasts, removeToast } = useToast()
function toastClass(type) {
  const map = {
    success: "bg-green-900/90 border-green-700 text-green-100",
    error:   "bg-red-900/90   border-red-700   text-red-100",
    warning: "bg-yellow-900/90 border-yellow-700 text-yellow-100",
    info:    "bg-indigo-900/90 border-indigo-700 text-indigo-100"
  }
  return map[type] || map.info
}
function icon(type) {
  const map = { success: "OK", error: "X", warning: "!", info: "i" }
  return map[type] || map.info
}
</script>

<style scoped>
.toast-enter-active { transition: all 0.3s ease-out; }
.toast-leave-active { transition: all 0.2s ease-in; }
.toast-enter-from   { transform: translateX(110%); opacity: 0; }
.toast-leave-to     { transform: translateX(110%); opacity: 0; }
.toast-move         { transition: transform 0.2s ease; }
</style>