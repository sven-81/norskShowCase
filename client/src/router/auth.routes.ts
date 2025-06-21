import { Login, Register } from '@/views/auth'
import Home from '@/views/HomeLayout.vue'

export default {
    path: '/',
    children: [
        {path: '/', component: Home},
        {path: '', redirect: 'login'},
        {path: 'login', component: Login},
        {path: 'register', component: Register},
    ]
};