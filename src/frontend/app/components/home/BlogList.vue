<template>
    <section class="blog_area">
        <div class="container">
            <article v-for="post in posts" :key="post.title">
                <img :src="post.image"  alt=""/>
                <h2>{{ post.title }}</h2>
                <p>{{ post.excerpt }}</p>
            </article>
        </div>
    </section>
</template>

<script setup>
const posts = [
    {
        title: 'Boil The Kettle',
        excerpt: 'Big story is coming...',
        image: '/img/blog-1.jpg'
    }
]
</script>
