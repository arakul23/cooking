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
    { title: 'Food', image: 'https://placehold.co/640x420?text=Temp+Category' },
    { title: 'Cooking', image: 'https://placehold.co/640x420?text=Temp+Category' },
    { title: 'Lifestyle', image: 'https://placehold.co/640x420?text=Temp+Category' }
]
</script>
