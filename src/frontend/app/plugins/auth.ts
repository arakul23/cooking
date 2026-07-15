import { useAuth } from '~/composables/useAuth'

export default defineNuxtPlugin(async () => {
    const { fetchUser } = useAuth()

    try {
        await fetchUser()
    } catch (error) {
        console.error('Unable to initialize auth state:', error)
    }
})
