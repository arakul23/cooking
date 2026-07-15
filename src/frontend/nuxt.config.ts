export default defineNuxtConfig({
    app: {
        head: {
            script: [

            ]
        }
    },
    compatibilityDate: '2025-07-15',
    devtools: {enabled: true},
    vite: {
        resolve: {
            alias: {
                '@': '/var/www/html/frontend',
            },
        },
        server: {
            watch: {
                usePolling: true,
                interval: 100
            }
        }
    },

    css: [
        '@/assets/css/style.css',
        '@/assets/css/main.css'
    ],
    srcDir: 'app/',
    modules: ['@nuxt/ui'],
    runtimeConfig: {
        apiInternalBase: process.env.NUXT_API_INTERNAL_BASE || 'http://nginx/api',
        public: {
            apiBase: process.env.NUXT_PUBLIC_API_BASE || '/api'
        }
    }
})
