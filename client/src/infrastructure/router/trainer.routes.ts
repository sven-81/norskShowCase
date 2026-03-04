import TrainingWordsView from '@/ui/views/trainer/TrainingWordsView.vue'
import TrainingVerbsView from '@/ui/views/trainer/TrainingVerbsView.vue'

export default {
    path: '/train',
    children: [
        { path: 'words', component: TrainingWordsView },
        { path: 'verbs', component: TrainingVerbsView }
    ]
}

