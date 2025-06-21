import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { fetchWrapper } from '../../src/request'
import { createPinia, setActivePinia } from 'pinia'
import { useAuthStore } from '@/stores'

const mockJsonResponse = { ok: true, statusCode: 200, data: 'some response Data' }

function getResponseMock(status = 200, contentType = 'application/json', jsonThrows = false) {
  return {
    headers: {
      get: (header) => (header === 'content-type' ? contentType : null)
    },
    ok: status < 400,
    status,
    statusText: status === 500 ? 'Internal Server Error' : '',
    json: async () => {
      if (jsonThrows) throw new Error('Invalid JSON')
      return mockJsonResponse
    }
  }
}

describe('FetchWrapper', () => {
  let store

  beforeEach(() => {
    vi.resetAllMocks()
    vi.stubEnv('VITE_API_TOKEN', 'some.valid.token')

    setActivePinia(createPinia())
    store = useAuthStore()
  })

  afterEach(() => {
    vi.unstubAllEnvs()
  })

  describe.each([
    ['get', 'GET'],
    ['put', 'PUT'],
    ['delete', 'DELETE'],
    ['post', 'POST', { content: 'some request content' }],
    ['patch', 'PATCH', { content: 'some request content' }]
  ])('fetchWrapper.%s', (method, httpMethod, body) => {
    it(`should make a ${httpMethod} request`, async () => {
      const fetchSpy = vi
          .spyOn(globalThis, 'fetch')
          .mockResolvedValue(getResponseMock(200))

      const response = fetchWrapper[method]('fooUrl', body)

      expect(fetchSpy).toHaveBeenCalledWith(
          'fooUrl',
          expect.objectContaining({
            method: httpMethod,
            headers: expect.objectContaining({
              Authorization: expect.any(String),
              'Content-Type': 'application/json'
            }),
            ...(body && { body: JSON.stringify(body) })
          })
      )

      expect(fetchSpy).toHaveBeenCalledTimes(1)
      await expect(response).resolves.toEqual(mockJsonResponse)
    })
  })

  it('should use apiToken if user is not logged in', async () => {
    store.user = null

    const fetchSpy = vi
        .spyOn(globalThis, 'fetch')
        .mockResolvedValue(getResponseMock())

    const response = fetchWrapper.get('fooUrl')

    expect(fetchSpy).toHaveBeenCalledWith(
        'fooUrl',
        expect.objectContaining({
          headers: expect.objectContaining({
            Authorization: expect.stringContaining('Bearer some.valid.token')
          }),
          method: 'GET'
        })
    )

    await expect(response).resolves.toEqual(mockJsonResponse)
  })

  it('should logout on 401 if user is logged in', async () => {
    const logoutMock = vi.fn()
    store.user = { token: 'invalid-token' }
    store.logout = logoutMock

    const responseMock = {
      ...getResponseMock(401),
      json: async () => ({ message: 'Unauthorized' })
    }

    vi.spyOn(globalThis, 'fetch').mockResolvedValue(responseMock)

    await expect(fetchWrapper.get('fooUrl')).rejects.toMatchObject({
      message: 'Unauthorized',
      status: 401
    })

    expect(logoutMock).toHaveBeenCalled()
  })

  it('should handle invalid JSON response gracefully', async () => {
    store.user = { token: 'valid-token' }

    vi.spyOn(globalThis, 'fetch').mockResolvedValue(getResponseMock(200, 'application/json', true))

    const response = fetchWrapper.get('fooUrl')
    await expect(response).resolves.toBeNull()
  })

  it('should handle response with missing content-type', async () => {
    vi.spyOn(globalThis, 'fetch').mockResolvedValue(getResponseMock(200, null))

    const response = fetchWrapper.get('fooUrl')
    await expect(response).resolves.toBeNull()
  })

  it('should throw error with statusText when response fails without JSON message', async () => {
    const responseMock = {
      ...getResponseMock(500),
      json: async () => ({})
    }

    vi.spyOn(globalThis, 'fetch').mockResolvedValue(responseMock)

    await expect(fetchWrapper.get('fooUrl')).rejects.toMatchObject({
      message: 'Internal Server Error',
      status: 500
    })
  })
})
