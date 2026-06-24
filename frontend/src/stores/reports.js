import { defineStore } from "pinia"
import { ref, reactive } from "vue"
import reportService from "@/services/reportService"

export const useReportsStore = defineStore("reports", () => {
  const reports = ref([])
  const total   = ref(0)
  const page    = ref(1)
  const limit   = ref(12)
  const loading = ref(false)
  const error   = ref(null)

  const filters = reactive({
    reporttype: "",
    categoryid: "",
    locationid: ""
  })

  const categories = ref([])
  const locations  = ref([])

  async function fetchReports(extraParams = {}) {
    loading.value = true
    error.value = null
    try {
      const params = {
        page: page.value,
        limit: limit.value,
        status: "open",
        ...filters,
        ...extraParams
      }
      // remove empty strings
      Object.keys(params).forEach(k => { if (params[k] === "") delete params[k] })
      const res = await reportService.getReports(params)
      reports.value = res.reports || []
      total.value   = res.pagination?.total || res.total || 0
    } catch (e) {
      error.value = e.response?.data?.message || "Failed to load reports"
    } finally {
      loading.value = false
    }
  }

  async function fetchClosedReports(extraParams = {}) {
    loading.value = true
    error.value = null
    try {
      const params = {
        page: page.value, limit: limit.value,
        status: "closed", ...extraParams
      }
      const res = await reportService.getClosedReports(params)
      reports.value = res.reports || []
      total.value   = res.pagination?.total || res.total || 0
    } catch (e) {
      error.value = e.response?.data?.message || "Failed to load closed reports"
    } finally {
      loading.value = false
    }
  }

  async function fetchMyReports() {
    loading.value = true
    error.value = null
    try {
      const res = await reportService.getMyReports({ page: page.value, limit: limit.value })
      reports.value = res.reports || []
      total.value   = res.pagination?.total || res.total || reports.value.length
    } catch (e) {
      error.value = e.response?.data?.message || "Failed to load your reports"
    } finally {
      loading.value = false
    }
  }

  async function createReport(payload) {
    const res = await reportService.createReport(payload)
    return res
  }

  async function closeReport(id) {
    const res = await reportService.closeReport(id)
    const idx = reports.value.findIndex(r => Number(r.reportid) === Number(id))
    if (idx !== -1) reports.value[idx].status = "closed"
    return res
  }

  async function deleteReport(id) {
    const res = await reportService.deleteReport(id)
    reports.value = reports.value.filter(r => Number(r.reportid) !== Number(id))
    total.value = Math.max(0, total.value - 1)
    return res
  }

  async function fetchCategories() {
    if (categories.value.length) return
    try {
      const res = await reportService.getCategories()
      categories.value = res.categories || []
    } catch {}
  }

  async function fetchLocations() {
    if (locations.value.length) return
    try {
      const res = await reportService.getLocations()
      locations.value = res.locations || []
    } catch {}
  }

  function setFilter(key, value) {
    filters[key] = value
    page.value = 1
  }

  function setPage(n) { page.value = n }

  function resetFilters() {
    filters.reporttype = ""
    filters.categoryid = ""
    filters.locationid = ""
    page.value = 1
  }

  return {
    reports, total, page, limit, loading, error, filters,
    categories, locations,
    fetchReports, fetchClosedReports, fetchMyReports,
    createReport, closeReport, deleteReport,
    fetchCategories, fetchLocations,
    setFilter, setPage, resetFilters
  }
})
