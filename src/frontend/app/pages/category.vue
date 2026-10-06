<script setup lang="ts">
const route = useRoute()
const config = useRuntimeConfig()
const fallbackImage = 'https://placehold.co/640x420?text=Recipe'

const categoryId = computed(() => {
    const id = route.query.id
    return Array.isArray(id) ? id[0] : String(id ?? '')
})

type CategoryItem = {
    id: number;
    name: string;
    image: string | null;
    recipes_count?: number | null;
    recipesCount?: number | null;
    recipes?: RecipeItem[]
}

type RecipeItem = {
    id: number
    title: string
    logo: string | null
}

const {data, pending, error} = await useFetch<{ data: CategoryItem }>(
    `${config.public.apiBase}/categories/${categoryId.value}`
)

const category = computed(() => data.value?.data ?? null)
const recipes = computed(() => category.value?.recipes ?? [])
</script>

<template>
    <div class="breadcumb-area">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-12">
                    <div class="bradcumb-title text-center">
                        <h2>{{ category?.name }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="breadcumb-nav">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <NuxtLink to="/">
                                    <i class="fa fa-home" aria-hidden="true"></i>
                                    Home
                                </NuxtLink>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Categories</li>
                            <li class="breadcrumb-item active" aria-current="page">{{ category?.name }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <section class="categories_area">
        <div class="container">
            <div v-if="pending" class="text-center">Loading category..</div>
            <div v-else-if="error" class="text-center">Failed to load category</div>
            <div v-else-if="recipes.length" class="row">
                <div
                    v-for="recipe in recipes"
                    :key="recipe.id"
                    class="col-12 col-md-6 col-lg-4"
                >
                    <NuxtLink :to="{ path: '/recipe', query: { id: recipe.id } }">
                        <article class="single_catagory category-recipe-card wow fadeInUp">
                            <img :src="recipe.logo || fallbackImage" :alt="recipe.title">
                            <h5 class="category-recipe-title">{{ recipe.title }}</h5>
                        </article>
                    </NuxtLink>
                </div>
            </div>
            <div v-else class="text-center">No recipes in this category yet</div>
        </div>
    </section>
</template>

<style scoped>
.category-recipe-card {
    overflow: hidden;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 18px rgb(35 45 55 / 8%);
    transition: transform 160ms ease, box-shadow 160ms ease;
}

.category-recipe-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgb(35 45 55 / 12%);
}

.category-recipe-card img {
    width: 100%;
    aspect-ratio: 4 / 3;
    object-fit: cover;
}

.category-recipe-title {
    margin: 0;
    padding: 16px;
    color: #232d37;
    font-size: 18px;
}
</style>
