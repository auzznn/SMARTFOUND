<template>
  <div class="min-h-screen flex items-center justify-center p-4">
    <div class="text-center max-w-md">
      <div class="text-8xl font-bold text-indigo-600 mb-4 opacity-80">404</div>
      <h1 class="text-3xl font-bold text-white mb-3">Page Not Found</h1>
      <p class="text-gray-500 mb-8">
        Oops! The page you're looking for doesn't exist or has been moved.
      </p>
      <div class="flex gap-3 justify-center">
        <button @click="router.back()" class="btn btn-secondary">
          Go Back
        </button>
        <RouterLink to="/" class="btn btn-primary no-underline">
          Go Home
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
const router = useRouter()
</script>
