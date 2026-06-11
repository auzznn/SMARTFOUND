<template>
  <footer class="bg-gray-900 border-t border-gray-800 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 bg-indigo-600 rounded-lg flex items-center justify-center font-bold text-white text-xs">SF</div>
          <span class="text-white font-semibold">SmartFound</span>
          <span class="text-gray-500 text-sm">— UTM Lost &amp; Found Platform</span>
        </div>
        <p class="text-gray-500 text-sm text-center">
          &copy; {{ year }} Universiti Teknologi Malaysia. All rights reserved.
        </p>
        <div class="flex gap-4 text-sm text-gray-500">
          <RouterLink to="/" class="hover:text-gray-300 no-underline transition-colors">Home</RouterLink>
          <RouterLink to="/reports" class="hover:text-gray-300 no-underline transition-colors">Reports</RouterLink>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
const year = new Date().getFullYear()
</script>
