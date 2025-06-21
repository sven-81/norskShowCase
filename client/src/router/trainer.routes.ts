import {TrainingVerbs, TrainingWords} from '@/views/trainer';

export default {
    path: '/train',
    children: [
        {path: 'words', component: TrainingWords},
        {path: 'verbs', component: TrainingVerbs}
    ]
};