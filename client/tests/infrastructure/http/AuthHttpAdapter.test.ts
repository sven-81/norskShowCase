import { afterEach, describe, expect, it, vi } from 'vitest'
import { AuthHttpAdapter } from '@/infrastructure/http/AuthHttpAdapter'
import { httpClient } from '@/infrastructure/http/HttpClient'

vi.mock('@/infrastructure/http/HttpClient', () => ({
    httpClient: {
        post: vi.fn()
    }
}))

describe('AuthHttpAdapter', () => {
    const adapter = new AuthHttpAdapter()

    afterEach(() => {
        vi.clearAllMocks()
    })

    it('calls httpClient.post with correct URL and credentials on login', async () => {
        const responseData = { token: 'jwt-token', username: 'sven' }
        vi.mocked(httpClient.post).mockResolvedValue(responseData)

        const result = await adapter.login('sven', 'geheim123')

        expect(httpClient.post).toHaveBeenCalledWith(
            expect.stringContaining('/user'),
            { username: 'sven', password: 'geheim123' }
        )
        expect(result).toEqual(responseData)
    })

    it('calls httpClient.post with correct URL and data on register', async () => {
        vi.mocked(httpClient.post).mockResolvedValue(undefined)

        const registrationData = {
            username: 'klaus',
            firstName: 'Klaus',
            lastName: 'Müller',
            password: 'sicheres-passwort'
        }

        await adapter.register(registrationData)

        expect(httpClient.post).toHaveBeenCalledWith(
            expect.stringContaining('/user/new'),
            registrationData
        )
    })
})

