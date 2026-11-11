<script setup lang="ts">
type ProductOption = {
    id: number
    name: string
}

type UnitOption = {
    id: number
    name: string
    code: string
    type: string
}

type CategoryOption = {
    id: number
    name: string
}

type RecipeItem = {
    id: number
    title: string
    description: string
    logo: string | null
    categories: CategoryOption[]
    products: Array<{
        id: number
        name: string
    }>
    missing_products: Array<{
        id: number
        name: string
        amount_unit: string
    }>
}

type FridgeOptions = {
    units: UnitOption[]
    categories: CategoryOption[]
}

type IngredientRow = {
    key: number
    product_id: number | null
    amount: number | null
    unit_id: number | null
}

type ApiCollection<T> = {
    data: T[]
}

const config = useRuntimeConfig()
const { t, locale } = useI18n()
const api = useApi()
const fallbackImage = 'https://placehold.co/640x420?text=Recipe'
const maxIngredients = 30
const optionsUrl = computed(() =>
    `${config.public.apiBase}/recipes/fridge-search/options?locale=${encodeURIComponent(locale.value)}`,
)

const [optionsFetch, initialProductsFetch] = await Promise.all([
    useFetch<FridgeOptions>(optionsUrl, { watch: [locale] }),
    useFetch<ApiCollection<ProductOption>>(`${config.public.apiBase}/recipes/fridge-search/products`),
])

const options = optionsFetch.data
const optionsError = optionsFetch.error
const units = computed(() => options.value?.units ?? [])
const categories = computed(() => options.value?.categories ?? [])
const defaultProductOptions = ref(initialProductsFetch.data.value?.data ?? [])
const unitOptions = computed(() =>
    units.value.map(unit => ({ ...unit, label: unit.name })),
)

let nextRowKey = 1
const createIngredientRow = (): IngredientRow => ({
    key: nextRowKey++,
    product_id: null,
    amount: 1,
    unit_id: null,
})

const ingredients = ref<IngredientRow[]>([createIngredientRow()])
const categoryId = ref<number | null>(null)
const results = ref<RecipeItem[]>([])
const submitted = ref(false)
const loading = ref(false)
const submitError = ref('')
const productSearchTerms = reactive<Record<number, string>>({})
const productSearchResults = reactive<Record<number, ProductOption[]>>({})
const productSearchLoading = reactive<Record<number, boolean>>({})
const productSearchErrors = reactive<Record<number, boolean>>({})
const productSearchTimers = new Map<number, ReturnType<typeof setTimeout>>()
const productSearchRequests = new Map<number, number>()
const knownProductOptions = reactive<Record<number, ProductOption>>({})
productSearchTerms[ingredients.value[0].key] = ''
productSearchResults[ingredients.value[0].key] = defaultProductOptions.value
productSearchErrors[ingredients.value[0].key] = Boolean(initialProductsFetch.error.value)

const rememberProductOptions = (products: ProductOption[]) => {
    products.forEach(product => {
        knownProductOptions[product.id] = product
    })
}

rememberProductOptions(defaultProductOptions.value)

const productOptionsFor = (row: IngredientRow) => {
    const selectedProductIds = ingredients.value
        .filter(ingredient => ingredient.key !== row.key)
        .map(ingredient => ingredient.product_id)
    const options = productSearchResults[row.key] ?? []
    const selectedProduct = row.product_id === null ? undefined : knownProductOptions[row.product_id]
    const availableOptions = selectedProduct && !options.some(product => product.id === selectedProduct.id)
        ? [...options, selectedProduct]
        : options

    return availableOptions.filter(product =>
        !selectedProductIds.includes(product.id) || product.id === row.product_id,
    )
}

const searchProducts = (rowKey: number, search: string) => {
    const term = search.trim()
    const previousTimer = productSearchTimers.get(rowKey)

    if (previousTimer) {
        clearTimeout(previousTimer)
    }

    if (term.length < 2) {
        productSearchRequests.set(rowKey, (productSearchRequests.get(rowKey) ?? 0) + 1)
        productSearchResults[rowKey] = term ? [] : (defaultProductOptions.value ?? [])
        productSearchLoading[rowKey] = false
        productSearchErrors[rowKey] = false
        return
    }

    productSearchLoading[rowKey] = true
    productSearchErrors[rowKey] = false
    const requestId = (productSearchRequests.get(rowKey) ?? 0) + 1
    productSearchRequests.set(rowKey, requestId)

    productSearchTimers.set(rowKey, setTimeout(async () => {
        try {
            const response = await api<ApiCollection<ProductOption>>(
                `${config.public.apiBase}/recipes/fridge-search/products`,
                { query: { search: term } },
            )

            if (productSearchRequests.get(rowKey) === requestId) {
                productSearchResults[rowKey] = response.data
                rememberProductOptions(response.data)
            }
        } catch {
            if (productSearchRequests.get(rowKey) === requestId) {
                productSearchErrors[rowKey] = true
                productSearchResults[rowKey] = []
            }
        } finally {
            if (productSearchRequests.get(rowKey) === requestId) {
                productSearchLoading[rowKey] = false
            }
        }
    }, 250))
}

const addIngredient = () => {
    const row = createIngredientRow()
    ingredients.value.push(row)
    productSearchTerms[row.key] = ''
    productSearchResults[row.key] = defaultProductOptions.value
}

const removeIngredient = (key: number) => {
    ingredients.value = ingredients.value.filter(ingredient => ingredient.key !== key)
    const timer = productSearchTimers.get(key)

    if (timer) {
        clearTimeout(timer)
    }

    delete productSearchTerms[key]
    delete productSearchResults[key]
    delete productSearchLoading[key]
    delete productSearchErrors[key]
    productSearchRequests.delete(key)
}

const searchRecipes = async () => {
    submitted.value = true
    submitError.value = ''
    results.value = []

    if (categoryId.value === null || ingredients.value.length === 0) {
        return
    }

    if (ingredients.value.some(ingredient =>
        ingredient.product_id === null
        || ingredient.unit_id === null
        || ingredient.amount === null
        || ingredient.amount <= 0,
    )) {
        submitError.value = t('fridge_search_form_error')
        return
    }

    loading.value = true

    try {
        const response = await api<ApiCollection<RecipeItem>>(
            `${config.public.apiBase}/recipes/fridge-search`,
            {
                method: 'POST',
                body: {
                    category_id: categoryId.value,
                    locale: locale.value,
                    ingredients: ingredients.value.map(({ product_id, amount, unit_id }) => ({
                        product_id,
                        amount,
                        unit_id,
                    })),
                },
            },
        )

        results.value = response.data
    } catch (error: unknown) {
        const responseData = error && typeof error === 'object' && 'data' in error
            ? error.data
            : null
        const message = responseData && typeof responseData === 'object' && 'message' in responseData
            ? responseData.message
            : null

        submitError.value = typeof message === 'string' ? message : t('fridge_search_error')
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="breadcumb-area">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-12">
                    <div class="bradcumb-title text-center">
                        <h2>{{ $t('fridge_search_title') }}</h2>
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
                    <li class="breadcrumb-item active" aria-current="page">{{ $t('fridge_search_title') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <main class="fridge-search-page">
        <div class="container">
            <section class="fridge-search-card">
                <p class="fridge-search-intro">{{ $t('fridge_search_description') }}</p>

                <div v-if="optionsError" class="fridge-search-message is-error" role="alert">
                    {{ $t('fridge_search_options_error') }}
                </div>
                <div v-else-if="!units.length || !categories.length" class="fridge-search-message">
                    {{ $t('fridge_search_options_empty') }}
                </div>
                <form v-else class="fridge-search-form" @submit.prevent="searchRecipes">
                    <label class="fridge-field">
                        <span>{{ $t('fridge_search_category') }}</span>
                        <USelectMenu
                            v-model="categoryId"
                            :items="categories"
                            label-key="name"
                            color="neutral"
                            variant="outline"
                            value-key="id"
                            :placeholder="$t('fridge_search_choose_category')"
                            class="w-full"
                            size="xl"
                        />
                    </label>

                    <section class="fridge-ingredients-section">
                        <div class="fridge-ingredients-heading">
                            <h3>{{ $t('fridge_search_ingredients') }}</h3>
                            <p>{{ $t('fridge_search_amount_hint') }}</p>
                        </div>

                        <div class="fridge-ingredient-list">
                            <div
                                v-for="(ingredient, index) in ingredients"
                                :key="ingredient.key"
                                class="fridge-ingredient-row"
                            >
                                <label class="fridge-field fridge-product-field">
                                    <span>{{ $t('fridge_search_product_number', { number: index + 1 }) }}</span>
                                    <USelectMenu
                                        v-model="ingredient.product_id"
                                        :items="productOptionsFor(ingredient)"
                                        label-key="name"
                                        color="neutral"
                                        variant="outline"
                                        value-key="id"
                                        :placeholder="$t('fridge_search_choose_product')"
                                        v-model:search-term="productSearchTerms[ingredient.key]"
                                        :search-input="{
                                            placeholder: $t('fridge_search_choose_product'),
                                            loading: productSearchLoading[ingredient.key],
                                        }"
                                        ignore-filter
                                        class="w-full"
                                        size="xl"
                                        @update:search-term="searchProducts(ingredient.key, $event)"
                                    />
                                    <span v-if="productSearchErrors[ingredient.key]" class="fridge-field-error">
                                        {{ $t('fridge_search_products_error') }}
                                    </span>
                                    <span
                                        v-else-if="(productSearchTerms[ingredient.key] ?? '').trim().length < 2"
                                        class="fridge-field-hint"
                                    >
                                        {{ $t('fridge_search_product_hint') }}
                                    </span>
                                    <span
                                        v-else-if="!productSearchLoading[ingredient.key] && !productOptionsFor(ingredient).length"
                                        class="fridge-field-hint"
                                    >
                                        {{ $t('fridge_search_products_empty') }}
                                    </span>
                                </label>
                                <label class="fridge-field fridge-amount-field">
                                    <span>{{ $t('fridge_search_amount') }}</span>
                                    <UInputNumber
                                        v-model="ingredient.amount"
                                        :min="0.001"
                                        :step="0.001"
                                        :format-options="{ maximumFractionDigits: 3 }"
                                        color="neutral"
                                        variant="outline"
                                        class="w-full"
                                        size="xl"
                                    />
                                </label>
                                <label class="fridge-field fridge-unit-field">
                                    <span>{{ $t('fridge_search_unit') }}</span>
                                    <USelectMenu
                                        v-model="ingredient.unit_id"
                                        :items="unitOptions"
                                        label-key="label"
                                        color="neutral"
                                        variant="outline"
                                        value-key="id"
                                        :placeholder="$t('fridge_search_choose_unit')"
                                        class="w-full"
                                        size="xl"
                                    />
                                </label>
                                <button
                                    v-if="ingredients.length > 1"
                                    type="button"
                                    class="fridge-remove-button"
                                    :aria-label="$t('fridge_search_remove_product')"
                                    :title="$t('fridge_search_remove_product')"
                                    @click="removeIngredient(ingredient.key)"
                                >
                                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
                                        <path d="M6 6l12 12M18 6L6 18" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="fridge-add-button"
                            :disabled="ingredients.length >= maxIngredients"
                            @click="addIngredient"
                        >
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                            {{ $t('fridge_search_add_product') }}
                        </button>
                    </section>

                    <p v-if="submitError" class="fridge-search-message is-error" role="alert">
                        {{ submitError }}
                    </p>

                    <button
                        type="submit"
                        class="fridge-submit-button"
                        :disabled="loading || !categoryId || ingredients.length === 0"
                    >
                        {{ loading ? $t('fridge_search_loading') : $t('fridge_search_submit') }}
                    </button>
                </form>
            </section>

            <section v-if="submitted && !loading && !submitError" class="fridge-results" aria-live="polite">
                <h3>{{ $t('fridge_search_results', { count: results.length }) }}</h3>
                <p v-if="results.length === 0" class="fridge-search-message">
                    {{ $t('fridge_search_no_results') }}
                </p>
                <div v-else class="row">
                    <div
                        v-for="recipe in results"
                        :key="recipe.id"
                        class="col-12 col-md-6 col-lg-4"
                    >
                        <NuxtLink
                            :to="{ path: '/recipe', query: { id: recipe.id } }"
                            class="fridge-recipe-link"
                        >
                            <article class="fridge-recipe-card">
                                <img :src="recipe.logo || fallbackImage" :alt="recipe.title">
                                <div class="fridge-recipe-content">
                                    <h4>{{ recipe.title }}</h4>
                                    <p>{{ recipe.description }}</p>
                                    <div v-if="recipe.products.length" class="fridge-recipe-products">
                                        <span class="fridge-recipe-products-label">
                                            {{ $t('fridge_search_recipe_products') }}:
                                        </span>
                                        <span
                                            v-for="product in recipe.products"
                                            :key="product.id"
                                            class="fridge-recipe-product"
                                        >
                                            {{ product.name }}
                                        </span>
                                    </div>
                                    <div v-if="recipe.missing_products.length" class="fridge-recipe-missing">
                                        <span class="fridge-recipe-missing-label">
                                            {{ $t('fridge_search_missing_products') }}:
                                        </span>
                                        <span
                                            v-for="product in recipe.missing_products"
                                            :key="product.id"
                                            class="fridge-recipe-missing-product"
                                        >
                                            {{ product.name }} — {{ product.amount_unit }}
                                        </span>
                                    </div>
                                    <span v-for="category in recipe.categories" :key="category.id" class="fridge-recipe-tag">
                                        {{ category.name }}
                                    </span>
                                </div>
                            </article>
                        </NuxtLink>
                    </div>
                </div>
            </section>
        </div>
    </main>
</template>

<style scoped>
.fridge-search-page {
    padding: 48px 0 70px;
}

.fridge-search-card {
    max-width: 900px;
    margin: 0 auto;
    padding: clamp(20px, 4vw, 40px);
    border: 1px solid var(--site-border, #ebebeb);
    border-radius: 16px;
    background: var(--site-surface, #fff);
}

.fridge-search-intro,
.fridge-ingredients-heading p,
.fridge-search-message {
    color: var(--site-text, #51545f);
}

.fridge-search-intro {
    margin-bottom: 28px;
}

.fridge-search-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.fridge-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 0;
    margin: 0;
    color: var(--site-heading, #232d37);
    font-size: 14px;
    font-weight: 600;
}

.fridge-field :deep(button),
.fridge-field :deep(input) {
    min-height: 50px;
    border-color: var(--site-border, #ebebeb);
    border-radius: 4px;
    color: var(--site-text, #51545f);
    font: inherit;
}

.fridge-field :deep(button:focus-visible),
.fridge-field :deep(input:focus-visible) {
    border-color: var(--site-accent, #fc6c3f);
    outline: 2px solid rgb(252 108 63 / 20%);
    outline-offset: 1px;
}

.fridge-field-error {
    display: block;
    margin-top: 6px;
    color: #dc2626;
    font-size: 13px;
    font-weight: 400;
}

.fridge-field-hint {
    display: block;
    margin-top: 6px;
    color: #888;
    font-size: 13px;
    font-weight: 400;
}

.fridge-ingredients-section {
    margin: 24px 0;
}

.fridge-ingredients-heading h3 {
    margin: 0;
    color: var(--site-heading, #232d37);
    font-size: 18px;
    font-weight: 700;
}

.fridge-ingredients-heading p {
    color: #888;
    font-size: 13px;
}

.fridge-ingredients-heading p {
    margin: 4px 0 16px;
}

.fridge-results h3 {
    margin-bottom: 22px;
    color: var(--site-heading, #232d37);
    font-size: 20px;
    font-weight: 700;
}

.fridge-ingredient-list {
    display: flex;
    flex-direction: column;
}

.fridge-ingredient-row {
    display: grid;
    grid-template-columns: minmax(180px, 1fr) minmax(100px, 0.35fr) minmax(130px, 0.55fr) 40px;
    align-items: start;
    gap: 12px;
    margin-bottom: 14px;
    padding: 16px;
    border: 1px solid var(--site-border, #ebebeb);
    border-radius: 8px;
    background: #faf9f8;
}

.fridge-ingredient-row :deep(button:not(.fridge-remove-button)),
.fridge-ingredient-row :deep(input) {
    min-height: 50px;
    border-color: var(--site-border, #ebebeb);
    border-radius: 4px;
    color: var(--site-text, #51545f);
    font: inherit;
}

.fridge-ingredient-row :deep(button:not(.fridge-remove-button):focus-visible),
.fridge-ingredient-row :deep(input:focus-visible) {
    border-color: var(--site-accent, #fc6c3f);
    outline: 2px solid rgb(252 108 63 / 20%);
    outline-offset: 1px;
}

.fridge-remove-button,
.fridge-add-button,
.fridge-submit-button {
    min-height: 42px;
    border-radius: 30px;
    font: inherit;
    cursor: pointer;
    transition: color 160ms ease, background-color 160ms ease, border-color 160ms ease;
}

.fridge-remove-button {
    align-self: start;
    width: 36px;
    min-height: 42px;
    margin-top: 30px;
    padding: 0;
    border: 1px solid var(--site-border, #ebebeb);
    background: var(--site-surface, #fff);
    color: var(--site-text, #51545f);
}

.fridge-remove-button:hover {
    border-color: var(--site-accent, #fc6c3f);
    color: var(--site-accent, #fc6c3f);
}

.fridge-remove-button:focus-visible,
.fridge-add-button:focus-visible {
    outline: 2px solid rgb(252 108 63 / 25%);
    outline-offset: 2px;
}

.fridge-remove-button svg,
.fridge-add-button svg {
    width: 18px;
    height: 18px;
    flex: 0 0 auto;
    stroke: currentColor;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 2;
}

.fridge-add-button {
    align-self: flex-start;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 42px;
    padding: 0 16px;
    border: 1px solid var(--site-accent, #fc6c3f);
    background: var(--site-surface, #fff);
    color: var(--site-accent, #fc6c3f);
    font-weight: 600;
}

.fridge-add-button:disabled {
    cursor: not-allowed;
    opacity: 0.55;
}

.fridge-add-button:not(:disabled):hover {
    background: var(--site-accent, #fc6c3f);
    color: #fff;
}

.fridge-submit-button {
    align-self: flex-start;
    min-width: 200px;
    padding: 0 24px;
    border: 1px solid var(--site-accent, #fc6c3f);
    background: var(--site-accent, #fc6c3f);
    color: #fff;
    font-weight: 700;
}

.fridge-submit-button:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

.fridge-submit-button:not(:disabled):hover {
    border-color: #d43f10;
    background: #d43f10;
}

.fridge-search-message {
    margin: 0;
    text-align: center;
}

.fridge-search-message.is-error {
    color: #b42318;
}

.fridge-results {
    max-width: 1100px;
    margin: 40px auto 0;
}

.fridge-results h3 {
    margin-bottom: 22px;
}

.fridge-recipe-link {
    display: block;
    height: calc(100% - 24px);
    margin-bottom: 24px;
    color: inherit;
    text-decoration: none;
}

.fridge-recipe-card {
    height: 100%;
    overflow: hidden;
    border: 1px solid var(--site-border, #ebebeb);
    border-radius: 8px;
    background: var(--site-surface, #fff);
    transition: transform 160ms ease, box-shadow 160ms ease;
}

.fridge-recipe-link:hover .fridge-recipe-card {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgb(35 45 55 / 10%);
}

.fridge-recipe-card img {
    width: 100%;
    aspect-ratio: 4 / 3;
    object-fit: cover;
}

.fridge-recipe-content {
    padding: 18px;
}

.fridge-recipe-content h4 {
    color: var(--site-heading, #232d37);
    font-size: 18px;
    font-weight: 700;
}

.fridge-recipe-content p {
    color: var(--site-text, #51545f);
}

.fridge-recipe-products {
    margin-bottom: 12px;
}

.fridge-recipe-missing {
    margin-bottom: 12px;
}

.fridge-recipe-missing-label {
    display: block;
    margin-bottom: 6px;
    color: #b84b24;
    font-size: 13px;
    font-weight: 600;
}

.fridge-recipe-missing-product {
    display: inline-block;
    margin: 0 6px 6px 0;
    padding: 4px 9px;
    border-radius: 14px;
    background: #fff0eb;
    color: #b84b24;
    font-size: 12px;
}

.fridge-recipe-products-label {
    display: block;
    margin-bottom: 6px;
    color: var(--site-heading, #232d37);
    font-size: 13px;
    font-weight: 600;
}

.fridge-recipe-product {
    display: inline-block;
    margin: 0 6px 6px 0;
    padding: 4px 9px;
    border-radius: 14px;
    background: #f5f3f1;
    color: var(--site-text, #51545f);
    font-size: 12px;
}

.fridge-recipe-tag {
    display: inline-block;
    margin: 0 6px 6px 0;
    padding: 4px 9px;
    border-radius: 14px;
    background: #fff0eb;
    color: var(--site-accent, #fc6c3f);
    font-size: 12px;
}

@media (max-width: 640px) {
    .fridge-ingredient-row {
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) 36px;
    }

    .fridge-product-field {
        grid-column: 1 / -1;
    }

    .fridge-unit-field {
        grid-column: 2;
        grid-row: 2;
    }

    .fridge-remove-button {
        grid-column: 3;
        grid-row: 2;
        margin-top: 30px;
    }
}

@media (max-width: 760px) {
    .fridge-submit-button {
        width: 100%;
    }
}
</style>
