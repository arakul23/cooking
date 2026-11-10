<script setup lang="ts">
import TiptapEditor from "../../TiptapEditor.vue";
import {createRecipeSchema} from "../../../composables/useRecipeValidation";
import type {FormSubmitEvent} from '@nuxt/ui';
import {ref, reactive, computed, onBeforeUnmount} from 'vue';

const config = useRuntimeConfig();
const { t, locale } = useI18n()
const recipeSchema = computed(() => createRecipeSchema(t))

const recipe = reactive({
    title: '',
    description: '',
    content: '',
    categories: [] as number[],
    ingredients: [{ rowKey: 0, product_id: null as number | null, amount: 1 as number | null, unit_id: null as number | null }],
    total_time_minutes: null as number | null,
    portions: null as number | null,
    calories: null as number | null,
    logo: null as File | null,
});

const finalTranscript = ref('')
const isListening = ref(false)
const isSupported = ref(false)

let recognition: any = null

if (import.meta.client) {
    const SpeechRecognitionCtor =
        (window as any).SpeechRecognition || (window as any).webkitSpeechRecognition

    isSupported.value = !!SpeechRecognitionCtor

    if (isSupported.value) {
        recognition = new SpeechRecognitionCtor()
        recognition.lang = locale.value === 'uk' ? 'uk-UA' : 'en-US'
        recognition.continuous = true
        recognition.interimResults = true

        recognition.onend = () => {
            if (isListening.value) {
                recognition.start()
            }
        }

        recognition.onresult = (event: any) => {
            let interimTranscript = ''

            for (let i = event.resultIndex; i < event.results.length; i++) {
                const chunk = event.results[i][0].transcript
                if (event.results[i].isFinal) {
                    finalTranscript.value += chunk + ' '
                } else {
                    interimTranscript += chunk
                }
            }

            recipe.content = finalTranscript.value + interimTranscript
        }

        recognition.onerror = (e: any) => {
            console.error('SpeechRecognition error:', e.error)
        }
    }
}

function toggleListening() {
    if (!recognition) return

    if (isListening.value) {
        isListening.value = false
        recognition.stop()
    } else {
        isListening.value = true
        finalTranscript.value = recipe.content ? recipe.content.trim() + ' ' : ''
        recognition.start()
    }
}

type CategoryItem = {
    id: number;
    name: string;
    image: string | null;
};

const {data, error} = await useFetch<{ data: CategoryItem[] }>(
    `${config.public.apiBase}/categories`
);

if (error.value) {
    console.error('Failed to load categories:', error.value);
}

const categories = computed(() => data.value?.data ?? []);

type ProductItem = {
    id: number;
    name: string;
};

type UnitItem = {
    id: number;
    name: string;
    code: string;
};

const ingredientOptionsUrl = computed(() =>
    `${config.public.apiBase}/recipes/fridge-search/options?locale=${encodeURIComponent(locale.value)}`,
);
const { data: ingredientOptions, error: ingredientOptionsError } = await useFetch<{
    units: UnitItem[];
}>(ingredientOptionsUrl, { watch: [locale] });
const { data: initialProducts, error: initialProductsError } = await useFetch<{ data: ProductItem[] }>(
    `${config.public.apiBase}/recipes/fridge-search/products`,
);
const unitOptions = computed(() =>
    (ingredientOptions.value?.units ?? []).map(unit => ({ ...unit, label: unit.name })),
);
const defaultProductOptions = computed(() => initialProducts.value?.data ?? []);
const productSearchTerms = reactive<Record<number, string>>({});
const productSearchResults = reactive<Record<number, ProductItem[]>>({});
const productSearchLoading = reactive<Record<number, boolean>>({});
const productSearchErrors = reactive<Record<number, boolean>>({});
const knownProductOptions = reactive<Record<number, ProductItem>>({});
const productSearchTimers = new Map<number, ReturnType<typeof setTimeout>>();
const productSearchRequests = new Map<number, number>();
let nextIngredientKey = 1;

const rememberProductOptions = (products: ProductItem[]) => {
    products.forEach(product => {
        knownProductOptions[product.id] = product;
    });
};

rememberProductOptions(defaultProductOptions.value);
productSearchTerms[0] = '';
productSearchResults[0] = defaultProductOptions.value;
productSearchErrors[0] = Boolean(initialProductsError.value);

const productOptionsFor = (rowKey: number, productId: number | null) => {
    const options = productSearchResults[rowKey] ?? [];
    const selectedProduct = productId === null ? undefined : knownProductOptions[productId];
    const availableOptions = selectedProduct && !options.some(product => product.id === selectedProduct.id)
        ? [...options, selectedProduct]
        : options;

    return availableOptions.filter(product =>
        !recipe.ingredients.some(ingredient =>
            ingredient.rowKey !== rowKey && ingredient.product_id === product.id
        ) || product.id === productId
    );
};

const searchProducts = (rowKey: number, search: string) => {
    const term = search.trim();
    const previousTimer = productSearchTimers.get(rowKey);
    if (previousTimer) clearTimeout(previousTimer);

    if (term.length < 2) {
        productSearchRequests.set(rowKey, (productSearchRequests.get(rowKey) ?? 0) + 1);
        productSearchResults[rowKey] = term ? [] : defaultProductOptions.value;
        productSearchLoading[rowKey] = false;
        productSearchErrors[rowKey] = false;
        return;
    }

    productSearchLoading[rowKey] = true;
    productSearchErrors[rowKey] = false;
    const requestId = (productSearchRequests.get(rowKey) ?? 0) + 1;
    productSearchRequests.set(rowKey, requestId);
    productSearchTimers.set(rowKey, setTimeout(async () => {
        try {
            const response = await $fetch<{ data: ProductItem[] }>(
                `${config.public.apiBase}/recipes/fridge-search/products`,
                { query: { search: term } },
            );
            if (productSearchRequests.get(rowKey) === requestId) {
                productSearchResults[rowKey] = response.data;
                rememberProductOptions(response.data);
            }
        } catch {
            if (productSearchRequests.get(rowKey) === requestId) {
                productSearchErrors[rowKey] = true;
                productSearchResults[rowKey] = [];
            }
        } finally {
            if (productSearchRequests.get(rowKey) === requestId) {
                productSearchLoading[rowKey] = false;
            }
        }
    }, 250));
};

const addIngredient = () => {
    const rowKey = nextIngredientKey++;
    recipe.ingredients.push({ rowKey, product_id: null, amount: 1, unit_id: null });
    productSearchTerms[rowKey] = '';
    productSearchResults[rowKey] = defaultProductOptions.value;
};

const removeIngredient = (rowKey: number) => {
    recipe.ingredients = recipe.ingredients.filter(ingredient => ingredient.rowKey !== rowKey);
    const timer = productSearchTimers.get(rowKey);
    if (timer) clearTimeout(timer);
    productSearchTimers.delete(rowKey);
    productSearchRequests.delete(rowKey);
    delete productSearchTerms[rowKey];
    delete productSearchResults[rowKey];
    delete productSearchLoading[rowKey];
    delete productSearchErrors[rowKey];
};

onBeforeUnmount(() => {
    productSearchTimers.forEach(timer => clearTimeout(timer));
});

const emit = defineEmits<{
    create: [recipe: any];
}>();

async function onSubmit(event: FormSubmitEvent<typeof recipe>) {
    emit('create', event.data);
}
</script>

<template>
    <UForm class="recipe-form" :schema="recipeSchema" :state="recipe" @submit="onSubmit">
        <UFormField :label="$t('recipe_field_title')" name="title" class="form-group">
            <UInput v-model="recipe.title" class="w-full" size="xl"/>
        </UFormField>

        <div class="row-2">
            <UFormField :label="$t('recipe_field_categories')" name="categories" class="form-group">
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

            <UFormField :label="$t('recipe_field_calories')" name="calories" class="form-group">
                <UInputNumber v-model="recipe.calories" color="neutral" variant="outline" class="w-full" size="xl"/>
            </UFormField>
        </div>

        <section class="ingredients-section">
            <div class="ingredients-heading">
                <h3>{{ $t('recipe_field_ingredients') }}</h3>
                <p>{{ $t('recipe_ingredients_description') }}</p>
            </div>
            <p v-if="ingredientOptionsError || initialProductsError" class="ingredients-error" role="alert">
                {{ $t('recipe_ingredients_load_error') }}
            </p>
            <div
                v-for="(ingredient, index) in recipe.ingredients"
                :key="ingredient.rowKey"
                class="ingredient-row"
            >
                <UFormField
                    :label="$t('recipe_ingredient_product')"
                    :name="`ingredients.${index}.product_id`"
                    class="form-group"
                >
                    <USelectMenu
                        v-model="ingredient.product_id"
                        :items="productOptionsFor(ingredient.rowKey, ingredient.product_id)"
                        label-key="name"
                        color="neutral"
                        variant="outline"
                        value-key="id"
                        :placeholder="$t('recipe_ingredient_choose_product')"
                        v-model:search-term="productSearchTerms[ingredient.rowKey]"
                        :search-input="{
                            placeholder: $t('recipe_ingredient_choose_product'),
                            loading: productSearchLoading[ingredient.rowKey],
                        }"
                        ignore-filter
                        class="w-full"
                        size="xl"
                        @update:search-term="searchProducts(ingredient.rowKey, $event)"
                    />
                    <span v-if="productSearchErrors[ingredient.rowKey]" class="ingredients-error">
                        {{ $t('recipe_ingredients_load_error') }}
                    </span>
                    <span
                        v-else-if="(productSearchTerms[ingredient.rowKey] ?? '').trim().length < 2"
                        class="ingredients-hint"
                    >
                        {{ $t('recipe_ingredient_search_hint') }}
                    </span>
                    <span
                        v-else-if="!productSearchLoading[ingredient.rowKey] && !productOptionsFor(ingredient.rowKey, ingredient.product_id).length"
                        class="ingredients-hint"
                    >
                        {{ $t('recipe_ingredient_no_products') }}
                    </span>
                </UFormField>
                <UFormField
                    :label="$t('recipe_ingredient_amount')"
                    :name="`ingredients.${index}.amount`"
                    class="form-group ingredient-amount"
                >
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
                </UFormField>
                <UFormField
                    :label="$t('recipe_ingredient_unit')"
                    :name="`ingredients.${index}.unit_id`"
                    class="form-group ingredient-unit"
                >
                    <USelectMenu
                        v-model="ingredient.unit_id"
                        :items="unitOptions"
                        label-key="label"
                        color="neutral"
                        variant="outline"
                        value-key="id"
                        :placeholder="$t('recipe_ingredient_choose_unit')"
                        class="w-full"
                        size="xl"
                    />
                </UFormField>
                <button
                    v-if="recipe.ingredients.length > 1"
                    type="button"
                    class="remove-ingredient"
                    :aria-label="$t('recipe_ingredient_remove')"
                    :title="$t('recipe_ingredient_remove')"
                    @click="removeIngredient(ingredient.rowKey)"
                >
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
                        <path d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>
            </div>
            <button
                type="button"
                class="add-ingredient"
                :disabled="recipe.ingredients.length >= 30"
                @click="addIngredient"
            >
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                {{ $t('recipe_ingredient_add') }}
            </button>
        </section>

        <div class="row-2">
            <UFormField :label="$t('recipe_field_cooking_time')" name="total_time_minutes" class="form-group">
                <UInputNumber v-model="recipe.total_time_minutes" color="neutral" variant="outline" class="w-full"
                              size="xl" placeholder="45"/>
            </UFormField>
            <UFormField :label="$t('recipe_field_portions')" name="portions" class="form-group">
                <UInputNumber v-model="recipe.portions" color="neutral" variant="outline" class="w-full" size="xl"/>
            </UFormField>
        </div>

        <UFormField :label="$t('recipe_field_description')" name="description" class="form-group">
            <UTextarea v-model="recipe.description" color="neutral" variant="outline" class="w-full" size="xl"/>
        </UFormField>

        <client-only>
            <UFormField :label="$t('recipe_field_instructions')" name="content" class="form-group">
                <TiptapEditor v-model="recipe.content"/>
            </UFormField>

            <template #fallback>
                <div class="form-group">
                    <label>{{ $t('recipe_field_instructions') }}</label>
                    <div style="height: 200px; background: #f5f5f8; border-radius: 8px;"/>
                </div>
            </template>

            <button
                v-if="isSupported"
                type="button"
                @click="toggleListening"
                class="mt-2 flex items-center gap-2 px-3 py-1 rounded"
                :class="isListening ? 'bg-red-500 text-white' : 'bg-gray-200'"
            >
                <span v-if="isListening">🔴 {{ $t('recipe_voice_stop') }}</span>
                <span v-else>🎤 {{ $t('recipe_voice_start') }}</span>
            </button>

            <p v-else class="text-sm text-gray-500 mt-2">
                {{ $t('recipe_voice_unsupported') }}
            </p>
        </client-only>

        <UFormField :label="$t('recipe_field_photo')" name="logo" class="form-group">
            <UFileUpload
                v-model="recipe.logo"
                :label="$t('recipe_photo_upload_label')"
                :description="$t('recipe_photo_upload_description')"
                class="recipe-photo-upload min-h-48"
            />
        </UFormField>

        <div class="form-actions">
            <button type="button" class="btn btn-outline">{{ $t('cancel_btn') }}</button>
            <button type="submit" class="btn btn-primary">{{ $t('create_recipe_btn') }}</button>
        </div>
    </UForm>
</template>

<style scoped>
.row-2 {
    display: flex;
    gap: 20px;
}

.ingredients-section {
    margin: 24px 0;
}

.ingredients-heading h3 {
    margin: 0;
    color: var(--site-heading);
    font-size: 18px;
    font-weight: 700;
}

.ingredients-heading p,
.ingredients-hint {
    color: #888;
    font-size: 13px;
}

.ingredients-heading p {
    margin: 4px 0 16px;
}

.ingredient-row {
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

.ingredient-row .form-group {
    min-width: 0;
}

.ingredient-row :deep(button:not(.remove-ingredient)),
.ingredient-row :deep(input) {
    min-height: 50px;
    border-color: var(--site-border, #ebebeb);
    border-radius: 4px;
    color: var(--site-text, #51545f);
    font: inherit;
}

.ingredient-row :deep(button:not(.remove-ingredient):focus-visible),
.ingredient-row :deep(input:focus-visible) {
    border-color: var(--site-accent, #fc6c3f);
    outline: 2px solid rgb(252 108 63 / 20%);
    outline-offset: 1px;
}

.ingredients-error {
    display: block;
    margin-top: 6px;
    color: #dc2626;
    font-size: 13px;
}

.ingredients-hint {
    display: block;
    margin-top: 6px;
}

.remove-ingredient,
.add-ingredient {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 42px;
    border-radius: 30px;
    font: inherit;
    cursor: pointer;
    transition: color 160ms ease, background-color 160ms ease, border-color 160ms ease;
}

.remove-ingredient {
    align-self: start;
    width: 36px;
    min-height: 42px;
    margin-top: 30px;
    padding: 0;
    border: 1px solid var(--site-border, #ebebeb);
    background: var(--site-surface, #fff);
    color: var(--site-text, #51545f);
}

.remove-ingredient:hover {
    border-color: var(--site-accent, #fc6c3f);
    color: var(--site-accent, #fc6c3f);
}

.remove-ingredient:focus-visible,
.add-ingredient:focus-visible {
    outline: 2px solid rgb(252 108 63 / 25%);
    outline-offset: 2px;
}

.remove-ingredient svg,
.add-ingredient svg {
    width: 18px;
    height: 18px;
    flex: 0 0 auto;
    stroke: currentColor;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 2;
}

.add-ingredient {
    align-self: flex-start;
    gap: 8px;
    padding: 0 16px;
    border: 1px solid var(--site-accent, #fc6c3f);
    background: var(--site-surface, #fff);
    color: var(--site-accent, #fc6c3f);
    font-weight: 600;
}

.add-ingredient:disabled {
    cursor: not-allowed;
    opacity: 0.55;
}

.add-ingredient:not(:disabled):hover {
    background: var(--site-accent, #fc6c3f);
    color: #fff;
}

.row-2 .form-group {
    flex: 1;
    min-width: 0;
}

.recipe-photo-upload {
    width: min(100%, 24rem);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 10px;
    border-top: 1px solid var(--site-border);
    padding-top: 30px;
}

.recipe-form .btn {
    display: inline-block;
    border-radius: 30px;
    min-height: 44px;
    padding: 0 28px;
    font-size: 13px;
    font-weight: 500;
    text-transform: uppercase;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all .3s ease;
    letter-spacing: .5px;
}

.recipe-form .btn-primary {
    background-color: var(--site-accent);
    color: #fff;
}

.recipe-form .btn-primary:hover {
    background-color: #d43f10;
}

.recipe-form .btn-outline {
    background-color: transparent;
    color: #b5aeba;
    border: 1px solid #ebebeb;
}

.recipe-form .btn-outline:hover {
    color: #000;
    border-color: #000;
}

@media (max-width: 640px) {
    .row-2 {
        flex-direction: column;
        gap: 0;
    }

    .ingredient-row {
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) 36px;
    }

    .ingredient-row .form-group:first-child {
        grid-column: 1 / -1;
    }

    .remove-ingredient {
        grid-column: 3;
        grid-row: 2;
        margin-top: 30px;
    }

    .form-actions {
        flex-direction: column-reverse;
    }

    .form-actions .btn {
        width: 100%;
    }
}
</style>
