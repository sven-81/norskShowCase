import { useAuthStore } from '@/stores'
import { getHeaders } from '@/request/RequestHeaders'

function getApiToken(): string {
  return import.meta.env.VITE_API_TOKEN
}

export const fetchWrapper = {
  get: request('GET'),
  post: request('POST'),
  put: request('PUT'),
  patch: request('PATCH'),
  delete: request('DELETE')
}

function request(method): Response {
  return (url, body) => {
    let bearerToken: string = getBearerToken()
    let headers = getHeaders(bearerToken)

    const requestOptions = {
      method,
      credentials: 'include',
      headers
    }

    if (body) {
      requestOptions.headers['Content-Type'] = 'application/json'
      requestOptions.body = JSON.stringify(body)
    }

    return fetch(url, requestOptions).then(handleResponse)
  }
}

function getBearerToken() {
  // return auth header with jwt if user is logged in
  const { user } = useAuthStore()
  if (user !== null && user.token) {
    return 'Bearer ' + user.token
  } else {
    return 'Bearer ' + getApiToken()
  }
}

async function handleResponse(response: Response) {
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
    const { user, logout } = useAuthStore()
    if ([401, 403].includes(response.status) && user) {
      logout()
    }

    const error = new Error((data && data.message) || response.statusText || 'Fehler')
    error.status = response.status
    throw error
  }

  return data
}
