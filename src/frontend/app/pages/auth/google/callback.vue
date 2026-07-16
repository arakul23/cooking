<script setup lang="ts">
const route = useRoute()
const config = useRuntimeConfig()

const { data } = await useFetch(`${config.public.apiBase}/auth/google/callback`, {
    method: 'POST',
    body: { code: route.query.code },
})
</script>

<template>

</template>

<style scoped>

</style>
