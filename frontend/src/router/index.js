import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  { path: '/', name: 'home', component: () => import('@/pages/HomePage.vue'), meta: { public: true } },
  { path: '/login', name: 'login', component: () => import('@/pages/LoginPage.vue'), meta: { guestOnly: true } },
  { path: '/register', name: 'register', component: () => import('@/pages/RegisterPage.vue'), meta: { guestOnly: true } },
  { path: '/reports', name: 'reports', component: () => import('@/pages/AllReportsPage.vue'), meta: { public: true } },
  { path: '/reports/closed', name: 'reports-closed', component: () => import('@/pages/ClosedReportsPage.vue'), meta: { public: true } },
  { path: '/reports/make', name: 'reports-make', component: () => import('@/pages/MakeReportPage.vue'), meta: { requiresAuth: true } },
  { path: '/reports/:id', name: 'report-detail', component: () => import('@/pages/ReportDetailPage.vue'), meta: { public: true } },
  { path: '/profile', name: 'profile', component: () => import('@/pages/MyProfilePage.vue'), meta: { requiresAuth: true } },
  { path: '/admin/reports', name: 'admin-reports', component: () => import('@/pages/admin/ManageReportsPage.vue'), meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/admin/users', name: 'admin-users', component: () => import('@/pages/admin/ManageUsersPage.vue'), meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('@/pages/NotFoundPage.vue') }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition
    return { top: 0 }
  }
})

router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()

  // Restore session only for routes that need auth
  if (!auth.isAuthenticated && to.meta.requiresAuth) {
    try { await auth.refreshToken(); if (!auth.user) await auth.fetchMe() } catch {}
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }
  if (to.meta.requiresAdmin && !auth.isAdmin) {
    return next({ name: 'reports' })
  }
  if (to.meta.guestOnly && auth.isAuthenticated) {
    return next({ name: 'reports' })
  }
  next()
})

export default router
