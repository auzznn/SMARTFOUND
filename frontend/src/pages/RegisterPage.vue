<template>
  <div class="min-h-screen bg-gray-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md">

      <!-- Logo -->
      <div class="text-center mb-8">
        <RouterLink to="/" class="inline-flex items-center gap-2 no-underline">
          <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center font-bold text-white">SF</div>
          <span class="text-2xl font-bold text-white">Smart<span class="text-indigo-400">Found</span></span>
        </RouterLink>
        <p class="text-gray-500 text-sm mt-2">Create your account</p>
      </div>

      <div class="card p-8">
        <h2 class="text-xl font-semibold text-white mb-6">Register</h2>

        <form @submit.prevent="handleRegister" class="space-y-4" novalidate>

          <!-- Username -->
          <div class="form-group">
            <label for="reg-username">Username</label>
            <input id="reg-username" v-model.trim="form.username" type="text" placeholder="e.g. john_doe"
              :class="{ 'input-error': errors.username }" @blur="touch('username')" />
            <span v-if="errors.username" class="form-error">{{ errors.username }}</span>
          </div>

          <!-- Full Name -->
          <div class="form-group">
            <label for="reg-fullname">Full Name</label>
            <input id="reg-fullname" v-model.trim="form.fullname" type="text" placeholder="John Doe"
              :class="{ 'input-error': errors.fullname }" @blur="touch('fullname')" />
            <span v-if="errors.fullname" class="form-error">{{ errors.fullname }}</span>
          </div>

          <!-- Contact No -->
          <div class="form-group">
            <label for="reg-contact">Contact Number</label>
            <input id="reg-contact" v-model.trim="form.contactno" type="tel" placeholder="+60 12-345 6789"
              :class="{ 'input-error': errors.contactno }" @blur="touch('contactno')" />
            <span v-if="errors.contactno" class="form-error">{{ errors.contactno }}</span>
          </div>

          <!-- Password -->
          <div class="form-group">
            <label for="reg-password">Password</label>
            <div class="relative">
              <input id="reg-password" v-model="form.password"
                :type="showPass ? 'text' : 'password'"
                placeholder="Min 8 characters" class="w-full pr-10"
                :class="{ 'input-error': errors.password }" @blur="touch('password')" />
              <button type="button" class="absolute inset-y-0 right-3 text-gray-500 hover:text-gray-300 text-sm" tabindex="-1" @click="showPass = !showPass">
                {{ showPass ? 'Hide' : 'Show' }}
              </button>
            </div>
            <span v-if="errors.password" class="form-error">{{ errors.password }}</span>
          </div>

          <!-- Confirm Password -->
          <div class="form-group">
            <label for="reg-confirm">Confirm Password</label>
            <input id="reg-confirm" v-model="form.confirm"
              :type="showPass ? 'text' : 'password'"
              placeholder="Re-enter password"
              :class="{ 'input-error': errors.confirm }" @blur="touch('confirm')" />
            <span v-if="errors.confirm" class="form-error">{{ errors.confirm }}</span>
          </div>

          <button type="submit" class="btn btn-primary w-full btn-lg" :disabled="submitting">
            <span v-if="submitting" class="inline-block w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" />
            {{ submitting ? 'Creating account...' : 'Create Account' }}
          </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
          Already have an account?
          <RouterLink to="/login" class="text-indigo-400 hover:text-indigo-300">Sign in</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import authService from '@/services/authService'
import { useToast } from '@/composables/useToast'
import { validate, required, minLength, validPhone, validUsername, passwordMatch } from '@/utils/validators'

const router     = useRouter()
const toast      = useToast()
const submitting = ref(false)
const showPass   = ref(false)

const form   = reactive({ username: '', fullname: '', contactno: '', password: '', confirm: '' })
const errors = reactive({ username: '', fullname: '', contactno: '', password: '', confirm: '' })

const rules = () => ({
  username:  [required, validUsername],
  fullname:  [required, minLength(2)],
  contactno: [required, validPhone],
  password:  [required, minLength(8)],
  confirm:   [required, passwordMatch(form.password)]
})

function touch(field) {
  errors[field] = validate(form[field], rules()[field]) || ''
}

function validateAll() {
  let valid = true
  for (const [field, fieldRules] of Object.entries(rules())) {
    errors[field] = validate(form[field], fieldRules) || ''
    if (errors[field]) valid = false
  }
  return valid
}

async function handleRegister() {
  if (!validateAll()) return
  submitting.value = true
  try {
    await authService.register({
      username:  form.username,
      fullname:  form.fullname,
      contactno: form.contactno,
      password:  form.password,
      roleid:    null  // backend assigns default student role
    })
    toast.success('Account created! Please sign in.')
    router.push('/login')
  } catch (e) {
    const msg = e.response?.data?.message || 'Registration failed. Please try again.'
    toast.error(msg)
  } finally {
    submitting.value = false
  }
}
</script>
