import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { httpClient, registerAuthProvider } from '@/infrastructure/http/HttpClient'

function createMockResponse(options: {
    ok?: boolean
    status?: number
    statusText?: string
    json?: any
    contentType?: string
}): Response {
    const {
        ok = true,
        status = 200,
        statusText = 'OK',
        json = null,
        contentType = 'application/json'
    } = options

    return {
        ok,
        status,
        statusText,
        headers: {
            get: (name: string) => {
                if (name === 'content-type') {
                    return contentType
                }
                return null
            }
        },
        json: () => Promise.resolve(json)
    } as unknown as Response
}

describe('HttpClient', () => {
    let fetchMock: ReturnType<typeof vi.fn>

    beforeEach(() => {
        fetchMock = vi.fn()
        vi.stubGlobal('fetch', fetchMock)
    })

    afterEach(() => {
        vi.restoreAllMocks()
    })

    it('sends GET request with correct method', async () => {
        fetchMock.mockResolvedValue(createMockResponse({ json: { id: 1 } }))

        await httpClient.get('http://api/test')

        expect(fetchMock).toHaveBeenCalledWith(
            'http://api/test',
            expect.objectContaining({ method: 'GET' })
        )
    })

    it('sends POST request with body', async () => {
        fetchMock.mockResolvedValue(createMockResponse({ json: {} }))
        const body = { username: 'test', password: 'pw' }

        await httpClient.post('http://api/test', body)

        expect(fetchMock).toHaveBeenCalledWith(
            'http://api/test',
            expect.objectContaining({
                method: 'POST',
                body: JSON.stringify(body)
            })
        )
    })

    it('sends PUT request with body', async () => {
        fetchMock.mockResolvedValue(createMockResponse({ json: {} }))
        const body = { german: 'Hund', norsk: 'hund' }

        await httpClient.put('http://api/test/1', body)

        expect(fetchMock).toHaveBeenCalledWith(
            'http://api/test/1',
            expect.objectContaining({
                method: 'PUT',
                body: JSON.stringify(body)
            })
        )
    })

    it('sends PATCH request', async () => {
        fetchMock.mockResolvedValue(createMockResponse({ json: {} }))

        await httpClient.patch('http://api/test/1')

        expect(fetchMock).toHaveBeenCalledWith(
            'http://api/test/1',
            expect.objectContaining({ method: 'PATCH' })
        )
    })

    it('sends DELETE request', async () => {
        fetchMock.mockResolvedValue(createMockResponse({ json: {} }))

        await httpClient.delete('http://api/test/1')

        expect(fetchMock).toHaveBeenCalledWith(
            'http://api/test/1',
            expect.objectContaining({ method: 'DELETE' })
        )
    })

    it('returns parsed JSON data on success', async () => {
        const responseData = { id: 42, german: 'Katze', norsk: 'katt' }
        fetchMock.mockResolvedValue(createMockResponse({ json: responseData }))

        const result = await httpClient.get('http://api/test')

        expect(result).toEqual(responseData)
    })

    it('returns null when response has no JSON content type', async () => {
        fetchMock.mockResolvedValue(createMockResponse({ contentType: 'text/plain' }))

        const result = await httpClient.get('http://api/test')

        expect(result).toBeNull()
    })

    it('throws error with message from response on failure', async () => {
        fetchMock.mockResolvedValue(createMockResponse({
            ok: false,
            status: 500,
            statusText: 'Server Error',
            json: { message: 'Interner Fehler' }
        }))

        await expect(httpClient.get('http://api/test')).rejects.toThrow('Interner Fehler')
    })

    it('throws error with statusText when no message in response', async () => {
        fetchMock.mockResolvedValue(createMockResponse({
            ok: false,
            status: 404,
            statusText: 'Not Found',
            json: {}
        }))

        await expect(httpClient.get('http://api/test')).rejects.toThrow('Not Found')
    })

    it('calls authProvider.logout on 401 response', async () => {
        const logoutMock = vi.fn()
        registerAuthProvider({
            getToken: () => 'token',
            logout: logoutMock
        })

        fetchMock.mockResolvedValue(createMockResponse({
            ok: false,
            status: 401,
            statusText: 'Unauthorized',
            json: { message: 'Not allowed' }
        }))

        await expect(httpClient.get('http://api/test')).rejects.toThrow()
        expect(logoutMock).toHaveBeenCalled()
    })

    it('calls authProvider.logout on 403 response', async () => {
        const logoutMock = vi.fn()
        registerAuthProvider({
            getToken: () => 'token',
            logout: logoutMock
        })

        fetchMock.mockResolvedValue(createMockResponse({
            ok: false,
            status: 403,
            statusText: 'Forbidden',
            json: { message: 'Forbidden' }
        }))

        await expect(httpClient.get('http://api/test')).rejects.toThrow()
        expect(logoutMock).toHaveBeenCalled()
    })

    it('uses bearer token from authProvider when available', async () => {
        registerAuthProvider({
            getToken: () => 'my-jwt-token',
            logout: vi.fn()
        })

        fetchMock.mockResolvedValue(createMockResponse({ json: {} }))

        await httpClient.get('http://api/test')

        const requestInit = fetchMock.mock.calls[0][1]
        expect(requestInit.headers['Authorization']).toBe('Bearer my-jwt-token')
    })

    it('includes credentials in request', async () => {
        fetchMock.mockResolvedValue(createMockResponse({ json: {} }))

        await httpClient.get('http://api/test')

        const requestInit = fetchMock.mock.calls[0][1]
        expect(requestInit.credentials).toBe('include')
    })
})

