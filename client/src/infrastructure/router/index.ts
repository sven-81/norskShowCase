import { createRouter, createWebHistory } from 'vue-router'
import HomeLayout from '@/ui/layouts/HomeLayout.vue'
import ImprintLayout from '@/ui/layouts/ImprintLayout.vue'
import authRoutes from '@/infrastructure/router/auth.routes'
import trainerRoutes from '@/infrastructure/router/trainer.routes'
import managerRoutes from '@/infrastructure/router/manager.routes'

function redirectEverythingElseToHomePage() {
    return { path: '/:pathMatch(.*)*', redirect: '/' }
}

export const routes = [
    { path: '/', component: HomeLayout },
    { path: '/imprint', component: ImprintLayout },
    { ...authRoutes },
    { ...trainerRoutes },
    { ...managerRoutes },
    redirectEverythingElseToHomePage()
]

export const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    linkActiveClass: 'active',
    routes
})

export async function routerGuard(to: any): Promise<boolean | string> {
    const { useAuthStore } = await import('@/infrastructure/store/auth.store')
    const { useAlertStore } = await import('@/infrastructure/store/alert.store')
    const authStore = useAuthStore()
    const alertStore = useAlertStore()

    alertStore.clear()

    const publicPages = ['/', '/login', '/register', '/imprint']
    const isAuthRequired = !publicPages.includes(to.path)

    if (isAuthRequired && !authStore.user) {
        authStore.returnUrl = to.fullPath
        return '/login'
    }

    return true
}

router.beforeEach(routerGuard)

