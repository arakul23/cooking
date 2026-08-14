export default defineNuxtConfig({
    app: {
        head: {
            script: [
            ]
        }
    },
    compatibilityDate: '2025-07-15',
    devtools: { enabled: true },
    vite: {
        resolve: {
            alias: {
                '@': '/var/www/html/frontend',
                '@assets': '/var/www/html/frontend/assets'
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
    modules: ['@nuxt/ui', '@nuxtjs/i18n'],
    colorMode: {
        preference: 'light',
        fallback: 'light',
        classSuffix: ''
    },
    i18n: {
        lazy: true,
        langDir: 'locales',
        defaultLocale: 'en',
        strategy: 'no_prefix',
        locales: [
            { code: 'en', file: 'en.json', name: 'English' },
            { code: 'uk', file: 'uk.json', name: 'Українська' },
        ],
        detectBrowserLanguage: {
       useCookie: true,
       cookieKey: 'i18n_redirected',
       redirectOn: 'root',
       alwaysRedirect: false,
    },
    },
    runtimeConfig: {
        apiInternalBase: process.env.NUXT_API_INTERNAL_BASE || 'http://nginx/api',
        public: {
            apiBase: process.env.NUXT_PUBLIC_API_BASE || '/api'
        }
    }
})
