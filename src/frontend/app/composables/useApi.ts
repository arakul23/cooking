type ApiOptions = Parameters<typeof $fetch>[1]

const csrfMethods = new Set(['POST', 'PUT', 'PATCH', 'DELETE'])

const getXsrfToken = (cookieToken?: string | null) => {
    if (cookieToken) {
        return cookieToken
    }

    if (import.meta.client) {
        const token = document.cookie
            .split('; ')
            .find((row) => row.startsWith('XSRF-TOKEN='))
            ?.split('=')[1]

        return token ?? null
    }

    return null
}

export const useApi = () => {
    const requestHeaders = import.meta.server ? useRequestHeaders(['cookie']) : {}
    const xsrfCookie = useCookie<string | null>('XSRF-TOKEN')
    const user = useState('auth:user', () => null)
    const initialized = useState('auth:initialized', () => false)

    return async <T>(url: string, options: ApiOptions = {}) => {
        const method = String(options.method ?? 'GET').toUpperCase()
        const headers = new Headers(options.headers as HeadersInit)

        headers.set('Accept', 'application/json')
        headers.set('X-Requested-With', 'XMLHttpRequest')

        if (import.meta.server && requestHeaders.cookie) {
            headers.set('cookie', requestHeaders.cookie)
        }

        const xsrfToken = csrfMethods.has(method) ? getXsrfToken(xsrfCookie.value) : null

        if (xsrfToken) {
            headers.set('X-XSRF-TOKEN', decodeURIComponent(xsrfToken))
        }

        try {
            return await $fetch<T>(url, {
                ...options,
                credentials: 'include',
                headers,
            })
        } catch (error: any) {
            const status = error?.status ?? error?.statusCode ?? error?.response?.status

            if (status === 401) {
                user.value = null
                initialized.value = true
            }

            throw error
        }
    }
}
