<script setup lang="ts">
const route = useRoute()
const config = useRuntimeConfig()
const { locale } = useI18n()
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
    rating_avg: number | string | null
}

const {data, pending, error} = await useFetch<{ data: CategoryItem }>(
    `${config.public.apiBase}/categories/${categoryId.value}`
)

const category = computed(() => data.value?.data ?? null)
const recipes = computed(() => category.value?.recipes ?? [])
const sortMode = ref<'rating' | 'alphabetical'>('rating')
const sortedRecipes = computed(() => [...recipes.value].sort((left, right) => {
    if (sortMode.value === 'alphabetical') {
        return left.title.localeCompare(right.title, locale.value, { sensitivity: 'base' })
    }

    return Number(right.rating_avg ?? 0) - Number(left.rating_avg ?? 0)
}))
const changeSortMode = (event: Event) => {
    const selectedMode = (event.currentTarget as HTMLSelectElement).value
    sortMode.value = selectedMode === 'alphabetical' ? 'alphabetical' : 'rating'
}
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
            <template v-else-if="recipes.length">
                <div class="category-recipes-sort">
                    <label for="category-recipes-sort">{{$t('sort')}}</label>
                    <select
                        id="category-recipes-sort"
                        :value="sortMode"
                        aria-label="Сортировка рецептов"
                        @change="changeSortMode"
                    >
                        <option value="rating">{{ $t('sort_by_rating') }}</option>
                        <option value="alphabetical">{{ $t('sort_by_alphabet') }}</option>
                    </select>
                </div>
                <div class="row">
                    <div
                        v-for="recipe in sortedRecipes"
                        :key="`${sortMode}-${recipe.id}`"
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
            </template>
            <div v-else class="text-center">No recipes in this category yet</div>
        </div>
    </section>
</template>

<style scoped>
.category-recipes-sort {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    margin-bottom: 20px;
    color: #727272;
}

.category-recipes-sort select {
    min-height: 42px;
    padding: 8px 12px;
    border: 1px solid #e5e5e5;
    border-radius: 4px;
    background: #fff;
    color: #232d37;
    font: inherit;
}

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
