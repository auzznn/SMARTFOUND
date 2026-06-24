import apiClient, { unwrapApiData } from './apiClient'

const adminService = {
  async getUsers(params = {}) {
    const response = await apiClient.get('/users', { params })
    return unwrapApiData(response)
  },
  async deleteUser(id) {
    const response = await apiClient.delete(`/users/${id}`)
    return unwrapApiData(response)
  },
  async getAllReports(params = {}) {
    const response = await apiClient.get('/reports', { params: { status: 'all', ...params } })
    return unwrapApiData(response)
  },
  async deleteReport(id) {
    const response = await apiClient.delete(`/reports/${id}`)
    return unwrapApiData(response)
  }
}

export default adminService
