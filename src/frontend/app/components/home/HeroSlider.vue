<template>
    <section class="welcome-post-sliders">
        <div
            v-for="(slide, i) in slides"
            :key="i"
            class="welcome-single-slide"
        >
            <img :src="slide.image"  alt=""/>
            <div class="project_title">
                <h5>{{ slide.title }}</h5>
            </div>
        </div>
    </section>
</template>

<script setup>
import slide1 from '@/assets/img/bg-img/slide-1.jpg';
import slide2 from '@/assets/img/bg-img/slide-2.jpg';

const slides = [
    {
        image: slide1,
        title: 'Tribute to Istanbul’s Street'
    },
    {
        image: slide2,
        title: 'Best Street Food'
    }
]
</script>
