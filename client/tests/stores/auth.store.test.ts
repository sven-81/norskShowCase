import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useAlertStore, useAuthStore } from '@/stores'
import { router } from '@/router'
import sinon from 'sinon'
import { fetchWrapper } from '@/request'

vi.mock('jwt-decode', () => ({
    jwtDecode: () => ({scope: 'user:read'}) // Mocked JWT payload
}))

describe('AuthStore login test', () => {
    let store
    let username = 'some-username'
    let password = 'some-password'

    beforeEach(() => {
        setActivePinia(createPinia())
        store = useAuthStore()
        localStorage.clear()
    })

    afterEach(() => {
        sinon.restore()
        vi.restoreAllMocks()
    })

    it('should log user in', async () => {
        const routerMock = sinon.stub(router, 'push')
        const localStorageMock = vi.spyOn(Storage.prototype, 'setItem')
        const fetchWrapperPostStub = sinon.stub(fetchWrapper, 'post')

        const user = {
            login: true,
            username,
            firstName: 'firstName',
            lastName: 'lastName',
            token: 'fake-jwt-token'
        }

        fetchWrapperPostStub.resolves(user)

        await store.login(username, password)

        expect(localStorageMock).toHaveBeenCalledTimes(1)
        expect(localStorageMock).toHaveBeenCalledWith(
            'user',
            JSON.stringify({...user, scope: 'user:read'})
        )
        expect(store.user).toStrictEqual({...user, scope: 'user:read'})

        sinon.assert.calledOnceWithExactly(
            fetchWrapperPostStub,
            `${import.meta.env.VITE_BACKEND_URL}/user`,
            {username, password}
        )
        sinon.assert.calledOnceWithExactly(routerMock, '/')
    })

    it('should handle error when fetching user data', async () => {
        const error = {status: 401, message: 'Unauthorized'}
        sinon.stub(fetchWrapper, 'post').rejects(error)

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
    })

    afterEach(() => {
        sinon.restore()
        vi.restoreAllMocks()
    })

    it('should log user out', () => {
        const routerMock = sinon.stub(router, 'push')
        const localStorageMock = vi.spyOn(Storage.prototype, 'removeItem')

        store.user = {
            login: true,
            username: 'username',
            firstName: 'firstName',
            lastName: 'lastName',
            token: 'fake-jwt-token'
        }

        localStorage.setItem('user', JSON.stringify(store.user))

        store.logout()

        expect(localStorageMock).toHaveBeenCalledTimes(1)
        expect(localStorageMock).toHaveBeenCalledWith('user')
        expect(store.user).toBeNull()
        sinon.assert.calledOnceWithExactly(routerMock, '/login')
    })
})
