<template>
    <Preloader />
    <Header />

    <div class="breadcumb-area">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-12">
                    <div class="bradcumb-title text-center">
                        <h2>{{ $t('account') }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="breadcumb-nav">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <NuxtLink to="/">
                                    <i class="fa fa-home" aria-hidden="true"></i>
                                    Home
                                </NuxtLink>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ title }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <section class="account-area section_padding_80">
        <div class="container">
            <div class="row">
                <div class="col-12 col-lg-4">
                    <aside class="account-sidebar">
                        <div class="account-user">
                            <h3>{{ user?.name || 'User' }}</h3>
                            <p>{{ user?.email }}</p>
                        </div>

                        <nav class="account-menu" aria-label="Account navigation">
                            <NuxtLink
                                to="/account"
                                class="account-menu-link"
                                :class="{ active: route.path === '/account' }"
                            >
                                Главная
                            </NuxtLink>
                            <NuxtLink
                                to="/account/favorites"
                                class="account-menu-link"
                                :class="{ active: route.path === '/account/favorites' }"
                            >
                                Любимые рецепты
                            </NuxtLink>
                            <NuxtLink
                                to="/account/create-recipe"
                                class="account-menu-link"
                                :class="{ active: route.path === '/account/create-recipe' }"
                            >
                                Добавить рецепт
                            </NuxtLink>
                        </nav>
                    </aside>
                </div>

                <div class="col-12 col-lg-8">
                    <main class="account-content">
                        <slot />
                    </main>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>

<script setup lang="ts">
import Preloader from '@/app/components/layout/Preloader.vue'
import Header from '@/app/components/layout/Header.vue'
import Footer from '@/app/components/layout/Footer.vue'
import { useAuth } from '@/app/composables/useAuth'

defineProps<{
    title: string
}>()

const route = useRoute()
const { user } = useAuth()
</script>

<style scoped>
@import '@assets/css/account/style.css';
</style>
