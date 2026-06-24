<template>
  <div class="min-h-screen bg-gray-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md">

      <!-- Logo -->
      <div class="text-center mb-8">
        <RouterLink to="/" class="inline-flex items-center gap-2 no-underline">
          <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center font-bold text-white">SF</div>
          <span class="text-2xl font-bold text-white">Smart<span class="text-indigo-400">Found</span></span>
        </RouterLink>
        <p class="text-gray-500 text-sm mt-2">UTM Lost &amp; Found Platform</p>
      </div>

      <!-- Card -->
      <div class="card p-8">
        <h2 class="text-xl font-semibold text-white mb-6">Sign in to your account</h2>

        <form @submit.prevent="handleLogin" class="space-y-5" novalidate>

          <!-- Username -->
          <div class="form-group">
            <label for="username">Username</label>
            <input
              id="username"
              v-model.trim="form.username"
              type="text"
              autocomplete="username"
              placeholder="your_username"
              :class="{ 'input-error': errors.username }"
              @blur="touchField('username')"
            />
            <span v-if="errors.username" class="form-error">{{ errors.username }}</span>
          </div>

          <!-- Password -->
          <div class="form-group">
            <label for="password">Password</label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                placeholder="••••••••"
                :class="{ 'input-error': errors.password }"
                class="w-full pr-10"
                @blur="touchField('password')"
              />
              <button
                type="button"
                class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-300"
                @click="showPassword = !showPassword"
                tabindex="-1"
              >
                {{ showPassword ? 'Hide' : 'Show' }}
              </button>
            </div>
            <span v-if="errors.password" class="form-error">{{ errors.password }}</span>
          </div>

          <!-- Submit -->
          <button type="submit" class="btn btn-primary w-full btn-lg" :disabled="submitting">
            <span v-if="submitting" class="inline-block w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" />
            {{ submitting ? 'Signing in...' : 'Sign In' }}
          </button>

          <!-- Divider -->
          <div v-if="googleEnabled" class="relative flex items-center gap-3">
            <div class="flex-1 h-px bg-gray-700" />
            <span class="text-xs text-gray-500">or</span>
            <div class="flex-1 h-px bg-gray-700" />
          </div>

          <!-- Google login -->
          <button
            v-if="googleEnabled"
            type="button"
            @click="handleGoogle"
            class="btn btn-secondary w-full"
            :disabled="submitting"
          >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
              <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
              <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
              <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
              <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            Continue with Google
          </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
          Don't have an account?
          <RouterLink to="/register" class="text-indigo-400 hover:text-indigo-300">Register here</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import { useRouter, useRoute } from 'vue-router'
import { validate, required, minLength } from '@/utils/validators'

const auth   = useAuthStore()
const toast  = useToast()
const router = useRouter()
const route  = useRoute()

const form         = reactive({ username: '', password: '' })
const errors       = reactive({ username: '', password: '' })
const showPassword = ref(false)
const submitting   = ref(false)
const touched      = reactive({ username: false, password: false })
const googleEnabled = computed(() => {
  const id = import.meta.env.VITE_GOOGLE_CLIENT_ID || ''
  return id && id !== 'your_google_client_id_here'
})

const rules = {
  username: [required],
  password: [required, minLength(6)]
}

function touchField(field) {
  touched[field] = true
  errors[field] = validate(form[field], rules[field]) || ''
}

function validateAll() {
  let valid = true
  for (const field of Object.keys(rules)) {
    errors[field] = validate(form[field], rules[field]) || ''
    if (errors[field]) valid = false
  }
  return valid
}

async function handleLogin() {
  if (!validateAll()) return

  submitting.value = true
  try {
    await auth.login(form.username, form.password)
    toast.success(`Welcome back, ${auth.user?.username || ''}!`)
    const redirect = route.query.redirect || (auth.isAdmin ? '/admin/reports' : '/reports')
    router.push(redirect)
  } catch (e) {
    const msg = e.response?.data?.message || 'Invalid username or password'
    toast.error(msg)
  } finally {
    submitting.value = false
  }
}

async function handleGoogle() {
  try {
    await auth.loginGoogle()
  } catch {
    toast.error('Google login unavailable')
  }
}
</script>
