<script setup lang="ts">
import Preloader from '@/app/components/layout/Preloader.vue'
import Header from '@/app/components/layout/Header.vue'
import Footer from '@/app/components/layout/Footer.vue'

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
}

const {data, pending, error} = await useFetch<{ data: CategoryItem[] }>(
    `${config.public.apiBase}/categories/${categoryId.value}`
)

const category = computed(() => data.value?.data ?? [])
</script>

<template>
    <Preloader/>
    <Header/>
    <div class="breadcumb-area">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-12">
                    <div class="bradcumb-title text-center">
                        <h2>{{ category.name }}</h2>
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
                            <li class="breadcrumb-item active" aria-current="page">{{ category.name }}</li>
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
            <div v-else class="row">
                <div
                    v-for="recipe in category.recipes"
                    :key="recipe.id"
                    class="col-12 col-md-6 col-lg-4"
                >
                    <NuxtLink :to="{ path: '/recipe', query: { id: recipe.id } }">
                        <article class="single_catagory wow fadeInUp">
                            <img :src="recipe.logo || fallbackImage" :alt="recipe.title">
                            <h5>{{ recipe.title }}</h5>
                        </article>
                    </NuxtLink>
                </div>

            </div>
        </div>
    </section>

    <Footer/>
</template>

<style scoped>
.category-recipes {
    margin-bottom: 0;
    text-align: center;
}
</style>
