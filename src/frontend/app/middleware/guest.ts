import { useAuth } from '~/composables/useAuth'

export default defineNuxtRouteMiddleware(async () => {
    const { fetchUser, isAuthenticated } = useAuth()

    if (!isAuthenticated.value) {
        try {
            await fetchUser()
        } catch {
            return
        }
    }

    if (isAuthenticated.value) {
        return navigateTo('/')
    }
})
