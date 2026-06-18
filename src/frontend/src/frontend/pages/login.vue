<template>
    <Preloader />
    <Header />

    <div class="breadcumb-area">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-12">
                    <div class="bradcumb-title text-center">
                        <h2>Login</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="breadcumb-nav">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <NuxtLink to="/">
                                    <i class="fa fa-home" aria-hidden="true"></i>
                                    Home
                                </NuxtLink>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Login</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <section class="contact-area section_padding_80">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <div class="contact-form wow fadeInUpBig">
                        <h2 class="contact-form-title mb-30">Welcome Back</h2>

                        <div v-if="errorMessage" class="status-message status-error" role="alert">
                            {{ errorMessage }}
                        </div>

                        <form @submit.prevent="handleLogin">
                            <div class="form-group">
                                <input
                                    id="login-email"
                                    v-model="form.email"
                                    type="email"
                                    class="form-control"
                                    placeholder="Email"
                                    autocomplete="email"
                                    required
                                >
                                <small v-if="fieldError('email')" class="field-error">{{ fieldError('email') }}</small>
                            </div>

                            <div class="form-group">
                                <input
                                    id="login-password"
                                    v-model="form.password"
                                    type="password"
                                    class="form-control"
                                    placeholder="Password"
                                    autocomplete="current-password"
                                    required
                                >
                                <small v-if="fieldError('password')" class="field-error">{{ fieldError('password') }}</small>
                            </div>

                            <div class="form-group auth-options">
                                <label class="remember-option" for="login-remember">
                                    <input
                                        id="login-remember"
                                        v-model="form.remember"
                                        type="checkbox"
                                    >
                                    Remember me
                                </label>

                                <NuxtLink to="/register" class="auth-link">
                                    Create account
                                </NuxtLink>
                            </div>

                            <button type="submit" class="btn contact-btn" :disabled="isSubmitting">
                                {{ isSubmitting ? 'Signing in...' : 'Login' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</template>

<script setup lang="ts">
import Preloader from '@/app/components/layout/Preloader.vue'
import Header from '@/app/components/layout/Header.vue'
import Footer from '@/app/components/layout/Footer.vue'
import { useAuth } from '@/app/composables/useAuth'

definePageMeta({
    middleware: 'guest',
})

type LoginForm = {
    email: string
    password: string
    remember: boolean
}

const form = reactive<LoginForm>({
    email: '',
    password: '',
    remember: false,
})

const validationErrors = ref<Record<string, string[]>>({})
const errorMessage = ref('')
const isSubmitting = ref(false)
const { login } = useAuth()

const fieldError = (field: string): string => {
    return validationErrors.value[field]?.[0] ?? ''
}

const handleLogin = async () => {
    isSubmitting.value = true
    errorMessage.value = ''
    validationErrors.value = {}

    try {
        await login({
            email: form.email,
            password: form.password,
            remember: form.remember,
        })

        await navigateTo('/')
    } catch (error: any) {
        console.error('Login failed:', error)
        validationErrors.value = error?.data?.errors ?? {}
        const responseStatus = error?.status ?? error?.statusCode ?? error?.response?.status
        const status = responseStatus ? ` (status ${responseStatus})` : ''
        errorMessage.value = error?.data?.message ?? `Unable to login right now${status}.`
    } finally {
        isSubmitting.value = false
    }
}
</script>

<style scoped>
.status-message {
    margin-bottom: 18px;
    padding: 12px 14px;
    border-radius: 6px;
    font-size: 14px;
    line-height: 1.4;
}

.status-error {
    border: 1px solid #efb5b5;
    background: #fff2f2;
    color: #b42525;
}

.field-error {
    display: block;
    margin-top: 6px;
    color: #b42525;
    font-size: 13px;
}

.auth-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.remember-option {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 0;
    color: #51545f;
    font-size: 14px;
}

.auth-link {
    color: #fc6c3f;
    font-size: 14px;
}

.contact-btn[disabled] {
    cursor: not-allowed;
    opacity: 0.75;
}
</style>
