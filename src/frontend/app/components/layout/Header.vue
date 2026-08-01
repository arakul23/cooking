<template>
    <header class="header_area">
        <div class="container">
            <div class="row">
                <!-- Logo Area Start -->
                <div class="col-12">
                    <div class="logo_area text-center">
                        <NuxtLink to="/" class="yummy-logo">
                            <img alt="logo" src="@assets/images/logo_ua.svg" />
                        </NuxtLink>
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
                                    <NuxtLink class="nav-link" to="/">Home</NuxtLink>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#">Features</a>
                                </li>
                                <li class="nav-item"  :class="{ active: isActiveTab('/categories') }">
                                    <NuxtLink class="nav-link" to="/categories">Categories</NuxtLink>
                                </li>
                                <li class="nav-item">
                                    <NuxtLink to="/archive" class="nav-link">Archive</NuxtLink>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#">About</a>
                                </li>
                                <li class="nav-item" :class="{ active: isActiveTab('/contact') }">
                                    <NuxtLink to="/contact" class="nav-link">Contact</NuxtLink>
                                </li>
                                <li v-if="!isAuthenticated" class="nav-item" :class="{ active: isActiveTab('/login') }">
                                    <NuxtLink to="/login" class="nav-link">Login</NuxtLink>
                                </li>
                                <li v-if="!isAuthenticated" class="nav-item" :class="{ active: isActiveTab('/register') }">
                                    <NuxtLink to="/register" class="nav-link">Register</NuxtLink>
                                </li>
                                <li v-if="isAuthenticated" class="nav-item" :class="{ active: isAccountActive() }">
                                    <NuxtLink to="/account" class="nav-link">Личный кабинет</NuxtLink>
                                </li>
                                <li v-if="isAuthenticated" class="nav-item">
                                    <button class="nav-link auth-nav-button" type="button" @click="handleLogout">
                                        Logout
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
const { isAuthenticated, logout } = useAuth()

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
.logo_area .yummy-logo {
    display: inline-block;
    padding: 30px 0 0 0; /* Убрали нижний отступ (был 20px) */
    margin-bottom: 0;
}

/* Настраиваем ширину самой картинки */
.logo_area .yummy-logo img {
    width: 360px; /* Сделали значительно побольше и пошире */
    max-width: 100%;
    height: auto;
    display: block;
    margin: 0 auto;
}

.auth-nav-button {
    border: 0;
    background: transparent;
    cursor: pointer;
}
</style>
