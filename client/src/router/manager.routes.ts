import {ManageVerb, ManageWord} from '@/views/manager';

export default {
    path: '/manage',
    children: [
        {path: 'words', component: ManageWord},
        {path: 'verbs', component: ManageVerb},
    ]
};