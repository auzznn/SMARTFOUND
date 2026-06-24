import apiClient, { unwrapApiData } from './apiClient'

const commentService = {
  async getComments(reportId) {
    const response = await apiClient.get(`/reports/${reportId}/comments`)
    return unwrapApiData(response)
  },
  async createComment(reportId, comment) {
    const response = await apiClient.post(`/reports/${reportId}/comments`, { comment })
    return unwrapApiData(response)
  }
}

export default commentService
