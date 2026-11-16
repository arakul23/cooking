<template>
    <AccountShell :title="$t('account_favorites_title')">
        <div class="favorites-header">
            <div>
                <h2>{{ $t('account_favorites_title') }}</h2>
                <p>{{ $t('account_favorites_description') }}</p>
            </div>
            <span class="favorites-count">{{ $t('account_favorites_count', { count: favoriteRecipes.length }) }}</span>
        </div>
        <p v-if="favoriteError" class="favorites-error" role="alert">{{ favoriteError }}</p>

        <div v-if="favoriteRecipes.length === 0" class="favorites-empty">
            <div class="favorites-empty-icon">
                <i class="fa fa-heart-o" aria-hidden="true"></i>
            </div>
            <h3>{{ $t('account_favorites_empty_title') }}</h3>
            <p>{{ $t('account_favorites_empty_description') }}</p>
            <NuxtLink to="/categories" class="btn contact-btn">{{ $t('account_browse_recipes') }}</NuxtLink>
        </div>

        <div v-else class="favorites-grid">
            <article
                v-for="recipe in favoriteRecipes"
                :key="recipe.id"
                class="favorite-card"
            >
                <NuxtLink :to="{ path: '/recipe', query: { id: recipe.id } }" class="favorite-card-image">
                    <img :src="recipe.logo || DEFAULT_RECIPE_IMAGE" :alt="recipe.title">
                </NuxtLink>

                <div class="favorite-card-body">
                    <div class="favorite-card-meta">
                        <span>{{ recipe.categories.map((category) => category.name).join(', ') }}</span>
                        <span v-if="recipe.total_time_minutes">
                            {{ recipe.total_time_minutes }} {{ $t('minutes_short') }}
                        </span>
                    </div>

                    <NuxtLink :to="{ path: '/recipe', query: { id: recipe.id } }" class="favorite-card-title">
                        {{ recipe.title }}
                    </NuxtLink>

                    <p>{{ recipe.description }}</p>

                    <div class="favorite-card-footer">
                        <span class="favorite-rating">
                            <i class="fa fa-star" aria-hidden="true"></i>
                            {{ recipe.rating_avg ?? 0 }}
                        </span>

                        <div class="favorite-actions">
                            <button
                                class="favorite-action favorite-action-muted"
                                type="button"
                                :disabled="removingFavoriteId !== null"
                                @click="removeFavorite(recipe.id)"
                            >
                                {{ removingFavoriteId === recipe.id ? $t('account_remove_progress') : $t('recipe_favourite_remove') }}
                            </button>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </AccountShell>
</template>

<script setup lang="ts">
import AccountShell from '@/app/components/account/AccountShell.vue'
import { DEFAULT_RECIPE_IMAGE } from '@/app/utils/imageFallbacks'
import { useApi } from '../../composables/useApi'

const config = useRuntimeConfig()
const api = useApi()
const { t } = useI18n()
const favoriteRecipes = ref<FavoriteRecipe[]>([])
const removingFavoriteId = ref<number | null>(null)
const favoriteError = ref('')

definePageMeta({
    middleware: 'auth',
})

type FavoriteRecipe = {
    id: number
    title: string
    description: string
    logo: string | null
    categories: Array<{ id: number; name: string }>
    total_time_minutes: number | null
    rating_avg: number | string | null
}

const removeFavorite = async (recipeId: number) => {
    if (removingFavoriteId.value !== null) {
        return
    }

    removingFavoriteId.value = recipeId
    favoriteError.value = ''

    try {
        await api<void>(`${config.public.apiBase}/recipes/favourite/${recipeId}`, {
            method: 'DELETE',
        })
        favoriteRecipes.value = favoriteRecipes.value.filter((recipe) => recipe.id !== recipeId)
    } catch (error) {
        console.error('Failed to remove favorite:', error)
        favoriteError.value = t('account_remove_error')
    } finally {
        removingFavoriteId.value = null
    }
}

onMounted(async () => {
    try {
        const data = await api<FavoriteRecipe[]>(`${config.public.apiBase}/recipes/favourite`)
        favoriteRecipes.value = data
    } catch (error) {
        console.error('Failed to load favorites:', error)
        favoriteError.value = t('account_favorites_load_error')
    }
})
</script>

<style scoped>
.favorites-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 28px;
}

.favorites-header h2 {
    margin-bottom: 12px;
    color: #232d37;
    font-size: 24px;
}

.favorites-header p {
    margin-bottom: 0;
    color: #51545f;
    line-height: 1.7;
}

.favorites-count {
    flex: 0 0 auto;
    padding: 8px 12px;
    border-radius: 4px;
    background: #fff4ef;
    color: #fc6c3f;
    font-size: 13px;
    font-weight: 700;
}

.favorites-error {
    margin: -12px 0 24px;
    color: #b42318;
}

.favorites-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 22px;
}

.favorite-card {
    overflow: hidden;
    border: 1px solid #ebebeb;
    border-radius: 6px;
    background: #fff;
}

.favorite-card-image {
    position: relative;
    display: block;
    aspect-ratio: 4 / 3;
    overflow: hidden;
    background: #f6f6f6;
}

.favorite-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 220ms ease;
}

.favorite-card:hover .favorite-card-image img {
    transform: scale(1.04);
}

.favorite-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #fc6c3f;
    color: #fff;
    font-size: 14px;
}

.favorite-card-body {
    padding: 18px;
}

.favorite-card-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 10px;
    color: #888;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}

.favorite-card-title {
    display: block;
    margin-bottom: 10px;
    color: #232d37;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.35;
}

.favorite-card-title:hover {
    color: #fc6c3f;
}

.favorite-card-body p {
    display: -webkit-box;
    min-height: 48px;
    margin-bottom: 18px;
    overflow: hidden;
    color: #51545f;
    font-size: 14px;
    line-height: 1.7;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}

.favorite-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding-top: 14px;
    border-top: 1px solid #f0f0f0;
}

.favorite-rating {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #232d37;
    font-size: 14px;
    font-weight: 700;
}

.favorite-rating i {
    color: #f7b731;
}

.favorite-actions {
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.favorite-action {
    border: 0;
    background: transparent;
    color: #fc6c3f;
    cursor: pointer;
    font-size: 13px;
    font-weight: 700;
}

.favorite-action-muted {
    padding: 8px 12px;
    border: 1px solid #d64545;
    border-radius: 6px;
    background: #fff1f0;
    color: #b42318;
    transition: color 160ms ease, background 160ms ease, box-shadow 160ms ease, transform 160ms ease;
}

.favorite-action-muted:hover:not(:disabled) {
    transform: translateY(-1px);
    background: #b42318;
    color: #fff;
    box-shadow: 0 3px 8px rgb(180 35 24 / 25%);
}

.favorite-action-muted:focus-visible {
    outline: 3px solid rgb(180 35 24 / 35%);
    outline-offset: 2px;
}

.favorite-action-muted:disabled {
    cursor: wait;
    opacity: 0.65;
}

.favorites-empty {
    display: flex;
    min-height: 300px;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 38px 20px;
    border: 1px dashed #d8d8d8;
    border-radius: 6px;
    text-align: center;
}

.favorites-empty-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 64px;
    height: 64px;
    margin-bottom: 18px;
    border-radius: 50%;
    background: #fff4ef;
    color: #fc6c3f;
    font-size: 26px;
}

.favorites-empty h3 {
    margin-bottom: 8px;
    color: #232d37;
    font-size: 20px;
}

.favorites-empty p {
    max-width: 360px;
    margin-bottom: 22px;
    color: #51545f;
    line-height: 1.7;
}

@media only screen and (max-width: 767px) {
    .favorites-header {
        flex-direction: column;
    }

    .favorites-grid {
        grid-template-columns: 1fr;
    }

    .favorite-card-footer {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>
