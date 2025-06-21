import { describe, it, expect, beforeEach, vi } from 'vitest'
import { routerGuard } from '@/router/index'
import * as stores from '@/stores'

describe('routerGuard', () => {
    let authStoreMock: any
    let alertStoreMock: any

    beforeEach(() => {
        authStoreMock = {
            user: null,
            returnUrl: ''
        }
        alertStoreMock = {
            clear: vi.fn()
        }

        vi.spyOn(stores, 'useAuthStore').mockReturnValue(authStoreMock)
        vi.spyOn(stores, 'useAlertStore').mockReturnValue(alertStoreMock)
    })

    it('clears old alerts on each navigation', async () => {
        const to = { path: '/', fullPath: '/' }
        const result = await routerGuard(to)
        expect(alertStoreMock.clear).toHaveBeenCalled()
        expect(result).toBe(true)
    })

    it('redirects to login if auth required and user not logged in', async () => {
        const to = { path: '/protected', fullPath: '/protected' }
        const result = await routerGuard(to)
        expect(authStoreMock.returnUrl).toBe('/protected')
        expect(result).toBe('/login')
    })

    it('allows navigation if user is logged in', async () => {
        authStoreMock.user = { id: 1 }
        const to = { path: '/protected', fullPath: '/protected' }
        const result = await routerGuard(to)
        expect(result).toBe(true)
    })
})
