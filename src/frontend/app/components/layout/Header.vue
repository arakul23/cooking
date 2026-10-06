<template>
    <header class="header_area">
        <div class="container">
            <div class="row">
                <!-- Logo Area Start -->
                <div class="col-12">
                    <div class="row align-items-center">
                        <div class="col-md-6 col-12">
                            <div class="logo_area text-md-left text-center">
                                <NuxtLink to="/" class="yummy-logo">
                                    <img alt="logo" src="@assets/images/logo_ua.svg" />
                                </NuxtLink>
                            </div>
                        </div>
                        <div class="col-md-6 col-12 d-flex justify-content-md-end justify-content-center">
                            <div class="header-select">
                                <USelect
                                    v-model="value"
                                    :items="languages"
                                    class="header-language-select"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <form class="header-search" role="search" @submit.prevent="submitSearch">
                        <div class="header-search-controls">
                            <label class="header-search-input">
                                <span class="sr-only">{{ $t('search_placeholder') }}</span>
                                <input
                                    v-model="searchText"
                                    type="search"
                                    :placeholder="$t('search_placeholder')"
                                    :aria-label="$t('search_placeholder')"
                                />
                            </label>
                            <button
                                class="header-search-submit"
                                type="submit"
                                :disabled="!searchText.trim() || (!searchCategories && !searchRecipes)"
                            >
                                {{ $t('search_button') }}
                            </button>
                        </div>
                        <div class="header-search-types">
                            <label>
                                <input v-model="searchCategories" type="checkbox" />
                                <span>{{ $t('search_categories') }}</span>
                            </label>
                            <label>
                                <input v-model="searchRecipes" type="checkbox" />
                                <span>{{ $t('search_recipes') }}</span>
                            </label>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <nav class="navbar navbar-expand-lg">
                        <button
                            class="navbar-toggler"
                            type="button"
                            aria-controls="yummyfood-nav"
                            :aria-expanded="mobileMenuOpen"
                            aria-label="Toggle navigation"
                            @click="mobileMenuOpen = !mobileMenuOpen"
                        >
                            <i class="fa fa-bars" aria-hidden="true"></i> Menu
                        </button>
                        <!-- Menu Area Start -->
                        <div
                            id="yummyfood-nav"
                            class="navbar-collapse justify-content-center"
                            :class="{ 'is-open': mobileMenuOpen }"
                        >
                            <ul class="navbar-nav" id="yummy-nav">
                                <li class="nav-item" :class="{ active: isActiveTab('/') }">
                                    <NuxtLink class="nav-link" to="/">{{ $t('menu_home') }}</NuxtLink>
                                </li>
                                <li class="nav-item" :class="{ active: isActiveTab('/categories') || route.path === '/category' }">
                                    <NuxtLink class="nav-link" to="/categories">{{ $t('menu_categories') }}</NuxtLink>
                                </li>
                                <li class="nav-item" :class="{ active: isActiveTab('/contact') }">
                                    <NuxtLink to="/contact" class="nav-link">{{ $t('menu_contact') }}</NuxtLink>
                                </li>
                                <li v-if="!isAuthenticated" class="nav-item" :class="{ active: isActiveTab('/login') }">
                                    <NuxtLink to="/login" class="nav-link">{{ $t('menu_login') }}</NuxtLink>
                                </li>
                                <li v-if="!isAuthenticated" class="nav-item" :class="{ active: isActiveTab('/register') }">
                                    <NuxtLink to="/register" class="nav-link">{{ $t('menu_register') }}</NuxtLink>
                                </li>
                                <li v-if="isAuthenticated" class="nav-item" :class="{ active: isAccountActive() }">
                                    <NuxtLink to="/account" class="nav-link">{{ $t('menu_profile') }}</NuxtLink>
                                </li>
                                <li v-if="isAuthenticated" class="nav-item">
                                    <button class="nav-link auth-nav-button" type="button" @click="handleLogout">
                                        {{ $t('menu_logout') }}
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup lang="ts">
import { useAuth } from '../../composables/useAuth'

const route = useRoute();
const { locale, setLocale } = useI18n();
const { isAuthenticated, logout } = useAuth()
const searchText = ref(Array.isArray(route.query.q) ? route.query.q[0] ?? '' : route.query.q ?? '')
const searchCategories = ref(route.query.categories !== '0')
const searchRecipes = ref(route.query.recipes !== '0')
const mobileMenuOpen = ref(false)

const languages = ref([
    { label: 'English', value: 'en' },
    { label: 'Українська', value: 'uk' },
]);
const value = ref(locale.value)

watch(value, async(newLang) => {
    if (typeof newLang === 'string') {
        await setLocale(newLang)
    }
});

watch(() => route.query.q, (query) => {
    searchText.value = Array.isArray(query) ? query[0] ?? '' : query ?? ''
})
watch(() => route.query.categories, (enabled) => {
    searchCategories.value = enabled !== '0'
})
watch(() => route.query.recipes, (enabled) => {
    searchRecipes.value = enabled !== '0'
})
watch(() => route.path, () => {
    mobileMenuOpen.value = false
})

const isActiveTab = (tabName: string) => {
    return route.path === tabName;
}

const isAccountActive = () => {
    return route.path.startsWith('/account')
}

const handleLogout = async () => {
    await logout()
    await navigateTo('/')
}

const submitSearch = async () => {
    const query = searchText.value.trim()

    if (!query || (!searchCategories.value && !searchRecipes.value)) {
        return
    }

    await navigateTo({
        path: '/search',
        query: {
            q: query,
            categories: searchCategories.value ? '1' : '0',
            recipes: searchRecipes.value ? '1' : '0',
        },
    })
}
</script>

<style scoped>
.logo_area {
    display: flex;
    align-items: center;
    justify-content: center;
}
.logo_area .yummy-logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 16px 0;
        margin-bottom: 0;
        text-decoration: none;
}

.logo_area .yummy-logo img {
        width: clamp(120px, 18vw, 360px);
        max-width: 100%;
        height: auto;
        display: block;
}

@media (max-width: 480px) {
    .logo_area .yummy-logo {
        padding: 10px 0;
    }
    .logo_area .yummy-logo img {
        width: 120px;
    }
}

.auth-nav-button {
        border: 0;
        background: transparent;
        cursor: pointer;
}

.header-select {
    width: min(100%, 280px);
    min-width: 0;
    margin: 8px 10px 14px;
}

.navbar-toggler {
    display: none;
}

.header-language-select {
    width: 100%;
}

.header-select :deep(button) {
    box-sizing: border-box;
    width: 100%;
    min-height: 44px;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 14px;
    border: 1px solid #e5e5e5;
    border-radius: 6px;
    background: #fff;
    color: #51545f;
    text-align: left;
}

.header-select :deep(button:hover) {
    border-color: #fc6c3f;
}

.header-select :deep(button:focus-visible) {
    border-color: #fc6c3f;
    outline: 2px solid rgb(252 108 63 / 18%);
    outline-offset: 1px;
}

@media (max-width: 767px) {
    .header-select {
        width: min(100%, 280px);
        margin: 0 auto 14px;
    }
}

@media (max-width: 991px) {
    .navbar-toggler {
        display: block;
        width: 100%;
        margin: 8px 0;
        padding: 11px 16px;
        border: 1px solid #fc6c3f;
        border-radius: 4px;
        background: #fc6c3f;
        color: #fff;
        font: inherit;
        text-align: left;
    }

    .navbar-collapse {
        display: none;
        width: 100%;
    }

    .navbar-collapse.is-open {
        display: block;
    }

    .navbar-nav {
        align-items: stretch;
    }

    .navbar.navbar-expand-lg .navbar-nav .nav-link {
        padding: 12px 16px;
    }
}

@media (min-width: 992px) {
    .navbar-collapse {
        display: flex !important;
    }
}

.header-search {
    max-width: 660px;
    margin: 0 auto 18px;
}

.header-search-controls {
    display: flex;
    gap: 8px;
}

.header-search-input {
    flex: 1;
    min-width: 0;
    margin: 0;
}

.header-search-input input {
    width: 100%;
    min-height: 42px;
    padding: 9px 14px;
    border: 1px solid #e5e5e5;
    border-radius: 4px;
    color: #232d37;
    font: inherit;
}

.header-search-input input:focus {
    border-color: #fc6c3f;
    outline: 2px solid rgb(252 108 63 / 18%);
}

.header-search-submit {
    min-height: 42px;
    padding: 9px 20px;
    border: 1px solid #fc6c3f;
    border-radius: 4px;
    background: #fc6c3f;
    color: #fff;
    font-weight: 700;
    cursor: pointer;
}

.header-search-submit:disabled {
    cursor: not-allowed;
    opacity: 0.55;
}

.header-search-types {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px 22px;
    margin-top: 9px;
}

.header-search-types label {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    margin: 0;
    color: #727272;
    font-size: 13px;
    cursor: pointer;
}

.header-search-types input {
    accent-color: #fc6c3f;
}

@media (max-width: 480px) {
    .header-search-submit {
        padding-right: 14px;
        padding-left: 14px;
    }
}
</style>
