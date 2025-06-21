import { afterEach, beforeEach, describe, it } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useAlertStore, useUserStore } from '@/stores'
import { fetchWrapper } from '@/request'
import { router } from '@/router'
import sinon from 'sinon'

function simulateSuccessfulRegistration(fetchWrapperPostStub) {
  fetchWrapperPostStub.resolves({})
}

describe('UserStore', () => {
  let store
  let fetchWrapperPostStub
  let alertStoreSuccessStub
  let routerPushStub

  beforeEach(() => {
    setActivePinia(createPinia())
    store = useUserStore()

    fetchWrapperPostStub = sinon.stub(fetchWrapper, 'post')
    alertStoreSuccessStub = sinon.stub(useAlertStore(), 'success')
    routerPushStub = sinon.stub(router, 'push')
  })

  afterEach(() => {
    fetchWrapperPostStub.restore()
    alertStoreSuccessStub.restore()
    routerPushStub.restore()
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

    simulateSuccessfulRegistration(fetchWrapperPostStub)

    await store.register(user)

    sinon.assert.calledOnceWithExactly(
      fetchWrapperPostStub,
      `${import.meta.env.VITE_BACKEND_URL}/user/new`,
      user
    )
    sinon.assert.calledOnce(routerPushStub)
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

    fetchWrapperPostStub.rejects(error) // Simulate failed registration

    await store.register(user)

    sinon.assert.calledOnceWithExactly(
      fetchWrapperPostStub,
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
    simulateSuccessfulRegistration(fetchWrapperPostStub)

    await store.register(user)

    sinon.assert.calledOnce(routerPushStub)
    sinon.assert.calledWith(routerPushStub, '/login')
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

    simulateSuccessfulRegistration(fetchWrapperPostStub)

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

    simulateSuccessfulRegistration(fetchWrapperPostStub)

    await store.register(user)

    sinon.assert.calledOnceWithExactly(
      fetchWrapperPostStub,
      `${import.meta.env.VITE_BACKEND_URL}/user/new`,
      user
    )
  })
})
