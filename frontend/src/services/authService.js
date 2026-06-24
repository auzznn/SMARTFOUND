import apiClient, { unwrapApiData } from './apiClient'

const authService = {
  /**
   * Login with username + password.
   * Backend sets httpOnly refresh-token cookie.
   * Returns { accessToken, user }
   */
  async login(username, password) {
    const response = await apiClient.post('/auth/login', { username, password })
    return unwrapApiData(response)
  },

  /**
   * Register new user.
   */
  async register({ username, fullname, contactno, password, roleid }) {
    const response = await apiClient.post('/auth/register', {
      username,
      fullname,
      contactno,
      password,
      roleid
    })
    return unwrapApiData(response)
  },

  /**
   * Redirect to Google OAuth — backend provides the URL.
   */
  async googleRedirect() {
    return { url: apiClient.defaults.baseURL + '/auth/google/redirect' }
  },

  /**
   * Call the refresh endpoint. Backend reads httpOnly cookie, returns new accessToken.
   */
  async refresh() {
    const response = await apiClient.post('/auth/refresh')
    return unwrapApiData(response)
  },

  /**
   * Logout — backend clears the refresh-token cookie.
   */
  async logout() {
    await apiClient.post('/auth/logout')
  },

  /**
   * Get currently authenticated user's profile.
   */
  async getMe() {
    const response = await apiClient.get('/users/me')
    return unwrapApiData(response)
  },

  /**
   * Update profile fields (fullname, contactno).
   */
  async updateProfile({ fullname, contactno }) {
    const response = await apiClient.patch('/users/me', { fullname, contactno })
    return unwrapApiData(response)
  }
}

export default authService
