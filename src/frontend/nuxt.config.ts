// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
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
        '@/assets/css/bootstrap.min.css',
        '@/assets/css/font-awesome.min.css',
        '@/assets/css/magnific-popup.css',
        '@/assets/css/meanmenu.min.css',
        '@/assets/css/owl.carousel.min.css',
        '@/assets/css/pe-icon-7-stroke.css',
        '@/assets/css/responsive.css',
    ],

    runtimeConfig: {
        public: {
            // А здесь то, что будет доступно и на клиенте, и на сервере
            apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8080/api'
        }
    }
})
