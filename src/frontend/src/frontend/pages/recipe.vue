<script setup lang="ts">
import Preloader from '@/app/components/layout/Preloader.vue'
import Header from '@/app/components/layout/Header.vue'
import Footer from '@/app/components/layout/Footer.vue'

const route = useRoute()
const config = useRuntimeConfig()
const fallbackImage = 'https://placehold.co/960x640?text=Recipe'

const recipeId = computed(() => {
    const id = route.query.id
    return Array.isArray(id) ? id[0] : String(id ?? '')
})

type RecipeCategory = {
    id: number;
    name: string;
    image: string | null;
}

type RecipeItem = {
    id: number;
    title: string;
    description: string;
    logo: string | null;
    content: string | null;
    created_at: string;
    categories: RecipeCategory[];
    views: number;
    products?: Array<{
        id?: number;
        name?: string | null;
        amount?: number | string | null;
        unit?: {
            id?: number;
            name?: string | null;
            short_name?: string | null;
            shortName?: string | null;
        } | string | null;
        note?: string | null;
    }>;
}

type ApiResponse<T> = {
    data: T
}

const recipeUrl = computed(() =>
    recipeId.value ? `${config.public.apiBase}/recipes/${recipeId.value}` : null
)

const { data: recipe, pending, error } = await useFetch<RecipeItem | null>(
    recipeUrl,
    {
        watch: [recipeId],
        transform: (res: ApiResponse<RecipeItem>) => res.data,
        default: () => null
    }
)

const recipeCategories = computed(() => recipe.value?.categories ?? [])
const recipeProducts = computed(() => recipe.value?.products ?? [])

const formattedCreatedAt = computed(() => {
    const value = recipe.value?.created_at
    if (!value) return ''

    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return value

    return new Intl.DateTimeFormat('uk-UA', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(date)
})

const formatProductAmount = (product: NonNullable<RecipeItem['products']>[number]) => {
    const amount = product.amount

    if (amount === null || amount === undefined || amount === '') {
        return ''
    }

    const unit = typeof product.unit === 'string'
        ? product.unit
        : (product.unit?.short_name ?? product.unit?.shortName ?? product.unit?.name ?? '')

    return `${amount}${unit ? ` ${unit}` : ''}`
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
                        <h2>{{ recipe?.title || 'Recipe' }}</h2>
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
                            <li class="breadcrumb-item active" aria-current="page">Recipe</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <section class="blog_area section_padding_80">
        <div class="container">
            <div v-if="!recipeId" class="text-center">Recipe id is missing</div>
            <div v-else-if="pending" class="text-center">Loading...</div>
            <div v-else-if="error" class="text-center">Error loading recipe</div>
            <div v-else-if="recipe" class="row justify-content-center">
                <div class="col-12 col-lg-10">
                    <article class="single-post wow fadeInUp">
                        <div class="post-thumb">
                            <img :src="recipe.logo || fallbackImage" :alt="recipe.title">
                        </div>
                        <div class="post-content">
                            <div class="post-meta d-flex flex-wrap align-items-center">
                                <div class="post-author-date-area d-flex flex-wrap align-items-center">
                                    <div class="post-author" v-if="recipeCategories.length > 0">
                                        <a>{{ recipeCategories.map((category) => category.name).join(', ') }}</a>
                                    </div>
                                    <div class="post-author" v-else>
                                        <a>No category</a>
                                    </div>
                                    <div class="post-date" v-if="formattedCreatedAt">
                                        <a>{{ formattedCreatedAt }}</a>
                                    </div>
                                </div>
                                <div class="post-comment-share-area d-flex">
                                    <div class="post-comments">
                                        <a><i class="fa fa-eye" aria-hidden="true"></i> Views: {{ recipe.views }}</a>
                                    </div>
                                </div>
                            </div>
                            <h4 class="post-headline">{{ recipe.title }}</h4>
                            <p>{{ recipe.description }}</p>
                            <div v-if="recipeProducts.length > 0" class="recipe-products">
                                <h5 class="recipe-products-title">Products</h5>
                                <ul class="recipe-products-list">
                                    <li
                                        v-for="(product, index) in recipeProducts"
                                        :key="product.id ?? `${product.name}-${index}`"
                                        class="recipe-products-item"
                                    >
                                        <span class="product-name">{{ product.name || `Product ${index + 1}` }}</span>
                                        <span class="product-amount">{{ formatProductAmount(product) }}</span>
                                    </li>
                                </ul>
                            </div>
                            <p v-if="recipe.content" class="recipe-content">{{ recipe.content }}</p>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>

<style scoped>
.recipe-products {
    margin: 22px 0 26px;
    padding: 22px 24px;
    border: 1px solid #ebebeb;
    border-radius: 16px;
}

.recipe-products-title {
    margin: 0 0 14px;
    color: #232d37;
    font-size: 20px;
}

.recipe-products-list {
    margin: 0;
    padding: 0;
    list-style: none;
}

.recipe-products-item {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 16px;
    padding: 10px 0;
    border-bottom: 1px solid #f1edf7;
}

.recipe-products-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.product-name {
    color: #232d37;
    font-weight: 500;
}

.product-amount {
    color: #8a8494;
    white-space: nowrap;
}

.recipe-content {
    white-space: pre-line;
}
</style>
