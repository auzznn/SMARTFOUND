import apiClient from './apiClient'

const authService = {
  /**
   * Login with username + password.
   * Backend sets httpOnly refresh-token cookie.
   * Returns { accessToken, user }
   */
  async login(username, password) {
    const { data } = await apiClient.post('/auth/login', { username, password })
    return data
  },

  /**
   * Register new user.
   */
  async register({ username, fullname, contactno, password, roleid }) {
    const { data } = await apiClient.post('/auth/register', {
      username,
      fullname,
      contactno,
      password,
      roleid
    })
    return data
  },

  /**
   * Redirect to Google OAuth — backend provides the URL.
   */
  async googleRedirect() {
    const { data } = await apiClient.get('/auth/google')
    return data // { url: '...' }
  },

  /**
   * Call the refresh endpoint. Backend reads httpOnly cookie, returns new accessToken.
   */
  async refresh() {
    const { data } = await apiClient.post('/auth/refresh')
    return data // { accessToken }
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
    const { data } = await apiClient.get('/auth/me')
    return data // { user }
  },

  /**
   * Update profile fields (fullname, contactno).
   */
  async updateProfile({ fullname, contactno }) {
    const { data } = await apiClient.put('/auth/me', { fullname, contactno })
    return data
  }
}

export default authService
