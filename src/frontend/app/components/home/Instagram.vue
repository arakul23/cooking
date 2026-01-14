<template>
    <section class="instagram_area">
        <img
            v-for="(img, i) in images"
            :key="i"
            :src="img"
         alt=""/>
    </section>
</template>

<script setup>
const images = [
    '/img/insta-1.jpg',
    '/img/insta-2.jpg',
    '/img/insta-3.jpg'
]
</script>
