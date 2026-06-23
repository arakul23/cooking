import { getRequestHeaders, parseCookies, proxyRequest } from 'h3'

const decodeCookieValue = (value?: string) => {
    if (!value) {
        return null
    }

    try {
        return decodeURIComponent(value)
    } catch {
        return value
    }
}

const getRequestOrigin = (headers: ReturnType<typeof getRequestHeaders>) => {
    if (headers.origin) {
        return headers.origin
    }

    const host = headers['x-forwarded-host'] ?? headers.host
    const protocol = headers['x-forwarded-proto'] ?? 'http'

    return host ? `${protocol}://${host}` : null
}

export default defineEventHandler(async (event) => {
    const config = useRuntimeConfig(event)
    const requestHeaders = getRequestHeaders(event)
    const rawPath = event.context.params?.path
    const path = Array.isArray(rawPath) ? rawPath.join('/') : (rawPath || '')

    const apiBase = config.apiInternalBase.replace(/\/+$/, '')
    const appBase = apiBase.replace(/\/api$/, '')
    const target = path === 'sanctum/csrf-cookie'
        ? `${appBase}/${path}`
        : `${apiBase}/auth/${path}`
    const xsrfToken = decodeCookieValue(parseCookies(event)['XSRF-TOKEN'])
    const headers: Record<string, string> = {
        accept: 'application/json',
        'x-requested-with': 'XMLHttpRequest',
    }

    if (xsrfToken) {
        headers['x-xsrf-token'] = xsrfToken
    }

    if (requestHeaders.cookie) {
        headers.cookie = requestHeaders.cookie
    }

    const origin = getRequestOrigin(requestHeaders)

    if (origin) {
        headers.origin = origin
        headers.referer = requestHeaders.referer ?? `${origin}/`
    }

    return proxyRequest(event, target, {
        headers,
        cookieDomainRewrite: '',
        cookiePathRewrite: '/',
    })
})
