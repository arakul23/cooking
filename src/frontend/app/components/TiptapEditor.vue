<template>
    <div class="tiptap-editor-container" v-if="editor">
        <!-- Панель инструментов (Menu Bar) -->
        <div class="editor-toolbar">
            <!-- Жирный -->
            <button
                type="button"
                @click="editor.chain().focus().toggleBold().run()"
                :class="{ 'is-active': editor.isActive('bold') }"
                :title="$t('editor_bold')"
            >
                <b>B</b>
            </button>

            <!-- Курсив -->
            <button
                type="button"
                @click="editor.chain().focus().toggleItalic().run()"
                :class="{ 'is-active': editor.isActive('italic') }"
                :title="$t('editor_italic')"
            >
                <i>I</i>
            </button>

            <span class="divider"></span>

            <!-- Заголовок 2 -->
            <button
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                :class="{ 'is-active': editor.isActive('heading', { level: 2 }) }"
                :title="$t('editor_heading')"
            >
                H2
            </button>

            <!-- Маркированный список -->
            <button
                type="button"
                @click="editor.chain().focus().toggleBulletList().run()"
                :class="{ 'is-active': editor.isActive('bulletList') }"
                :title="$t('editor_bullet_list')"
            >
                • {{ $t('editor_bullet_list') }}
            </button>

            <!-- Цитата -->
            <button
                type="button"
                @click="editor.chain().focus().toggleBlockquote().run()"
                :class="{ 'is-active': editor.isActive('blockquote') }"
                :title="$t('editor_quote')"
            >
                ” {{ $t('editor_quote') }}
            </button>

            <span class="divider"></span>

            <!-- Очистить форматирование -->
            <button
                type="button"
                @click="editor.chain().focus().unsetAllMarks().clearNodes().run()"
                :title="$t('editor_clear_format')"
            >
                {{ $t('editor_clear_format') }}
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
    setup() {
        const { t } = useI18n()

        return { t }
    },
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

<style scoped>
.tiptap-editor-container {
    width: 100%;
    overflow: hidden;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    background: #fff;
}

.editor-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 10px;
    border-bottom: 1px solid #e5e5e5;
    background: #faf9f8;
}

.editor-toolbar button {
    min-height: 36px;
    padding: 6px 10px;
    border: 1px solid #e5e5e5;
    border-radius: 5px;
    background: #fff;
    color: #51545f;
    cursor: pointer;
}

.editor-toolbar button:hover,
.editor-toolbar button.is-active {
    border-color: #fc6c3f;
    color: #fc6c3f;
}

.editor-toolbar .divider {
    align-self: stretch;
    border-left: 1px solid #ddd;
}

.tiptap-editor-container :deep(.ProseMirror) {
    min-height: 200px;
    padding: 16px;
    overflow-wrap: anywhere;
    outline: none;
}

@media (max-width: 480px) {
    .editor-toolbar {
        gap: 6px;
        padding: 8px;
    }

    .editor-toolbar button {
        padding: 6px 8px;
    }
}
</style>
