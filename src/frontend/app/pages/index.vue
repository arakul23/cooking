<template>
    <Preloader />
    <Header />

    <HeroSlider />
    <Categories />
    <BlogList />

    <Footer />
</template>

<script setup>
import Preloader from '@/app/components/layout/Preloader.vue'
import Header from '@/app/components/layout/Header.vue'
import Footer from '@/app/components/layout/Footer.vue'

import HeroSlider from '@/app/components/home/HeroSlider.vue'
import Categories from '@/app/components/home/Categories.vue'
import BlogList from '@/app/components/home/BlogList.vue'
</script>
