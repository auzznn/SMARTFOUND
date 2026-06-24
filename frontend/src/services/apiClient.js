import axios from "axios"

const BASE_URL = import.meta.env.VITE_API_BASE_URL || "http://localhost:8080/api/v1"

const apiClient = axios.create({
  baseURL: BASE_URL,
  withCredentials: true,
  headers: { "Content-Type": "application/json" },
  timeout: 15000
})

export function unwrapApiData(response) {
  return response.data?.data ?? response.data
}

// Lazily resolved store reference to avoid circular deps
let _authStore = null
async function getAuthStore() {
  if (!_authStore) {
    const { useAuthStore } = await import("@/stores/auth")
    _authStore = useAuthStore()
  }
  return _authStore
}

let _toastFn = null
async function showToast(message, type) {
  try {
    if (!_toastFn) {
      const { useToast } = await import("@/composables/useToast")
      _toastFn = useToast()
    }
    if (type === "error") _toastFn.error(message)
    else if (type === "info") _toastFn.info(message)
    else _toastFn.addToast(message, type)
  } catch {}
}

// Request interceptor: attach Bearer token
apiClient.interceptors.request.use(
  async (config) => {
    try {
      const auth = await getAuthStore()
      if (auth.accessToken) {
        config.headers.Authorization = "Bearer " + auth.accessToken
      }
    } catch {}
    return config
  },
  (error) => Promise.reject(error)
)

// Response interceptor
let _isRefreshing = false
let _pendingQueue = []

function processPending(error, token = null) {
  _pendingQueue.forEach(({ resolve, reject }) => {
    if (error) reject(error)
    else resolve(token)
  })
  _pendingQueue = []
}

apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config

    if (!error.response) {
      showToast("Network error. Please check your connection.", "error")
      return Promise.reject(error)
    }

    const status = error.response.status
    const requestUrl = originalRequest?.url || ''

    // Never retry auth endpoints (refresh, logout, login) to prevent infinite loops
    if (requestUrl.includes('/auth/')) {
      return Promise.reject(error)
    }

    if (status === 401 && !originalRequest._retry) {
      if (_isRefreshing) {
        return new Promise((resolve, reject) => {
          _pendingQueue.push({ resolve, reject })
        }).then((token) => {
          originalRequest.headers.Authorization = "Bearer " + token
          return apiClient(originalRequest)
        })
      }

      originalRequest._retry = true
      _isRefreshing = true

      try {
        const auth = await getAuthStore()
        const newToken = await auth.refreshToken()
        processPending(null, newToken)
        originalRequest.headers = originalRequest.headers || {}
        originalRequest.headers.Authorization = "Bearer " + newToken
        return apiClient(originalRequest)
      } catch (refreshError) {
        processPending(refreshError, null)
        // Clear session locally — do NOT call auth.logout() as it makes
        // another API call which would trigger the interceptor again
        try {
          const auth = await getAuthStore()
          auth.$reset()
          _authStore = null
        } catch {}
        return Promise.reject(refreshError)
      } finally {
        _isRefreshing = false
      }
    }

    if (status === 403) {
      showToast("Access denied - you do not have permission.", "error")
      return Promise.reject(error)
    }

    const message =
      error.response?.data?.message ||
      error.response?.data?.error ||
      "Request failed (" + status + ")"

    // Do not toast on 401 (handled by refresh flow or redirect)
    if (status !== 401) showToast(message, "error")

    return Promise.reject(error)
  }
)

export default apiClient
