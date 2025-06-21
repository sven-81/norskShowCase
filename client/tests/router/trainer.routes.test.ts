import { createMemoryHistory, createRouter } from 'vue-router'
import { describe, it, expect } from 'vitest'

import TrainerRoutes from '@/router/trainer.routes'
import { TrainingWords, TrainingVerbs } from '@/views/trainer'

describe('Trainer Routes Configuration', () => {
    const router = createRouter({
        history: createMemoryHistory(),
        routes: [{ ...TrainerRoutes }],
    });

    it('should contain /train/words route with TrainingWords component', () => {
        const route = router.getRoutes().find(r => r.path === '/train/words');
        expect(route).toBeDefined();
        expect(route?.components?.default || route?.component).toBe(TrainingWords);
    });

    it('should contain /train/verbs route with TrainingVerbs component', () => {
        const route = router.getRoutes().find(r => r.path === '/train/verbs');
        expect(route).toBeDefined();
        expect(route?.components?.default || route?.component).toBe(TrainingVerbs);
    });
});
