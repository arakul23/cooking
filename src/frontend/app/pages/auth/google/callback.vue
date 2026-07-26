<script setup lang="ts">
const route = useRoute()
const { loginWithGoogle } = useAuth()

const code = route.query.code as string | undefined

try {
    if (!code) {
        throw new Error('Missing authorization code')
    }

    await loginWithGoogle(code)
    await navigateTo('/', { replace: true })
} catch (error) {
    console.error('Google auth failed:', error)
    await navigateTo('/login?error=google_auth_failed', { replace: true })
}
</script>

<template>
    <p>Signing you in…</p>
</template>
