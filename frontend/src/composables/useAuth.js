import { useAuthStore } from "@/stores/auth"
import { useToast } from "./useToast"
import { useRouter } from "vue-router"

export function useAuth() {
  const auth   = useAuthStore()
  const toast  = useToast()
  const router = useRouter()

  async function login(username, password) {
    try {
      await auth.login(username, password)
      toast.success("Welcome back, " + (auth.user?.username || "") + "!")
      const dest = auth.isAdmin ? "/admin/reports" : "/reports"
      router.push(dest)
      return true
    } catch (e) {
      const msg = e.response?.data?.message || "Login failed"
      toast.error(msg)
      return false
    }
  }

  async function register(payload) {
    try {
      await auth.register ? auth.register(payload) : null
      toast.success("Account created! Please log in.")
      router.push("/login")
      return true
    } catch (e) {
      const msg = e.response?.data?.message || "Registration failed"
      toast.error(msg)
      return false
    }
  }

  async function logout() {
    await auth.logout()
    toast.info("You have been logged out.")
    router.push("/login")
  }

  return { auth, login, register, logout }
}
