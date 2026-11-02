<script setup lang="ts">
import { useAuth } from '@/app/composables/useAuth'
import { defineAsyncComponent } from 'vue'

const StarRating = defineAsyncComponent(() => import('vue-star-rating'))

const route = useRoute()
const config = useRuntimeConfig()
const fallbackImage = 'https://placehold.co/960x640?text=Recipe'
const { isAuthenticated } = useAuth()
const { locale } = useI18n()
const isFavorite = ref(false)
const favoriteMessage = ref('')
const rating = ref(0)

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
    rating_avg: number;
    products?: Array<{
        id?: number;
        name?: string | null;
        amount?: number | string | null;
        amount_unit?: string | null;
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
    recipeId.value
        ? `${config.public.apiBase}/recipes/${encodeURIComponent(recipeId.value)}?locale=${encodeURIComponent(locale.value)}`
        : null
)

const recipePdfUrl = computed(() =>
    recipeId.value
        ? `${config.public.apiBase}/recipes/${encodeURIComponent(recipeId.value)}/pdf?locale=${encodeURIComponent(locale.value)}`
        : null
)

const { data: recipe, pending, error } = await useFetch<RecipeItem | null>(
    recipeUrl,
    {
        watch: [recipeId, locale],
        transform: (res: ApiResponse<RecipeItem>) => res.data,
        default: () => null
    }
)

console.log('recipe.value', recipe.value);

if (recipe.value?.rating_avg) {
    rating.value = recipe.value.rating_avg
}

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

let favoriteMessageTimer: ReturnType<typeof window.setTimeout> | null = null

const toggleFavorite = async () => {
    if (!isAuthenticated.value) {
        await navigateTo('/login')
        return
    }

    isFavorite.value = !isFavorite.value
    favoriteMessage.value = isFavorite.value
        ? 'Рецепт добавлен в избранное'
        : 'Рецепт удален из избранного'

    if (favoriteMessageTimer) {
        window.clearTimeout(favoriteMessageTimer)
    }

    favoriteMessageTimer = window.setTimeout(() => {
        favoriteMessage.value = ''
    }, 1800)
}

const setRating = async () => {
    const {data, pending, error} = await useFetch<{ data: RecipeItem[] }>(
        `${config.public.apiBase}/recipes/${recipeId.value}/rating`,
        {
            method: 'POST',
            body: {
                rating: rating.value,
            },
        }
    )
}

</script>

<template>
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
            <div v-else-if="pending" class="text-center">{{ $t('loading') }}</div>
            <div v-else-if="error" class="text-center">Error loading recipe {{ error }}</div>
            <div v-else-if="recipe" class="row justify-content-center">
                <div class="col-12 col-lg-10">
                    <article class="single-post wow fadeInUp">
                        <ClientOnly>
                            {{ $t('rate_recipe') }}
                            <StarRating v-model:rating="rating" :increment="0.01" class="recipe-rating" @click="setRating" disabled/>
                        </ClientOnly>
                        <div class="post-thumb">
                            <img :src="recipe.logo || fallbackImage" :alt="recipe.title">
                        </div>
                        <div class="post-content">
                            <div v-if="favoriteMessage" class="favorite-message" role="status">
                                {{ favoriteMessage }}
                            </div>
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
                                        <a><i class="fa fa-eye" aria-hidden="true"></i> {{$t('recipe_calories')}}: {{ recipe.calories }}</a>
                                    </div>
                                    <div class="post-comments">
                                        <a><i class="fa fa-eye" aria-hidden="true"></i> {{$t('recipe_portions')}}: {{ recipe.portions }}</a>
                                    </div>
                                    <div class="post-comments">
                                        <a><i class="fa fa-eye" aria-hidden="true"></i> {{$t('views')}}: {{ recipe.views }}</a>
                                    </div>
                                    <div class="post-comments">
                                        <a><i class="fa fa-eye" aria-hidden="true"></i> {{$t('rating')}}: {{ rating }}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="recipe-title-row">
                                <h4 class="post-headline">{{ recipe.title }}</h4>
                                <div class="recipe-actions">
                                    <a
                                        v-if="recipePdfUrl"
                                        class="recipe-export"
                                        :href="recipePdfUrl"
                                    >
                                        {{ $t('recipe_export_pdf') }}
                                    </a>
                                    <button
                                        class="favorite-toggle"
                                        :class="{ active: isFavorite }"
                                        type="button"
                                        :aria-pressed="isFavorite"
                                        :aria-label="isFavorite ? 'Убрать из избранного' : 'Добавить в избранное'"
                                        @click="toggleFavorite"
                                    >
                                        <span class="favorite-heart" aria-hidden="true">
                                            {{ isFavorite ? '♥' : '♡' }}
                                        </span>
                                        <span>{{ isFavorite ? 'В избранном' : 'В избранное' }}</span>
                                    </button>
                                </div>
                            </div>
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
                                        <span class="product-amount">{{ product.amount_unit }}</span>
                                    </li>
                                </ul>
                            </div>
                            <div v-if="recipe.content" class="recipe-content" v-html="recipe.content"></div>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.recipe-title-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 16px;
}

.recipe-title-row .post-headline {
    margin-bottom: 0;
}

.recipe-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
}

.recipe-export {
    display: inline-flex;
    align-items: center;
    min-height: 42px;
    padding: 9px 14px;
    border: 1px solid #fc6c3f;
    border-radius: 4px;
    color: #fc6c3f;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
}

.recipe-export:hover {
    background: #fc6c3f;
    color: #fff;
}

.favorite-toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    gap: 8px;
    min-height: 42px;
    padding: 9px 14px;
    border: 1px solid #f5c6b6;
    border-radius: 4px;
    background: #fff4ef;
    color: #fc6c3f;
    cursor: pointer;
    font-size: 14px;
    font-weight: 700;
    transition: transform 160ms ease, background 160ms ease, color 160ms ease;
}

.favorite-toggle:hover {
    transform: translateY(-1px);
}

.favorite-toggle.active {
    background: #fc6c3f;
    border-color: #fc6c3f;
    color: #fff;
}

.favorite-heart {
    font-size: 20px;
    line-height: 1;
}

.favorite-message {
    margin-bottom: 18px;
    padding: 12px 14px;
    border: 1px solid #f5c6b6;
    border-radius: 6px;
    background: #fff4ef;
    color: #b84b24;
    font-size: 14px;
    font-weight: 600;
}

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

@media only screen and (max-width: 767px) {
    .recipe-title-row {
        flex-direction: column;
    }

    .recipe-actions {
        justify-content: flex-start;
    }
}
</style>
