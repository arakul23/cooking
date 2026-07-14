<template>
    <div class="tiptap-editor-container" v-if="editor">
        <!-- Панель инструментов (Menu Bar) -->
        <div class="editor-toolbar">
            <!-- Жирный -->
            <button
                type="button"
                @click="editor.chain().focus().toggleBold().run()"
                :class="{ 'is-active': editor.isActive('bold') }"
                title="Жирный"
            >
                <b>B</b>
            </button>

            <!-- Курсив -->
            <button
                type="button"
                @click="editor.chain().focus().toggleItalic().run()"
                :class="{ 'is-active': editor.isActive('italic') }"
                title="Курсив"
            >
                <i>I</i>
            </button>

            <span class="divider"></span>

            <!-- Заголовок 2 -->
            <button
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                :class="{ 'is-active': editor.isActive('heading', { level: 2 }) }"
                title="Заголовок"
            >
                H2
            </button>

            <!-- Маркированный список -->
            <button
                type="button"
                @click="editor.chain().focus().toggleBulletList().run()"
                :class="{ 'is-active': editor.isActive('bulletList') }"
                title="Список"
            >
                • Список
            </button>

            <!-- Цитата -->
            <button
                type="button"
                @click="editor.chain().focus().toggleBlockquote().run()"
                :class="{ 'is-active': editor.isActive('blockquote') }"
                title="Цитата"
            >
                ” Цитата
            </button>

            <span class="divider"></span>

            <!-- Очистить форматирование -->
            <button
                type="button"
                @click="editor.chain().focus().unsetAllMarks().clearNodes().run()"
                title="Очистить формат"
            >
                Clear
            </button>
        </div>

        <!-- Поле самого редактора -->
        <editor-content :editor="editor" />
    </div>
</template>

<script>
import StarterKit from '@tiptap/starter-kit'
import { Editor, EditorContent } from '@tiptap/vue-3'

export default {
    name: 'TiptapEditor',
    components: {
        EditorContent,
    },

    props: {
        modelValue: {
            type: String,
            default: '',
        },
    },

    emits: ['update:modelValue'],

    data() {
        return {
            editor: null,
        }
    },

    watch: {
        modelValue(value) {
            if (!this.editor) return

            const isSame = this.editor.getHTML() === value
            if (isSame) {
                return
            }

            this.editor.commands.setContent(value)
        },
    },

    mounted() {
        this.editor = new Editor({
            extensions: [StarterKit],
            content: this.modelValue,
            autofocus: false,
            editable: true,
            injectCSS: true,
            onUpdate: () => {
                this.$emit('update:modelValue', this.editor.getHTML())
            },
        })
    },

    beforeUnmount() {
        if (this.editor) {
            this.editor.destroy()
        }
    },
}
</script>

<style lang="scss">
/* Контейнер редактора */
.tiptap-editor-container {
    width: 100%;
    border: 1px solid #ebebeb;
    border-radius: 8px;
    background-color: #f5f5f8;
    transition: all .3s ease;
    overflow: hidden; /* Ограничиваем панель радиусом скругления */

    &:focus-within {
        border-color: #fc6c3f;
        background-color: #fff;
    }
}

/* Панель кнопок */
.editor-toolbar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    padding: 8px 12px;
    background-color: #f9f9fb;
    border-bottom: 1px solid #ebebeb;

    button {
        background: transparent;
        border: none;
        color: #51545f;
        padding: 6px 12px;
        font-size: 13px;
        font-weight: 500;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
        font-family: 'Poppins', sans-serif;

        &:hover {
            background-color: #e2e2e9;
            color: #232d37;
        }

        /* Стиль для кнопки, когда эффект (например, Bold) активен */
        &.is-active {
            background-color: #fc6c3f;
            color: #fff;
        }
    }

    .divider {
        width: 1px;
        height: 18px;
        background-color: #ebebeb;
        margin: 0 4px;
    }
}

/* Область ввода текста */
.tiptap {
    min-height: 160px;
    padding: 15px 20px;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    color: #51545f;
    outline: none;

    &:focus {
        outline: none;
    }

    p {
        margin-bottom: 0.5rem;
    }

    ul, ol {
        padding-left: 1.5rem;
        margin-bottom: 1rem;

        li p {
            margin: 2px 0;
        }
    }

    blockquote {
        border-left: 3px solid #fc6c3f;
        margin: 1rem 0;
        padding-left: 1rem;
        color: #7a7f8d;
    }
}
</style>
