import { useReportsStore } from "@/stores/reports"
import { useToast } from "./useToast"

export function useReports() {
  const store = useReportsStore()
  const toast = useToast()

  async function loadReports(extra = {}) {
    await store.fetchReports(extra)
    if (store.error) toast.error(store.error)
  }

  async function loadClosed(extra = {}) {
    await store.fetchClosedReports(extra)
    if (store.error) toast.error(store.error)
  }

  async function loadMine() {
    await store.fetchMyReports()
    if (store.error) toast.error(store.error)
  }

  async function close(id) {
    try {
      await store.closeReport(id)
      toast.success("Report closed successfully")
      return true
    } catch (e) {
      toast.error(e.response?.data?.message || "Failed to close report")
      return false
    }
  }

  async function remove(id) {
    try {
      await store.deleteReport(id)
      toast.success("Report deleted")
      return true
    } catch (e) {
      toast.error(e.response?.data?.message || "Failed to delete report")
      return false
    }
  }

  return { store, loadReports, loadClosed, loadMine, close, remove }
}
