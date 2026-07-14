export default defineNuxtConfig({
    app: {
        head: {
            script: [
                { src: 'assets/js/jquery/jquery-2.2.4.min.js', type: 'text/javascript', defer: true },
                { src: 'assets/js/bootstrap/popper.min.js', type: 'text/javascript', defer: true },
                { src: 'assets/js/bootstrap/bootstrap.min.js', type: 'text/javascript', defer: true },
                { src: 'assets/js/others/plugins.js', type: 'text/javascript', defer: true },
                { src: 'assets/js/active.js', type: 'text/javascript', defer: true },
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
    srcDir: 'src/frontend/',
    modules: ['@nuxt/ui'],
    runtimeConfig: {
        apiInternalBase: process.env.NUXT_API_INTERNAL_BASE || 'http://nginx/api',
        public: {
            apiBase: process.env.NUXT_PUBLIC_API_BASE || '/api'
        }
    }
})
