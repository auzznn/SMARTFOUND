import { defineStore } from "pinia"
import { ref, computed } from "vue"
import authService from "@/services/authService"

export const useAuthStore = defineStore("auth", () => {
  const accessToken = ref(null)
  const user = ref(null)

  const isAuthenticated = computed(() => !!accessToken.value && !!user.value)
  const isAdmin   = computed(() => user.value?.rolename === "admin")
  const isOfficer = computed(() => user.value?.rolename === "officer")
  const isStudent = computed(() => user.value?.rolename === "student")

  async function login(username, password) {
    const response = await authService.login(username, password)
    _setSession(response)
    return response
  }

  async function loginGoogle() {
    const { url } = await authService.googleRedirect()
    if (url) window.location.href = url
  }

  async function refreshToken() {
    const response = await authService.refresh()
    _setSession(response)
    return response.access_token || response.accessToken || response.token
  }

  async function fetchMe() {
    const response = await authService.getMe()
    const fetchedUser = response.user || response
    user.value = _normaliseUser(fetchedUser)
    return user.value
  }

  async function logout() {
    try { await authService.logout() } catch {}
    finally { _clearSession() }
  }

  async function register(payload) {
    return authService.register(payload)
  }

  async function updateProfile(payload) {
    const response = await authService.updateProfile(payload)
    const updated = response.user || response
    user.value = { ...user.value, ..._normaliseUser(updated) }
    return user.value
  }

  function _setSession(response) {
    accessToken.value = response.access_token || response.accessToken || response.token || null
    if (response.user) user.value = _normaliseUser(response.user)
  }

  function _clearSession() {
    accessToken.value = null
    user.value = null
  }

  function _normaliseUser(u) {
    return {
      uuid:      u.uuid      || u.id    || null,
      username:  u.username               || "",
      fullname:  u.fullname  || u.full_name || "",
      contactno: u.contactno || u.contact_no || "",
      roleid:    u.roleid    || u.role_id  || null,
      rolename:  u.rolename  || u.role?.rolename || u.role_name || "student"
    }
  }

  function $reset() { _clearSession() }

  return {
    accessToken, user, isAuthenticated, isAdmin, isOfficer, isStudent,
    login, loginGoogle, refreshToken, fetchMe, logout, register, updateProfile, $reset
  }
})
