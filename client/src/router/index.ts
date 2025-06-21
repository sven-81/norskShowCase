import { createRouter, createWebHistory } from 'vue-router'
import Home from '@/views/HomeLayout.vue'
import Imprint from '@/views/ImprintLayout.vue'

import { useAlertStore, useAuthStore } from '@/stores'

import AuthRoutes from './auth.routes'
import TrainerRoutes from './trainer.routes'
import ManagerRoutes from './manager.routes'

function redirectEverythingElseToHomePage() {
  return { path: '/:pathMatch(.*)*', redirect: '/' }
}

export const routes = [
  { path: '/', component: Home },
  { path: '/imprint', component: Imprint },
  { ...AuthRoutes },
  { ...TrainerRoutes },
  { ...ManagerRoutes },
  redirectEverythingElseToHomePage()
]

export const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  linkActiveClass: 'active',
  routes
})

export async function routerGuard(to) {
  const authStore = useAuthStore()
  const alertStore = useAlertStore()

  clearOldAlerts(alertStore)

  if (isAuthRequired(to) && !isUserLoggedIn(authStore)) {
    saveReturnUrl(to, authStore)
    return redirectToLogin()
  }

  return true
}

router.beforeEach(routerGuard)

function clearOldAlerts(alertStore) {
  alertStore.clear()
}

function isAuthRequired(to) {
  const publicPages = ['/', '/login', '/register', '/imprint']
  return !publicPages.includes(to.path)
}

function isUserLoggedIn(authStore) {
  return !!authStore.user
}

function saveReturnUrl(to, authStore) {
  authStore.returnUrl = to.fullPath
}

function redirectToLogin() {
  return '/login'
}
