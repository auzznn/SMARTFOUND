<template>
  <div class="min-h-screen flex flex-col bg-gray-900">
    <AppNavbar v-if="!isAuthPage" />

    <main class="flex-1">
      <RouterView v-slot="{ Component, route }">
        <Transition name="fade" mode="out-in">
          <component :is="Component" :key="route.path" />
        </Transition>
      </RouterView>
    </main>

    <AppFooter v-if="!isAuthPage" />

    <!-- Global Toast Container -->
    <AppToast />
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppNavbar from '@/components/common/AppNavbar.vue'
import AppFooter from '@/components/common/AppFooter.vue'
import AppToast from '@/components/common/AppToast.vue'

const route    = useRoute()
const auth     = useAuthStore()

// Hide navbar/footer on auth pages
const isAuthPage = computed(() =>
  ['/login', '/register'].includes(route.path)
)

// On app mount: try silent token refresh to restore session
onMounted(async () => {
  if (!auth.isAuthenticated) {
    try {
      await auth.refreshToken()
      if (!auth.user) await auth.fetchMe()
    } catch {
      // No valid session — user stays logged out
    }
  }
})
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
