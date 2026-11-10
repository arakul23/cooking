<script setup lang="ts">
import RecipeForm from "~~/app/components/account/recipe/RecipeForm.vue";

const toast = useToast()
const config = useRuntimeConfig();
const { t, locale } = useI18n()

definePageMeta({
    middleware: 'auth',
})

const create = async (recipe) => {
    try {
        const formData = new FormData()

        const fields = {
            title: recipe.title,
            description: recipe.description,
            content: recipe.content,
            locale: locale.value,
            total_time_minutes: recipe.total_time_minutes,
            portions: recipe.portions,
            calories: recipe.calories
        }

        for (const [key, value] of Object.entries(fields)) {
            formData.append(key, String(value))
        }

        recipe.categories.forEach(id => formData.append('categories[]', String(id)))
        recipe.ingredients.forEach((ingredient, index) => {
            formData.append(`ingredients[${index}][product_id]`, String(ingredient.product_id))
            formData.append(`ingredients[${index}][amount]`, String(ingredient.amount))
            formData.append(`ingredients[${index}][unit_id]`, String(ingredient.unit_id))
        })

        if (recipe.logo) {
            formData.append('logo', recipe.logo)
        }

        await $fetch<{ data: Recipe[] }>(
            `${config.public.apiBase}/recipes/create`,
            {
                method: 'POST',
                body: formData,
            }
        )

        toast.add({
            title: t('recipe_create_success_title'),
            description: t('recipe_create_success_message'),
            color: 'success'
        })
    } catch (e) {
        toast.add({
            title: t('recipe_create_error_title'),
            description: t('recipe_create_error_message'),
            color: 'error'
        })
    }
};

</script>

<template>
    <div class="breadcumb-area">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-12">
                    <div class="bradcumb-title text-center">
                        <h2>{{ $t('create_recipe_title') }}</h2>
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
                            {{ $t('menu_home') }}
                        </NuxtLink>
                    </li>
                    <li class="breadcrumb-item">
                        <NuxtLink to="/account">{{ $t('account') }}</NuxtLink>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $t('create_recipe_title') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container recipe-create-page">
        <div class="card recipe-create-card">
            <div class="card-title">{{ $t('recipe_create_heading') }}</div>
            <div class="card-subtitle">{{ $t('recipe_create_description') }}</div>

          <RecipeForm @create="create" />
        </div>
    </div>
</template>

<style scoped>
.recipe-create-page {
    padding-top: 32px;
    padding-bottom: 64px;
}

.recipe-create-card {
    padding: clamp(20px, 4vw, 40px);
    border: 1px solid rgb(0 0 0 / 8%);
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 2px 15px rgb(0 0 0 / 4%);
}

.card-title {
    margin-bottom: 8px;
    color: var(--site-heading);
    font-size: 22px;
    font-weight: 700;
}

.card-subtitle {
    margin-bottom: 30px;
    color: #888;
    font-size: 14px;
}
</style>
