import { createMemoryHistory, createRouter } from 'vue-router'
import { describe, it, expect } from 'vitest'

import ManagerRoutes from '@/router/manager.routes'
import { ManageVerb, ManageWord } from '@/views/manager'

describe('Manager Routes Configuration', () => {
    const router = createRouter({
        history: createMemoryHistory(),
        routes: [{ ...ManagerRoutes }],
    });

    it('should contain /manage/words route with ManageWord component', () => {
        const route = router.getRoutes().find(r => r.path === '/manage/words');
        expect(route).toBeDefined();
        expect(route?.components?.default || route?.component).toBe(ManageWord);
    });

    it('should contain /manage/verbs route with ManageVerb component', () => {
        const route = router.getRoutes().find(r => r.path === '/manage/verbs');
        expect(route).toBeDefined();
        expect(route?.components?.default || route?.component).toBe(ManageVerb);
    });
});
