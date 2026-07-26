<template>
    <Preloader/>
    <Header/>

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
                                <NuxtLink to="/src/frontend/public">
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

                        <form @submit.prevent="handleLogin" class="auth-form">
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
                                <small v-if="fieldError('password')" class="field-error">{{
                                        fieldError('password')
                                    }}</small>
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
                        <div>
                            <button type="button" class="google-signin-btn" @click="loginWithGoogle()">
                                <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                                    <path fill="#4285F4"
                                          d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.874 2.684-6.615z"/>
                                    <path fill="#34A853"
                                          d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z"/>
                                    <path fill="#FBBC05"
                                          d="M3.964 10.71A5.41 5.41 0 0 1 3.68 9c0-.593.102-1.17.284-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z"/>
                                    <path fill="#EA4335"
                                          d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z"/>
                                </svg>
                                <span>Continue with Google</span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer/>
</template>

<script setup lang="ts">
import Preloader from '~/components/layout/Preloader.vue'
import Header from '~/components/layout/Header.vue'
import Footer from '~/components/layout/Footer.vue'
import {useAuth} from '~/composables/useAuth'

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
const {login} = useAuth()

const fieldError = (field: string): string => {
    return validationErrors.value[field]?.[0] ?? ''
}

const loginWithGoogle = () => {
    const params = new URLSearchParams({
        client_id: "198694372353-iqi8t0eb53u31spd5eo83g9a40i17rkk.apps.googleusercontent.com",
        redirect_uri: 'http://localhost:3000/auth/google/callback',
        response_type: 'code',
        scope: 'openid profile email',
        access_type: 'offline',
        prompt: 'consent',
    })
    window.location.href = `https://accounts.google.com/o/oauth2/v2/auth?${params}`
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

.google-signin-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #fff;
    color: #3c4043;
    border: 1px solid #dadce0;
    border-radius: 4px;
    padding: 10px 16px;
    font-family: 'Roboto', arial, sans-serif;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
}

.google-signin-btn:hover {
    box-shadow: 0 1px 2px 0 rgba(60, 64, 67, .3), 0 1px 3px 1px rgba(60, 64, 67, .15);
}

.auth-form {
    padding-bottom: 10px;
}
</style>
