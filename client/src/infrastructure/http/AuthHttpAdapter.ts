import type { AuthPort } from '@/domain/port/out/AuthPort'
import { httpClient } from '@/infrastructure/http/HttpClient'

const api: string = `${import.meta.env.VITE_BACKEND_URL}`

export class AuthHttpAdapter implements AuthPort {
    async login(username: string, password: string): Promise<any> {
        return httpClient.post(api + '/user', { username, password })
    }

    async register(data: {
        username: string
        firstName: string
        lastName: string
        password: string
    }): Promise<void> {
        await httpClient.post(api + '/user/new', data)
    }
}

