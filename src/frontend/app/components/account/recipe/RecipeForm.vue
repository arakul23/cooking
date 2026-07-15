<script setup lang="ts">
import TiptapEditor from "../../TiptapEditor.vue";
import {recipeSchema} from "../../../composables/useRecipeValidation"
import type {FormSubmitEvent} from '@nuxt/ui'

const config = useRuntimeConfig();

type CategoryItem = {
    id: number;
    name: string;
    image: string | null;
}

const {data, error} = await useFetch<{ data: CategoryItem[] }>(
    `${config.public.apiBase}/categories`
)

if (error.value) {
    console.error('Failed to load categories:', error.value)
}

const categories = computed(() => data.value?.data ?? [])

const emit = defineEmits<{
    create: [recipe: any,]
}>()

const recipe = reactive({
    title: '',
    description: '',
    content: '',
    categories: [] as number[],
    total_time_minutes: null as number | null,
    portions: null as number | null,
    calories: null as number | null,
    logo: null as File | null,
})

async function onSubmit(event: FormSubmitEvent<typeof recipe>) {
    emit('create', event.data)
}
</script>

<template>

    <UForm :schema="recipeSchema" :state="recipe" @submit="onSubmit">
        <UFormField label="Название рецепта" name="title" class="form-group">
            <UInput v-model="recipe.title" class="w-full" size="xl"/>
        </UFormField>

        <div class="row-2">
            <UFormField label="Категория" name="categories" class="form-group">
                <USelectMenu
                    v-model="recipe.categories"
                    label-key="name"
                    color="neutral"
                    variant="outline"
                    value-key="id"
                    multiple
                    :items="categories"
                    class="w-full"
                    size="xl"
                />
            </UFormField>
            <UFormField label="Каллории" name="calories" class="form-group">
                <UInputNumber v-model="recipe.calories" color="neutral" variant="outline" class="w-full" size="xl"/>
            </UFormField>
        </div>

        <div class="row-2">
            <UFormField label="Время готовки (мин)" name="total_time_minutes" class="form-group">
                <UInputNumber v-model="recipe.total_time_minutes" color="neutral" variant="outline" class="w-full"
                              size="xl" placeholder="45"/>
            </UFormField>
            <UFormField label="Кол-во порций" name="portions" class="form-group">
                <UInputNumber v-model="recipe.portions" color="neutral" variant="outline" class="w-full" size="xl"/>
            </UFormField>
        </div>

        <UFormField label="Краткое описание" name="description" class="form-group">
            <UTextarea v-model="recipe.description" color="neutral" variant="outline" class="w-full" size="xl"/>
        </UFormField>

        <client-only>
            <UFormField label="Описание / шаги приготовления" name="content" class="form-group">
                <TiptapEditor v-model="recipe.content"/>
            </UFormField>

            <template #fallback>
                <div class="form-group">
                    <label>Описание / шаги приготовления</label>
                    <div style="height: 200px; background: #f5f5f8; border-radius: 8px;"/>
                </div>
            </template>
        </client-only>

        <UFormField label="Photo" name="logo" class="form-group">
            <UFileUpload
                v-model="recipe.logo"
                label="Drop your image here"
                description="PNG or JPG (max. 2MB)"
                class="w-96 min-h-48"
            />
        </UFormField>

        <div class="form-actions">
            <button type="button" class="btn btn-outline">Отмена</button>
            <button type="submit" class="btn btn-primary">Опубликовать рецепт</button>
        </div>
    </UForm>


</template>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: #fff;
    color: #51545f;
}

h1, h2, h3, h4, label {
    font-family: 'Quicksand', sans-serif;
    font-weight: 700;
    color: #232d37;
}

.breadcumb-area {
    width: 100%;
    background-color: #626b76;
    padding: 60px 0;
    text-align: center;
}

.breadcumb-area h2 {
    color: #fff;
    font-size: 40px;
}

.container {
    max-width: 900px;
    margin: 0 auto;
    padding: 60px 15px;
}

.card {
    background: #fff;
    border: 1px solid rgba(0, 0, 0, .08);
    border-radius: 8px;
    padding: 40px;
    box-shadow: 0 2px 15px rgba(0, 0, 0, .04);
}

.card-title {
    font-size: 22px;
    margin-bottom: 8px;
}

.card-subtitle {
    color: #b5aec4;
    font-size: 14px;
    margin-bottom: 30px;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    font-size: 14px;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: .5px;
}

.row-2 {
    display: flex;
    gap: 20px;
}

.row-2 .form-group {
    flex: 1;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    margin-top: 10px;
    border-top: 1px solid #ebebeb;
    padding-top: 30px;
}

.btn {
    display: inline-block;
    border-radius: 30px;
    padding: 0 35px;
    height: 45px;
    line-height: 45px;
    font-size: 13px;
    font-weight: 500;
    text-transform: uppercase;
    cursor: pointer;
    border: none;
    transition: all .3s ease;
    letter-spacing: .5px;
}

.btn-primary {
    background-color: #fc6c3f;
    color: #fff;
}

.btn-primary:hover {
    background-color: #d43f10;
}

.btn-outline {
    background-color: transparent;
    color: #b5aeba;
    border: 1px solid #ebebeb;
}

.btn-outline:hover {
    color: #000;
    border-color: #000;
}
</style>
