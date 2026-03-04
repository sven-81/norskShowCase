import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useAlertStore } from '@/infrastructure/store/alert.store'
import { useUserStore } from '@/infrastructure/store/user.store'
import { httpClient } from '@/infrastructure/http/HttpClient'
import sinon from 'sinon'

const routerPushMock = vi.fn()
vi.mock('@/infrastructure/router', () => ({
    router: { push: routerPushMock }
}))

function simulateSuccessfulRegistration(httpClientPostStub) {
    httpClientPostStub.resolves({})
}

describe('UserStore', () => {
    let store
    let httpClientPostStub
    let alertStoreSuccessStub

    beforeEach(() => {
        setActivePinia(createPinia())
        store = useUserStore()
        httpClientPostStub = sinon.stub(httpClient, 'post')
        alertStoreSuccessStub = sinon.stub(useAlertStore(), 'success')
        routerPushMock.mockReset()
    })

    afterEach(() => {
        httpClientPostStub.restore()
        alertStoreSuccessStub.restore()
    })

    it('should register user and navigate to login', async () => {
        const user = {
            firstName: 'Papa',
            lastName: 'Smurf',
            username: 'papaSmurf',
            password: 'azrael'
        }

        const successMessage =
            'Registrierung erfolgreich. Du wirst informiert, wenn dein User freigeschaltet ist.'

        simulateSuccessfulRegistration(httpClientPostStub)

        await store.register(user)

        sinon.assert.calledOnceWithExactly(
            httpClientPostStub,
            `${import.meta.env.VITE_BACKEND_URL}/user/new`,
            user
        )
        expect(routerPushMock).toHaveBeenCalledWith('/login')
        sinon.assert.calledWith(alertStoreSuccessStub, successMessage)
    })

    it('should handle error during registration', async () => {
        const user = {
            firstName: 'Papa',
            lastName: 'Smurf',
            username: 'papaSmurf',
            password: 'azrael'
        }

        const error = new Error('Registration failed')
        const alertStoreMapAuthErrorStub = sinon.stub(useAlertStore(), 'mapAuthError')

        httpClientPostStub.rejects(error)

        await store.register(user)

        sinon.assert.calledOnceWithExactly(
            httpClientPostStub,
            `${import.meta.env.VITE_BACKEND_URL}/user/new`,
            user
        )
        sinon.assert.calledOnce(alertStoreMapAuthErrorStub)
    })

    it('should navigate to login on successful registration', async () => {
        const user = {
            firstName: 'Papa',
            lastName: 'Smurf',
            username: 'papaSmurf',
            password: 'azrael'
        }
        simulateSuccessfulRegistration(httpClientPostStub)

        await store.register(user)

        expect(routerPushMock).toHaveBeenCalledWith('/login')
    })

    it('should verify that alertStore.success is called on successful registration', async () => {
        const user = {
            firstName: 'Papa',
            lastName: 'Smurf',
            username: 'papaSmurf',
            password: 'azrael'
        }

        const successMessage =
            'Registrierung erfolgreich. Du wirst informiert, wenn dein User freigeschaltet ist.'

        simulateSuccessfulRegistration(httpClientPostStub)

        await store.register(user)

        sinon.assert.calledWith(alertStoreSuccessStub, successMessage)
    })

    it('should verify that the correct registration URL is used', async () => {
        const user = {
            firstName: 'Papa',
            lastName: 'Smurf',
            username: 'papaSmurf',
            password: 'azrael'
        }

        simulateSuccessfulRegistration(httpClientPostStub)

        await store.register(user)

        sinon.assert.calledOnceWithExactly(
            httpClientPostStub,
            `${import.meta.env.VITE_BACKEND_URL}/user/new`,
            user
        )
    })
})

