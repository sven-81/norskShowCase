import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { routerGuard } from '@/infrastructure/router'
import { useAuthStore } from '@/infrastructure/store/auth.store'
import { useAlertStore } from '@/infrastructure/store/alert.store'
vi.mock('@/infrastructure/store/auth.store', () => ({
    useAuthStore: vi.fn()
}))
vi.mock('@/infrastructure/store/alert.store', () => ({
    useAlertStore: vi.fn()
}))
describe('routerGuard', () => {
    let mockAuthStore: any
    let mockAlertStore: any
    beforeEach(() => {
        setActivePinia(createPinia())
        mockAlertStore = { clear: vi.fn() }
        mockAuthStore = { user: null, returnUrl: null }
        ;(useAuthStore as any).mockReturnValue(mockAuthStore)
        ;(useAlertStore as any).mockReturnValue(mockAlertStore)
    })
    it('allows access to public pages without login', async () => {
        const result = await routerGuard({ path: '/login', fullPath: '/login' })
        expect(result).toBe(true)
    })
    it('redirects to login for protected pages without user', async () => {
        const result = await routerGuard({ path: '/train/words', fullPath: '/train/words' })
        expect(result).toBe('/login')
        expect(mockAuthStore.returnUrl).toBe('/train/words')
    })
    it('allows access to protected pages with logged in user', async () => {
        mockAuthStore.user = { token: 'valid' }
        const result = await routerGuard({ path: '/train/words', fullPath: '/train/words' })
        expect(result).toBe(true)
    })
    it('clears alert store on navigation', async () => {
        await routerGuard({ path: '/', fullPath: '/' })
        expect(mockAlertStore.clear).toHaveBeenCalled()
    })
})
