import { defineStore } from 'pinia'

import { fetchWrapper } from '@/request'
import { router } from '@/router'
import { useAlertStore } from '@/stores'

const newUserRoute: string = `${import.meta.env.VITE_BACKEND_URL}/user/new`
export const useUserStore = defineStore({
  id: 'users',
  state: () => ({
    users: {},
    user: {}
  }),
  actions: {
    async register(user) {
      const successMessage =
        'Registrierung erfolgreich. Du wirst informiert, wenn dein User freigeschaltet ist.'

      const alertStore = useAlertStore()

      try {
        await fetchWrapper.post(newUserRoute, user)
        await router.push('/login')

        alertStore.success(successMessage)
      } catch (error) {
        alertStore.mapAuthError(error)
      }
    }
  }
})
