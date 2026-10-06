<template>
    <section class="categories_area">
        <div class="container">
            <div class="row">
                <div
                    v-for="cat in categories"
                    :key="cat.title"
                    class="col-12 col-sm-6 col-lg-4"
                >
                    <article class="single_catagory home-category-card">
                        <img :src="cat.image" :alt="cat.title"/>
                        <div class="catagory-title">
                            <h5>{{ cat.title }}</h5>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
const categories = [
    { title: 'Food', image: 'https://placehold.co/640x420?text=Temp+Category' },
    { title: 'Cooking', image: 'https://placehold.co/640x420?text=Temp+Category' },
    { title: 'Lifestyle', image: 'https://placehold.co/640x420?text=Temp+Category' }
]
</script>

<style scoped>
.home-category-card img {
    width: 100%;
    aspect-ratio: 4 / 3;
    object-fit: cover;
}

.home-category-card .catagory-title h5 {
    margin: 0;
    line-height: 1.25;
}
</style>
