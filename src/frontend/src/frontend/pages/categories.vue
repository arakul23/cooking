<script setup lang="ts">
import Preloader from '@/app/components/layout/Preloader.vue'
import Header from '@/app/components/layout/Header.vue'
import Footer from '@/app/components/layout/Footer.vue'

const config = useRuntimeConfig()
const fallbackImage = 'https://placehold.co/640x420?text=Category'

type CategoryItem = {
    id: number;
    name: string;
    image: string | null;
    recipes_count?: number | null;
    recipesCount?: number | null;
}

const { data, pending, error } = await useFetch<{ data: CategoryItem[] }>(
    `${config.public.apiBase}/categories`
)

const categories = computed(() => data.value?.data ?? [])

const getRecipeCount = (category: CategoryItem) => {
    const count = category.recipes_count ?? category.recipesCount
    return typeof count === 'number' ? count : '-'
}
</script>

<template>
    <Preloader />
    <Header />

    <div class="breadcumb-area">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-12">
                    <div class="bradcumb-title text-center">
                        <h2>Categories</h2>
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
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <section class="categories_area">
        <div class="container">
            <div v-if="pending" class="text-center">Loading categories...</div>
            <div v-else-if="error" class="text-center">Failed to load categories</div>
            <div v-else-if="categories.length === 0" class="text-center">No categories yet</div>
            <div v-else class="row">
                <div
                    v-for="category in categories"
                    :key="category.id"
                    class="col-12 col-md-6 col-lg-4"
                >
                    <article class="single_catagory wow fadeInUp">
                        <img :src="category.image || fallbackImage" :alt="category.name">
                        <div class="catagory-title">
                            <a>
                                <h5>{{ category.name }}</h5>
                            </a>
                        </div>
                        <p class="category-recipes">Recipes: {{ getRecipeCount(category) }}</p>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>

<style scoped>
.category-recipes {
    margin-bottom: 0;
    text-align: center;
}
</style>
