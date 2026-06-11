import apiClient from './apiClient'

const reportService = {
  async getReports(params = {}) {
    const { data } = await apiClient.get('/reports', { params })
    return data
  },
  async getReport(id) {
    const { data } = await apiClient.get(`/reports/${id}`)
    return data
  },
  async createReport(formData) {
    const { data } = await apiClient.post('/reports', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    return data
  },
  async closeReport(id) {
    const { data } = await apiClient.patch(`/reports/${id}/close`)
    return data
  },
  async deleteReport(id) {
    const { data } = await apiClient.delete(`/reports/${id}`)
    return data
  },
  async getClosedReports(params = {}) {
    const { data } = await apiClient.get('/reports', { params: { ...params, status: 'closed' } })
    return data
  },
  async getMyReports(params = {}) {
    const { data } = await apiClient.get('/reports/my', { params })
    return data
  },
  async getCategories() {
    const { data } = await apiClient.get('/categories')
    return data
  },
  async getLocations() {
    const { data } = await apiClient.get('/locations')
    return data
  }
}

export default reportService
