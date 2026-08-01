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

</style>
