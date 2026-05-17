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
    views: number
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
                                        <a><i class="fa fa-eye" aria-hidden="true"></i> {{ recipe.views }}</a>
                                    </div>
                                </div>
                            </div>
                            <h4 class="post-headline">{{ recipe.title }}</h4>
                            <p>{{ recipe.description }}</p>
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
.recipe-content {
    white-space: pre-line;
}
</style>
