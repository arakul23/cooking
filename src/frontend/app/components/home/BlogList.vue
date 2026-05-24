<template>
    <section class="blog_area section_padding_0_80">
        <div class="container">
            <div v-if="pending" class="recipes-loading">
                <div class="yummy-load"></div>
            </div>
            <div v-else-if="error">Failed to load recipes</div>
            <div v-else-if="recipes.length === 0">No recipes yet</div>
            <div v-else class="row">
                <div
                    v-for="recipe in recipes"
                    :key="recipe.id"
                    class="col-12 col-md-6"
                >
                    <article class="single-post wow fadeInUp mb-4">
                        <div class="post-thumb">
                            <img :src="recipe.logo || fallbackImage" :alt="recipe.title">
                        </div>
                        <div class="post-content">
                            <h4 class="post-headline">{{ recipe.title }}</h4>
                            <p>{{ recipe.description }}</p>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
const config = useRuntimeConfig()
const fallbackImage = 'https://placehold.co/960x640?text=Recipe'

type RecipeItem = {
    id: number
    title: string
    logo: string | null
    description: string
}

const { data, pending, error } = await useFetch<RecipeItem[]>(
    `${config.public.apiBase}/recipes`, {
        server: false
    }
)

const recipes = computed(() => data.value ?? [])
</script>

<style scoped>
.recipes-loading {
    min-height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.recipes-loading .yummy-load {
    position: static;
    left: auto;
    top: auto;
}
</style>
