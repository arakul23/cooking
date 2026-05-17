<script setup lang="ts">
const config = useRuntimeConfig();

type RecipeItem = {
    id: number;
    title: string;
    description: string;
    logo: string;
    created_at: string;
    views: number
}

const { data, pending, error } = await useFetch<RecipeItem[]>(
    `${config.public.apiBase}/recipes/popular`
)

const recipes = computed(() => data.value ?? [])

const formatCreatedAt = (value: string) => {
    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return value

    return new Intl.DateTimeFormat('uk-UA', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    }).format(date)
}
</script>

<template>
    <div v-if="pending" class="recipes-loading">
        <div class="yummy-load"></div>
    </div>
    <div v-else class="single-widget-area popular-post-widget">
        <div class="widget-title text-center">
            <h6>Popular Posts</h6>
        </div>
        <div v-for="(recipe, index) in recipes.data" class="single-populer-post d-flex">
            <img :src="recipe.logo" alt=''>
            <div class="post-content">
                <NuxtLink :to="{ path: '/recipe', query: { id: recipe.id } }">
                    <h6>{{ recipe.title }}</h6>
                </NuxtLink>
                <p>Views: {{recipe.views}}</p>
                <p>{{formatCreatedAt(recipe.created_at)}}</p>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
