import { createApp } from 'vue'
import { createPinia, setActivePinia } from 'pinia'

import App from '@/ui/App.vue'
import { router } from '@/infrastructure/router'
import { registerAuthProvider } from '@/infrastructure/http/HttpClient'
import { useAuthStore } from '@/infrastructure/store/auth.store'

const app = createApp(App)

const pinia = createPinia()
setActivePinia(pinia)

app.use(pinia)
app.use(router)

const authStore = useAuthStore()
registerAuthProvider({
    getToken: () => {
        if (authStore.user !== null && authStore.user.token) {
            return authStore.user.token
        }
        return null
    },
    logout: () => {
        authStore.logout()
    }
})

app.mount('#app')

