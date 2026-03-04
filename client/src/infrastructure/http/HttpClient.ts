import type { AuthProviderPort } from '@/domain/port/out/AuthProviderPort'
import { buildRequestHeaders } from '@/infrastructure/http/RequestHeaders'

let authProvider: AuthProviderPort | null = null

export function registerAuthProvider(provider: AuthProviderPort): void {
    authProvider = provider
}

function getApiToken(): string {
    return import.meta.env.VITE_API_TOKEN
}

function getBearerToken(): string {
    if (authProvider !== null) {
        const token = authProvider.getToken()
        if (token !== null) {
            return 'Bearer ' + token
        }
    }
    return 'Bearer ' + getApiToken()
}

function createRequest(method: string): (url: string, body?: object) => Promise<any> {
    return (url: string, body?: object) => {
        const bearerToken = getBearerToken()
        const headers = buildRequestHeaders(bearerToken)

        const requestOptions: RequestInit = {
            method,
            credentials: 'include',
            headers
        }

        if (body) {
            (requestOptions.headers as Record<string, string>)['Content-Type'] = 'application/json'
            requestOptions.body = JSON.stringify(body)
        }

        return fetch(url, requestOptions).then(handleResponse)
    }
}

async function handleResponse(response: Response): Promise<any> {
    const contentType = response.headers?.get('content-type')
    const isJson = contentType?.includes('application/json')

    let data = null

    if (isJson) {
        try {
            data = await response.json()
        } catch {
            // JSON parsing failed, leave data as null
        }
    }

    if (!response.ok) {
        if ([401, 403].includes(response.status) && authProvider !== null) {
            authProvider.logout()
        }

        const error: any = new Error((data && data.message) || response.statusText || 'Fehler')
        error.status = response.status
        throw error
    }

    return data
}

export const httpClient = {
    get: createRequest('GET'),
    post: createRequest('POST'),
    put: createRequest('PUT'),
    patch: createRequest('PATCH'),
    delete: createRequest('DELETE')
}

