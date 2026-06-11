import apiClient from './apiClient'

const adminService = {
  async getUsers(params = {}) {
    const { data } = await apiClient.get('/admin/users', { params })
    return data
  },
  async deleteUser(id) {
    const { data } = await apiClient.delete(`/admin/users/${id}`)
    return data
  },
  async getAllReports(params = {}) {
    const { data } = await apiClient.get('/admin/reports', { params })
    return data
  },
  async deleteReport(id) {
    const { data } = await apiClient.delete(`/admin/reports/${id}`)
    return data
  }
}

export default adminService
