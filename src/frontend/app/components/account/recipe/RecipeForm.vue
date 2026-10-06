<script setup lang="ts">
import TiptapEditor from "../../TiptapEditor.vue";
import {createRecipeSchema} from "../../../composables/useRecipeValidation";
import type {FormSubmitEvent} from '@nuxt/ui';
import {ref, reactive, computed} from 'vue';

const config = useRuntimeConfig();
const { t, locale } = useI18n()
const recipeSchema = computed(() => createRecipeSchema(t))

const recipe = reactive({
    title: '',
    description: '',
    content: '',
    categories: [] as CategoryItem[],
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

    .form-actions {
        flex-direction: column-reverse;
    }

    .form-actions .btn {
        width: 100%;
    }
}
</style>
