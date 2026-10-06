<script setup lang="ts">
type CategoryItem = {
    id: number
    name: string
    image: string | null
    recipes_count?: number | null
}

type RecipeItem = {
    id: number
    title: string
    description: string | null
    logo: string | null
}

type ApiResponse<T> = {
    data?: T
}

type SearchResults = {
    categories: CategoryItem[]
    recipes: RecipeItem[]
}

const route = useRoute()
const config = useRuntimeConfig()
const requestFetch = useRequestFetch()
const searchQuery = computed(() => {
    const query = route.query.q
    return (Array.isArray(query) ? query[0] : query ?? '').trim()
})
const searchCategories = computed(() => route.query.categories !== '0')
const searchRecipes = computed(() => route.query.recipes !== '0')

const fetchCollection = async <T>(url: string): Promise<T[]> => {
    const response = await requestFetch<T[] | ApiResponse<T[]> | null>(url, {
        query: { search: searchQuery.value },
    })

    if (!response) {
        return []
    }

    if (Array.isArray(response)) {
        return response
    }

    return Array.isArray(response.data) ? response.data : []
}

const { data, pending, error } = await useAsyncData<SearchResults>(
    'search-results',
    async () => {
        if (!searchQuery.value) {
            return { categories: [], recipes: [] }
        }

        const [categories, recipes] = await Promise.all([
            searchCategories.value
                ? fetchCollection<CategoryItem>(`${config.public.apiBase}/categories`)
                : Promise.resolve([]),
            searchRecipes.value
                ? fetchCollection<RecipeItem>(`${config.public.apiBase}/recipes`)
                : Promise.resolve([]),
        ])

        return {
            categories,
            recipes,
        }
    },
    {
        watch: [searchQuery, searchCategories, searchRecipes],
        default: () => ({ categories: [], recipes: [] }),
    },
)

const categories = computed(() =>
    Array.isArray(data.value?.categories) ? data.value.categories : []
)
const recipes = computed(() =>
    Array.isArray(data.value?.recipes) ? data.value.recipes : []
)
const hasResults = computed(() =>
    categories.value.length > 0 || recipes.value.length > 0
)
</script>

<template>
    <div class="breadcumb-area">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-12">
                    <div class="bradcumb-title text-center">
                        <h2>{{ $t('search_results') }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="breadcumb-nav">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <NuxtLink to="/">
                            <i class="fa fa-home" aria-hidden="true"></i>
                            Home
                        </NuxtLink>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $t('search_results') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="search-page">
        <div class="container">
            <p v-if="searchQuery" class="search-query">
                {{ $t('search_for') }}: <strong>“{{ searchQuery }}”</strong>
            </p>
            <div v-if="pending" class="text-center search-message">{{ $t('loading') }}</div>
            <div v-else-if="error" class="text-center search-message">{{ $t('search_error') }}</div>
            <div v-else-if="!searchQuery" class="text-center search-message">{{ $t('search_no_query') }}</div>
            <div v-else-if="!hasResults" class="text-center search-message">{{ $t('search_no_results') }}</div>

            <section v-if="categories.length" class="search-section">
                <h3>{{ $t('search_category_count') }} <span>{{ categories.length }}</span></h3>
                <div class="row">
                    <div
                        v-for="category in categories"
                        :key="category.id"
                        class="col-12 col-md-6 col-lg-4"
                    >
                        <NuxtLink :to="{ path: '/category', query: { id: category.id } }" class="search-result-link">
                            <article class="single_catagory search-category-card">
                                <img
                                    :src="category.image || 'https://placehold.co/640x420?text=Category'"
                                    :alt="category.name"
                                >
                                <div class="catagory-title">
                                    <h5>{{ category.name }}</h5>
                                </div>
                                <p class="search-category-count">{{ category.recipes_count ?? 0 }} {{ $t('search_recipe_count') }}</p>
                            </article>
                        </NuxtLink>
                    </div>
                </div>
            </section>

            <section v-if="recipes.length" class="search-section">
                <h3>{{ $t('search_recipe_count') }} <span>{{ recipes.length }}</span></h3>
                <div class="row">
                    <div
                        v-for="recipe in recipes"
                        :key="recipe.id"
                        class="col-12 col-md-6 col-lg-4"
                    >
                        <NuxtLink :to="{ path: '/recipe', query: { id: recipe.id } }" class="search-result-link">
                            <article class="single-post search-recipe-card">
                                <div class="post-thumb">
                                    <img
                                        :src="recipe.logo || 'https://placehold.co/640x420?text=Recipe'"
                                        :alt="recipe.title"
                                    >
                                </div>
                                <div class="post-content">
                                    <h4 class="post-headline">{{ recipe.title }}</h4>
                                    <p>{{ recipe.description }}</p>
                                </div>
                            </article>
                        </NuxtLink>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<style scoped>
.search-page {
    padding: 48px 0 70px;
}

.search-query {
    margin-bottom: 32px;
    color: #727272;
    text-align: center;
}

.search-message {
    padding: 32px 0;
    color: #727272;
}

.search-section + .search-section {
    margin-top: 42px;
}

.search-section h3 {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 24px;
    color: #232d37;
    font-size: 24px;
}

.search-section h3 span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
    padding: 0 8px;
    border-radius: 14px;
    background: #fff0eb;
    color: #fc6c3f;
    font-size: 14px;
}

.search-result-link {
    display: block;
    color: inherit;
    text-decoration: none;
}

.search-category-card {
    overflow: hidden;
    margin-bottom: 28px;
}

.search-category-card .catagory-title h5 {
    margin-bottom: 0;
    line-height: 50px;
}

.search-category-card:hover .catagory-title h5 {
    color: #fff;
}

.search-category-count {
    margin: 14px 12px;
    color: #727272;
    text-align: center;
}

.search-recipe-card {
    height: calc(100% - 24px);
    margin-bottom: 24px;
    transition: transform 160ms ease, box-shadow 160ms ease;
}

.search-recipe-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgb(35 45 55 / 10%);
}

.search-recipe-card .post-thumb img {
    width: 100%;
    aspect-ratio: 4 / 3;
    object-fit: cover;
}
</style>
