<template>
  <nav class="bg-gray-900 border-b border-gray-800 sticky top-0 z-40 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">

        <!-- Logo -->
        <RouterLink to="/" class="flex items-center gap-2 no-underline">
          <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center font-bold text-white text-sm">
            SF
          </div>
          <span class="text-xl font-bold text-white">Smart<span class="text-indigo-400">Found</span></span>
        </RouterLink>

        <!-- Desktop nav links -->
        <div class="hidden md:flex items-center gap-1">
          <RouterLink
            v-for="link in navLinks"
            :key="link.to"
            :to="link.to"
            class="px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-gray-800 transition-colors no-underline"
            active-class="text-white bg-gray-800"
          >
            {{ link.label }}
          </RouterLink>

          <!-- Admin dropdown -->
          <div v-if="auth.isAdmin" class="relative" id="adminDropdown">
            <button
              @click="adminOpen = !adminOpen"
              class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-medium text-gray-300 hover:text-white hover:bg-gray-800 transition-colors"
            >
              Admin
              <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': adminOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <Transition name="dropdown">
              <div
                v-if="adminOpen"
                v-click-outside="() => adminOpen = false"
                class="absolute right-0 mt-1 w-48 bg-gray-800 rounded-xl border border-gray-700 shadow-xl py-1 z-50"
              >
                <RouterLink
                  to="/admin/reports"
                  class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white no-underline transition-colors"
                  @click="adminOpen = false"
                >
                  Manage Reports
                </RouterLink>
                <RouterLink
                  to="/admin/users"
                  class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white no-underline transition-colors"
                  @click="adminOpen = false"
                >
                  Manage Users
                </RouterLink>
              </div>
            </Transition>
          </div>
        </div>

        <!-- Right side: auth buttons or user menu -->
        <div class="hidden md:flex items-center gap-3">
          <template v-if="auth.isAuthenticated">
            <span class="text-sm text-gray-400">{{ auth.user?.username }}</span>
            <span :class="roleBadgeClass">{{ auth.user?.rolename }}</span>
            <RouterLink to="/profile" class="btn btn-secondary btn-sm no-underline">Profile</RouterLink>
            <button @click="handleLogout" class="btn btn-danger btn-sm">Logout</button>
          </template>
          <template v-else>
            <RouterLink to="/login"    class="btn btn-secondary btn-sm no-underline">Login</RouterLink>
            <RouterLink to="/register" class="btn btn-primary btn-sm no-underline">Register</RouterLink>
          </template>
        </div>

        <!-- Mobile hamburger -->
        <button
          id="mobileMenuBtn"
          class="md:hidden p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-colors"
          aria-label="Toggle menu"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile menu (jQuery-controlled) -->
    <div id="mobileMenu" class="hidden md:hidden border-t border-gray-800 bg-gray-900">
      <div class="px-4 py-3 space-y-1">
        <RouterLink
          v-for="link in navLinks"
          :key="'m-' + link.to"
          :to="link.to"
          class="block px-3 py-2 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-gray-800 no-underline"
          @click="closeMobileMenu"
        >
          {{ link.label }}
        </RouterLink>

        <template v-if="auth.isAdmin">
          <RouterLink
            to="/admin/reports"
            class="block px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-gray-800 no-underline"
            @click="closeMobileMenu"
          >Admin: Reports</RouterLink>
          <RouterLink
            to="/admin/users"
            class="block px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-gray-800 no-underline"
            @click="closeMobileMenu"
          >Admin: Users</RouterLink>
        </template>

        <div class="pt-2 border-t border-gray-800 mt-2">
          <template v-if="auth.isAuthenticated">
            <RouterLink to="/profile" class="block px-3 py-2 text-sm text-gray-300 no-underline" @click="closeMobileMenu">Profile</RouterLink>
            <button @click="handleLogout" class="w-full text-left px-3 py-2 text-sm text-red-400 hover:text-red-300">Logout</button>
          </template>
          <template v-else>
            <RouterLink to="/login"    class="block px-3 py-2 text-sm text-gray-300 no-underline" @click="closeMobileMenu">Login</RouterLink>
            <RouterLink to="/register" class="block px-3 py-2 text-sm text-indigo-400 no-underline" @click="closeMobileMenu">Register</RouterLink>
          </template>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useAuth } from '@/composables/useAuth'
import $ from 'jquery'

const auth     = useAuthStore()
const { logout } = useAuth()
const adminOpen  = ref(false)

const navLinks = computed(() => {
  const links = [
    { to: '/',               label: 'Home' },
    { to: '/reports',        label: 'All Reports' },
    { to: '/reports/closed', label: 'Closed Reports' }
  ]
  if (auth.isAuthenticated) {
    links.push({ to: '/reports/make', label: 'Report Item' })
  }
  return links
})

const roleBadgeClass = computed(() => {
  const r = auth.user?.rolename
  if (r === 'admin')   return 'badge badge-admin'
  if (r === 'officer') return 'badge badge-officer'
  return 'badge badge-student'
})

async function handleLogout() {
  adminOpen.value = false
  closeMobileMenu()
  await logout()
}

function closeMobileMenu() {
  $('#mobileMenu').slideUp(200)
}

// jQuery mobile menu toggle (course requirement)
onMounted(() => {
  $('#mobileMenuBtn').on('click', function () {
    $('#mobileMenu').slideToggle(200)
  })
})

// Click-outside directive for admin dropdown
const vClickOutside = {
  mounted(el, binding) {
    el._clickOutside = (e) => {
      if (!el.contains(e.target)) binding.value(e)
    }
    document.addEventListener('click', el._clickOutside)
  },
  unmounted(el) {
    document.removeEventListener('click', el._clickOutside)
  }
}
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
