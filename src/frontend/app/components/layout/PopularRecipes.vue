<script setup lang="ts">
const config = useRuntimeConfig();

type RecipeItem = {
    id: number;
    title: string;
    description: string;
    image: string;
    createdAt: string;
    views: number
}

const { data, pending, error } = await useFetch<RecipeItem[]>(
    `${config.public.apiBase}/recipes/popular`, {
        server: false
    }
)


const recipes = computed(() => data.value ?? [])
console.log(recipes);
</script>

<template>
    <div class="single-widget-area popular-post-widget">
        <div class="widget-title text-center">
            <h6>Popular Posts</h6>
        </div>
        <!-- Single Popular Post -->
        <div class="single-populer-post d-flex">
            <img src='https://placehold.co/1200x800?text=Temp+Image' alt=''>
            <div class="post-content">
                <a href="#">
                    <h6>Top Wineries To Visit In England</h6>
                </a>
                <p>Tuesday, October 3, 2017</p>
            </div>
        </div>
        <div class="single-populer-post d-flex">
            <img src='https://placehold.co/1200x800?text=Temp+Image' alt=''>
            <div class="post-content">
                <a href="#">
                    <h6>The 8 Best Gastro Pubs In Bath</h6>
                </a>
                <p>Tuesday, October 3, 2017</p>
            </div>
        </div>
        <div class="single-populer-post d-flex">
            <img src='https://placehold.co/1200x800?text=Temp+Image' alt=''>
            <div class="post-content">
                <a href="#">
                    <h6>Zermatt Unplugged the best festival</h6>
                </a>
                <p>Tuesday, October 3, 2017</p>
            </div>
        </div>
        <div class="single-populer-post d-flex">
            <img src='https://placehold.co/1200x800?text=Temp+Image' alt=''>
            <div class="post-content">
                <a href="#">
                    <h6>Harrogate's Top 10 Independent Eats</h6>
                </a>
                <p>Tuesday, October 3, 2017</p>
            </div>
        </div>
        <div class="single-populer-post d-flex">
            <img src='https://placehold.co/1200x800?text=Temp+Image' alt=''>
            <div class="post-content">
                <a href="#">
                    <h6>Eating Out On A Budget In Oxford</h6>
                </a>
                <p>Tuesday, October 3, 2017</p>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
