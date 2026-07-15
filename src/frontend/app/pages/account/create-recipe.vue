<script setup lang="ts">
import RecipeForm from "~~/app/components/account/recipe/RecipeForm.vue";

const toast = useToast()
const config = useRuntimeConfig();

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
            total_time_minutes: recipe.total_time_minutes,
            portions: recipe.portions,
            calories: recipe.calories
        }

        for (const [key, value] of Object.entries(fields)) {
            formData.append(key, String(value))
        }

        recipe.categories.forEach(id => formData.append('categories[]', String(id)))

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
            title: 'Успешно!',
            description: 'Рецепт добавлен',
            color: 'success'
        })
    } catch (e) {
        toast.add({
            title: 'Ошибка',
            description: 'Не удалось создать рецепт',
            color: 'error'
        })
    }
};

</script>

<template>
    <div class="breadcumb-area">
        <h2>Добавить рецепт</h2>
    </div>

    <div class="container">
        <div class="card">
            <div class="card-title">Новый рецепт</div>
            <div class="card-subtitle">Заполните поля ниже, чтобы опубликовать рецепт в блоге.</div>

          <RecipeForm @create="create" />
        </div>
    </div>
</template>

<style>

</style>
