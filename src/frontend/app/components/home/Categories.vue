<template>
    <section class="categories_area">
        <div class="container">
            <div class="row">
                <div
                    v-for="cat in categories"
                    :key="cat.title"
                    class="col-lg-4"
                >
                    <img :src="cat.image"  alt=""/>
                    <h5>{{ cat.title }}</h5>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
const categories = [
    { title: 'Food', image: '/img/cat-1.jpg' },
    { title: 'Cooking', image: '/img/cat-2.jpg' },
    { title: 'Lifestyle', image: '/img/cat-3.jpg' }
]
</script>
