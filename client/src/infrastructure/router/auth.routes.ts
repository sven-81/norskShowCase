import LoginView from '@/ui/views/auth/LoginView.vue'
import RegisterView from '@/ui/views/auth/RegisterView.vue'

export default {
    path: '/',
    children: [
        { path: 'login', component: LoginView },
        { path: 'register', component: RegisterView }
    ]
}

