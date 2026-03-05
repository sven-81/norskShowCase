import { defineStore } from 'pinia'
import { jwtDecode } from 'jwt-decode'
import { httpClient } from '@/infrastructure/http/HttpClient'

const api: string = `${import.meta.env.VITE_BACKEND_URL}`

interface AuthState {
    user: any
    returnUrl: string | null
}

export const useAuthStore = defineStore('auth', {
    state: (): AuthState => ({
        user: JSON.parse(localStorage.getItem('user') as string),
        returnUrl: null
    }),
    actions: {
        async login(username: string, password: string): Promise<void> {
            try {
                const user = await httpClient.post(api + '/user', { username, password })
                const decodedToken: any = jwtDecode(user.token)
                const userWithScope = { ...user, scope: decodedToken.scope }

                this.user = userWithScope
                localStorage.setItem('user', JSON.stringify(userWithScope))

                const { router } = await import('@/infrastructure/router')
                await router.push(this.returnUrl || '/')
            } catch (error) {
                const { useAlertStore } = await import('@/infrastructure/store/alert.store')
                const alertStore = useAlertStore()
                alertStore.mapAuthError(error)
            }
        },
        async logout(): Promise<void> {
            this.user = null
            localStorage.removeItem('user')
            const { router } = await import('@/infrastructure/router')
            await router.push('/login')
        }
    }
})

