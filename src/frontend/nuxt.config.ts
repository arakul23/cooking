// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

    vite: {
        server: {
            watch: {
                usePolling: true,
                interval: 100
            }
        }
    },

    runtimeConfig: {
        public: {
            // А здесь то, что будет доступно и на клиенте, и на сервере
            apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8080/api'
        }
    }
})
