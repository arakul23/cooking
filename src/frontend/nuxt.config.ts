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
    ],
    srcDir: 'src/frontend/',
    runtimeConfig: {
        public: {
            apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8080/api'
        }
    }
})

