<script setup lang="ts">
import RecipeForm from "~~/app/components/account/recipe/RecipeForm.vue";

const config = useRuntimeConfig();

definePageMeta({
    middleware: 'auth',
})

const create = async (recipe) => {
    console.log(recipe);
     await useFetch<{ data: Recipe[] }>(
        `${config.public.apiBase}/recipes/create`,
        {
            method: 'POST',
            body: recipe,
        }
    )
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
