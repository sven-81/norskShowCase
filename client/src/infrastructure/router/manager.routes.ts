import ManageWordView from '@/ui/views/manager/ManageWordView.vue'
import ManageVerbView from '@/ui/views/manager/ManageVerbView.vue'

export default {
    path: '/manage',
    children: [
        { path: 'words', component: ManageWordView },
        { path: 'verbs', component: ManageVerbView }
    ]
}

