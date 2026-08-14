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
                                <USelect v-model="value" :items="items" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <nav class="navbar navbar-expand-lg">
                        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#yummyfood-nav" aria-controls="yummyfood-nav" aria-expanded="false" aria-label="Toggle navigation"><i class="fa fa-bars" aria-hidden="true"></i> Menu</button>
                        <!-- Menu Area Start -->
                        <div class="navbar-collapse justify-content-center" id="yummyfood-nav">
                            <ul class="navbar-nav" id="yummy-nav">
                                <li class="nav-item" :class="{ active: isActiveTab('/') }">
                                    <NuxtLink class="nav-link" to="/">{{ $t('menu_home') }}</NuxtLink>
                                </li>
                                <li class="nav-item"  :class="{ active: isActiveTab('/categories') }">
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

const items = ref([
    { label: 'English', value: 'en' },
    { label: 'Українська', value: 'uk' },
]);
const value = ref(locale.value)

watch(value, async(newLang) => {
    if (typeof newLang === 'string') {
        await setLocale(newLang)
    }
});

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
</script>

<style scoped>
/* Переопределяем стили родительского контейнера */
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

/* Настраиваем ширину самой картинки: responsive */
.logo_area .yummy-logo img {
        width: clamp(120px, 18vw, 360px);
        max-width: 100%;
        height: auto;
        display: block;
}

/* Уменьшаем логотип на очень маленьких экранах */
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
</style>

<style scoped>
/* Styles for the USelect wrapper to fit design */
.header-select {
    width: 220px;
    max-width: 45%;
    min-width: 140px;
    margin: 0 10px;
}

@media (max-width: 768px) {
    .header-select {
        max-width: 60%;
        width: 180px;
    }
}

@media (max-width: 480px) {
    .header-select {
        width: 140px;
    }
}
</style>
