import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useAuthStore } from '@/infrastructure/store/auth.store'
import { useAlertStore } from '@/infrastructure/store/alert.store'
import sinon from 'sinon'
import { httpClient } from '@/infrastructure/http/HttpClient'

vi.mock('jwt-decode', () => ({
    jwtDecode: () => ({ scope: 'user:read' })
}))

const routerPushMock = vi.fn()
vi.mock('@/infrastructure/router', () => ({
    router: { push: routerPushMock }
}))

describe('AuthStore login test', () => {
    let store
    const username = 'some-username'
    const password = 'some-password'

    beforeEach(() => {
        setActivePinia(createPinia())
        store = useAuthStore()
        localStorage.clear()
        routerPushMock.mockReset()
    })

    afterEach(() => {
        sinon.restore()
        vi.restoreAllMocks()
    })

    it('should log user in', async () => {
        const localStorageMock = vi.spyOn(localStorage, 'setItem')
        const httpClientPostStub = sinon.stub(httpClient, 'post')

        const user = {
            login: true,
            username,
            firstName: 'firstName',
            lastName: 'lastName',
            token: 'fake-jwt-token'
        }

        httpClientPostStub.resolves(user)

        await store.login(username, password)

        expect(localStorageMock).toHaveBeenCalledWith(
            'user',
            JSON.stringify({ ...user, scope: 'user:read' })
        )
        expect(store.user).toStrictEqual({ ...user, scope: 'user:read' })

        sinon.assert.calledOnceWithExactly(
            httpClientPostStub,
            `${import.meta.env.VITE_BACKEND_URL}/user`,
            { username, password }
        )
        expect(routerPushMock).toHaveBeenCalledWith('/')
    })

    it('should handle error when fetching user data', async () => {
        const error = { status: 401, message: 'Unauthorized' }
        sinon.stub(httpClient, 'post').rejects(error)

        const alertStore = useAlertStore()
        const alertStub = sinon.stub(alertStore, 'mapAuthError')

        await store.login(username, password)

        sinon.assert.calledOnce(alertStub)
        sinon.assert.calledWith(alertStub, error)
    })
})

describe('AuthStore logout test', () => {
    let store

    beforeEach(() => {
        setActivePinia(createPinia())
        store = useAuthStore()
        localStorage.clear()
        routerPushMock.mockReset()
    })

    afterEach(() => {
        sinon.restore()
        vi.restoreAllMocks()
    })

    it('should log user out', async () => {
        const localStorageMock = vi.spyOn(localStorage, 'removeItem')

        store.user = {
            login: true,
            username: 'username',
            firstName: 'firstName',
            lastName: 'lastName',
            token: 'fake-jwt-token'
        }

        localStorage.setItem('user', JSON.stringify(store.user))

        await store.logout()

        expect(localStorageMock).toHaveBeenCalledTimes(1)
        expect(localStorageMock).toHaveBeenCalledWith('user')
        expect(store.user).toBeNull()
        expect(routerPushMock).toHaveBeenCalledWith('/login')
    })
})

