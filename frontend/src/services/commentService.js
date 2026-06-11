import apiClient from './apiClient'

const commentService = {
  async getComments(reportId) {
    const { data } = await apiClient.get(`/reports/${reportId}/comments`)
    return data
  },
  async createComment(reportId, comment) {
    const { data } = await apiClient.post(`/reports/${reportId}/comments`, { comment })
    return data
  },
  async deleteComment(reportId, commentId) {
    const { data } = await apiClient.delete(`/reports/${reportId}/comments/${commentId}`)
    return data
  }
}

export default commentService
