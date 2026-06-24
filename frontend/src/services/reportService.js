import apiClient, { unwrapApiData } from './apiClient'

const reportService = {
  async getReports(params = {}) {
    const response = await apiClient.get('/reports', { params })
    return unwrapApiData(response)
  },
  async getReport(id) {
    const response = await apiClient.get(`/reports/${id}`)
    return unwrapApiData(response)
  },
  async createReport(formData) {
    const response = await apiClient.post('/reports', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    return unwrapApiData(response)
  },
  async closeReport(id) {
    const response = await apiClient.put(`/reports/${id}/status`, { status: 'closed' })
    return unwrapApiData(response)
  },
  async deleteReport(id) {
    const response = await apiClient.delete(`/reports/${id}`)
    return unwrapApiData(response)
  },
  async getClosedReports(params = {}) {
    const response = await apiClient.get('/reports/closed', { params })
    return unwrapApiData(response)
  },
  async getMyReports(params = {}) {
    const response = await apiClient.get('/reports/mine', { params })
    return unwrapApiData(response)
  },
  async getCategories() {
    const response = await apiClient.get('/categories')
    return unwrapApiData(response)
  },
  async getLocations() {
    const response = await apiClient.get('/locations')
    return unwrapApiData(response)
  }
}

export default reportService
