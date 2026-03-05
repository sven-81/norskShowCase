import { defineStore } from 'pinia'
import { httpClient } from '@/infrastructure/http/HttpClient'

const newUserRoute: string = `${import.meta.env.VITE_BACKEND_URL}/user/new`

interface UserState {
    users: object
    user: object
}

export const useUserStore = defineStore('users', {
    state: (): UserState => ({
        users: {},
        user: {}
    }),
    actions: {
        async register(user: {
            username: string
            firstName: string
            lastName: string
            password: string
        }): Promise<void> {
            const successMessage =
                'Registrierung erfolgreich. Du wirst informiert, wenn dein User freigeschaltet ist.'

            try {
                await httpClient.post(newUserRoute, user)
                const { router } = await import('@/infrastructure/router')
                await router.push('/login')

                const { useAlertStore } = await import('@/infrastructure/store/alert.store')
                const alertStore = useAlertStore()
                alertStore.success(successMessage)
            } catch (error) {
                const { useAlertStore } = await import('@/infrastructure/store/alert.store')
                const alertStore = useAlertStore()
                alertStore.mapAuthError(error)
            }
        }
    }
})

