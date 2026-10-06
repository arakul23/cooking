<template>
    <div class="site-layout">
        <Preloader />
        <Header />
        <div class="site-layout-content">
            <slot />
        </div>
        <Footer />
    </div>
</template>

<script setup lang="ts">
import Preloader from '~/components/layout/Preloader.vue'
import Header from '~/components/layout/Header.vue'
import Footer from '~/components/layout/Footer.vue'
</script>
