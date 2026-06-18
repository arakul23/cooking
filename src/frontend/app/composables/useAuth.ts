import { useApi } from './useApi'

export type AuthUser = {
    id: number
    name: string
    email: string
    email_verified_at?: string | null
}

export type LoginPayload = {
    email: string
    password: string
    remember?: boolean
}

export type RegisterPayload = {
    name: string
    email: string
    password: string
    password_confirmation: string
}

const fetchCsrfCookie = async () => {
    await $fetch.raw('/api/auth/sanctum/csrf-cookie', {
        credentials: 'include',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })

    if (import.meta.client) {
        await new Promise((resolve) => window.setTimeout(resolve, 0))
    }
}

export const useAuth = () => {
    const user = useState<AuthUser | null>('auth:user', () => null)
    const initialized = useState('auth:initialized', () => false)
    const isLoading = useState('auth:loading', () => false)
    const api = useApi()

    const isAuthenticated = computed(() => Boolean(user.value))

    const fetchUser = async (force = false) => {
        if (initialized.value && !force) {
            return user.value
        }

        isLoading.value = true

        try {
            user.value = await api<AuthUser>('/api/user')
        } catch (error: any) {
            const status = error?.status ?? error?.statusCode ?? error?.response?.status

            if (status === 401 || status === 419) {
                user.value = null
            } else {
                throw error
            }
        } finally {
            initialized.value = true
            isLoading.value = false
        }

        return user.value
    }

    const register = async (payload: RegisterPayload) => {
        await fetchCsrfCookie()
        await api<void>('/api/auth/register', {
            method: 'POST',
            body: payload,
        })

        return await fetchUser(true)
    }

    const login = async (payload: LoginPayload) => {
        await fetchCsrfCookie()
        await api<void>('/api/auth/login', {
            method: 'POST',
            body: payload,
        })

        return await fetchUser(true)
    }

    const logout = async () => {
        try {
            await fetchCsrfCookie()
            await api<void>('/api/auth/logout', {
                method: 'POST',
            })
        } finally {
            user.value = null
            initialized.value = true
        }
    }

    return {
        user,
        initialized,
        isLoading,
        isAuthenticated,
        fetchUser,
        register,
        login,
        logout,
    }
}
