<template>
  <div>
    <!-- ── Hero ───────────────────────────────────────── -->
    <section class="relative overflow-hidden bg-gradient-to-br from-gray-900 via-gray-900 to-indigo-950 py-24">
      <div class="absolute inset-0 opacity-5">
        <div class="absolute top-20 left-10 w-72 h-72 bg-indigo-500 rounded-full blur-3xl" />
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-purple-500 rounded-full blur-3xl" />
      </div>

      <div class="page-container relative text-center">
        <div class="inline-flex items-center gap-2 bg-indigo-900/40 border border-indigo-700/50 rounded-full px-4 py-1.5 text-sm text-indigo-300 mb-6">
          <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse" />
          UTM Lost &amp; Found Platform
        </div>

        <h1 class="text-5xl md:text-6xl font-bold text-white mb-4 leading-tight">
          Lost something?<br/>
          <span class="text-gradient">Found something?</span>
        </h1>

        <p class="text-gray-400 text-lg md:text-xl max-w-2xl mx-auto mb-10">
          SmartFound connects UTM students and staff to recover lost items quickly.
          Report what you've lost or found and help your community.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <RouterLink
            v-if="auth.isAuthenticated"
            to="/reports/make?type=lost"
            class="btn btn-danger btn-lg no-underline glow-indigo"
          >
            Report Lost Item
          </RouterLink>
          <RouterLink
            v-if="auth.isAuthenticated"
            to="/reports/make?type=found"
            class="btn btn-success btn-lg no-underline"
          >
            Report Found Item
          </RouterLink>
          <RouterLink
            v-if="!auth.isAuthenticated"
            to="/login"
            class="btn btn-primary btn-lg no-underline"
          >
            Get Started
          </RouterLink>
          <RouterLink
            to="/reports"
            class="btn btn-outline btn-lg no-underline"
          >
            Browse Reports
          </RouterLink>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-6 max-w-xl mx-auto mt-16">
          <div v-for="stat in stats" :key="stat.label" class="text-center">
            <p class="text-3xl font-bold text-indigo-400">{{ stat.value }}</p>
            <p class="text-gray-500 text-sm mt-1">{{ stat.label }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ── How it works ───────────────────────────────── -->
    <section class="py-20 bg-gray-900">
      <div class="page-container">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-white mb-3">How It Works</h2>
          <p class="text-gray-500">Simple steps to recover your belongings</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="step in steps" :key="step.title" class="text-center">
            <div class="w-14 h-14 bg-indigo-900/50 border border-indigo-700/50 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4">
              {{ step.icon }}
            </div>
            <h3 class="font-semibold text-white mb-2">{{ step.title }}</h3>
            <p class="text-gray-500 text-sm">{{ step.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Recent reports ─────────────────────────────── -->
    <section class="py-20 bg-gray-900/50">
      <div class="page-container">
        <div class="flex items-center justify-between mb-8">
          <h2 class="text-2xl font-bold text-white">Recent Reports</h2>
          <RouterLink to="/reports" class="text-sm text-indigo-400 hover:text-indigo-300 no-underline">
            View all &rarr;
          </RouterLink>
        </div>

        <div v-if="loadingReports" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div v-for="i in 4" :key="i" class="skeleton h-56 rounded-xl" />
        </div>

        <div v-else-if="!recentReports.length" class="empty-state">
          <p class="empty-state-title">No reports yet</p>
          <p class="empty-state-desc">Be the first to make a report!</p>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <ReportCard v-for="r in recentReports" :key="r.reportid" :report="r" />
        </div>
      </div>
    </section>

    <!-- ── FAQ accordion (jQuery) ────────────────────── -->
    <section class="py-20 bg-gray-900">
      <div class="page-container max-w-3xl mx-auto">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-white mb-3">FAQ</h2>
          <p class="text-gray-500">Frequently asked questions</p>
        </div>

        <div id="faqAccordion" class="space-y-3">
          <div
            v-for="(faq, idx) in faqs"
            :key="idx"
            class="card border border-gray-700"
          >
            <button
              class="faq-toggle w-full flex items-center justify-between px-5 py-4 text-left text-white font-medium hover:text-indigo-300 transition-colors"
              :data-idx="idx"
            >
              {{ faq.q }}
              <span class="faq-icon text-gray-500 text-xl leading-none flex-shrink-0 ml-4">+</span>
            </button>
            <div class="faq-body hidden px-5 pb-4 text-gray-400 text-sm">
              {{ faq.a }}
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import reportService from '@/services/reportService'
import ReportCard from '@/components/reports/ReportCard.vue'
import $ from 'jquery'

const auth = useAuthStore()

const loadingReports = ref(true)
const recentReports  = ref([])

const stats = ref([
  { value: '—', label: 'Reports Made' },
  { value: '—', label: 'Items Recovered' },
  { value: '—', label: 'Active Users' }
])

const steps = [
  { icon: '📝', title: 'Submit a Report',   desc: 'Fill in details about the lost or found item — name, category, location, and date.' },
  { icon: '🔍', title: 'Browse & Match',    desc: 'Browse reports from others to find a match for your lost item.' },
  { icon: '🤝', title: 'Connect & Recover', desc: 'Use the contact info or comments to connect and arrange the return.' }
]

const faqs = [
  { q: 'Who can use SmartFound?',              a: 'Any registered UTM student or staff member. Sign up with your university account.' },
  { q: 'How do I report a found item?',        a: 'Click "Report Found Item" from the homepage or navigation, fill in the form with details and a photo.' },
  { q: 'Can I remain anonymous?',              a: 'Your contact number is only shown on the report detail page to authenticated users.' },
  { q: 'What happens after I close a report?', a: 'Closed reports are archived and no longer accept comments, but remain visible for reference.' },
  { q: 'How long are reports kept?',           a: 'Reports are kept indefinitely unless deleted by an officer or admin.' }
]

async function loadRecentReports() {
  try {
    const res = await reportService.getReports({ status: 'open', limit: 4, page: 1 })
    recentReports.value = res.reports || res.data || []
  } catch {
    // silent fail on homepage
  } finally {
    loadingReports.value = false
  }
}

onMounted(async () => {
  await loadRecentReports()

  // jQuery accordion (course requirement)
  $('#faqAccordion').on('click', '.faq-toggle', function () {
    const $btn  = $(this)
    const $body = $btn.next('.faq-body')
    const $icon = $btn.find('.faq-icon')

    // Close others
    $('#faqAccordion .faq-body').not($body).slideUp(200)
    $('#faqAccordion .faq-icon').not($icon).text('+')

    // Toggle this
    $body.slideToggle(200)
    $icon.text($body.is(':hidden') ? '+' : '−')
  })
})
</script>
