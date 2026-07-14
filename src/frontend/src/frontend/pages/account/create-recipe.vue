<script setup lang="ts">
import {USelectMenu} from "#components";
import TiptapEditor from "~~/app/components/TiptapEditor.vue";

definePageMeta({
    middleware: 'auth',
})

const config = useRuntimeConfig();

type Recipe = {
    title: string
    description: string
    logo: string
    categories: number[]
    cookingTime: string
    portions: number
    time_raw: string
}

type CategoryItem = {
    id: number;
    name: string;
    image: string | null;
    recipes_count?: number | null;
    recipesCount?: number | null;
}

const recipe = reactive<Recipe>({
    title: '',
    description: '',
    logo: '',
    categories: [],
    cookingTime: '',
    portions: 0,
    time_raw: ''
})

// 1. Делаем запрос на самом верхнем уровне (без onMounted и без лишних try-catch вокруг useFetch)
const { data, error } = await useFetch<{ data: CategoryItem[] }>(
    `${config.public.apiBase}/categories`
)

if (error.value) {
    console.error('Failed to load categories:', error.value)
}

// 2. Объявляем computed на верхнем уровне. Шаблон автоматически увидит изменения!
const categories = computed(() => data.value?.data ?? [])

const create = async () => {
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

            <form @submit.prevent="create" method="post">
                <div class="form-group">
                    <label for="title">Название рецепта</label>
                    <input v-model="recipe.title" class="form-control" type="text" id="title" name="title" placeholder="Например, Тирамису классический">
                </div>

                <div class="row-2">
                    <div class="form-group">
                        <label for="category">Категория</label>
                        <USelectMenu v-model="recipe.categories" label-key="name" color="neutral"
                                     variant="outline" value-key="id" multiple :items="categories" class="w-48" size="xl" />
                    </div>
                    <div class="form-group">
                        <label for="cook_time">Время готовки (мин)</label>
                        <input v-model="recipe.cookingTime" class="form-control" type="number" id="cook_time" name="cook_time" placeholder="45">
                    </div>
                </div>

<!--                <div class="form-group">
                    <label for="ingredients">Ингредиенты</label>
                    <textarea class="form-control" id="ingredients" name="ingredients" placeholder="Каждый ингредиент с новой строки"></textarea>
                    <span class="hint">Один ингредиент на строку — так удобнее парсить на бэке.</span>
                </div>-->

                <div class="form-group">
                <client-only>
                    <label for="description">Описание / шаги приготовления</label>
                    <TiptapEditor v-model="recipe.description" />
                </client-only>
                </div>

<!--
                    <textarea class="form-control" id="description" name="description" placeholder="Опишите процесс приготовления шаг за шагом"></textarea>
-->


                <div class="form-group">
                    <label>Фото блюда</label>
                    <div class="upload-box">
                        <span>Перетащите изображение сюда или нажмите, чтобы выбрать файл</span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-outline">Отмена</button>
                    <button type="submit" class="btn btn-primary">Опубликовать рецепт</button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
* { margin: 0; padding: 0; box-sizing: border-box; }

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
    border: 1px solid rgba(0,0,0,.08);
    border-radius: 8px;
    padding: 40px;
    box-shadow: 0 2px 15px rgba(0,0,0,.04);
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

.form-group .hint {
    display: block;
    font-family: 'Poppins', sans-serif;
    font-weight: 400;
    text-transform: none;
    color: #b5aec4;
    font-size: 12px;
    margin-top: 4px;
    letter-spacing: 0;
}

.form-control,
textarea.form-control {
    width: 100%;
    border: 1px solid #ebebeb;
    border-radius: 8px;
    padding: 0 20px;
    height: 50px;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    color: #51545f;
    background-color: #f5f5f8;
    transition: border-color .3s ease;
}

textarea.form-control {
    height: auto;
    padding: 15px 20px;
    resize: vertical;
    min-height: 130px;
}

.form-control:focus,
textarea.form-control:focus {
    outline: none;
    border-color: #fc6c3f;
    background-color: #fff;
}

.row-2 {
    display: flex;
    gap: 20px;
}

.row-2 .form-group {
    flex: 1;
}

.upload-box {
    border: 2px dashed #ebebeb;
    border-radius: 8px;
    padding: 30px;
    text-align: center;
    cursor: pointer;
    background-color: #f5f5f8;
    transition: border-color .3s ease;
}

.upload-box:hover {
    border-color: #fc6c3f;
}

.upload-box i {
    font-size: 30px;
    color: #fc6c3f;
    display: block;
    margin-bottom: 10px;
}

.upload-box span {
    font-size: 13px;
    color: #b5aec4;
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
