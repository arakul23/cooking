import { parseCookies, proxyRequest } from 'h3'

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

export default defineEventHandler(async (event) => {
    const config = useRuntimeConfig(event)
    const rawPath = event.context.params?.path
    const path = Array.isArray(rawPath) ? rawPath.join('/') : (rawPath || '')
    const base = config.apiInternalBase.replace(/\/+$/, '')
    const target = `${base}/${path}`
    const xsrfToken = decodeCookieValue(parseCookies(event)['XSRF-TOKEN'])
    const headers: Record<string, string> = {
        accept: 'application/json',
        'x-requested-with': 'XMLHttpRequest',
    }

    if (xsrfToken) {
        headers['x-xsrf-token'] = xsrfToken
    }

    return proxyRequest(event, target, {
        headers,
    })
})
