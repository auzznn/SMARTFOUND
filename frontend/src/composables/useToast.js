import { ref, readonly } from "vue"

// Module-level singleton so toasts are shared across the app
const toasts = ref([])
let _nextId = 1

export function useToast() {
  function addToast(message, type = "info", duration = 3500) {
    const id = _nextId++
    toasts.value.push({ id, message, type, visible: true })
    setTimeout(() => removeToast(id), duration)
    return id
  }

  function removeToast(id) {
    const idx = toasts.value.findIndex(t => t.id === id)
    if (idx !== -1) toasts.value.splice(idx, 1)
  }

  function success(message, duration) { return addToast(message, "success", duration) }
  function error(message, duration)   { return addToast(message, "error",   duration) }
  function info(message, duration)    { return addToast(message, "info",    duration) }
  function warn(message, duration)    { return addToast(message, "warning", duration) }

  return {
    toasts: readonly(toasts),
    addToast,
    removeToast,
    success,
    error,
    info,
    warn
  }
}
