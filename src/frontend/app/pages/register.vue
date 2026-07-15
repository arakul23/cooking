<template>
    <Preloader />
    <Header />

    <div class="breadcumb-area">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-12">
                    <div class="bradcumb-title text-center">
                        <h2>Create Account</h2>
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
                            <li class="breadcrumb-item active" aria-current="page">Register</li>
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
                        <h2 class="contact-form-title mb-30">Join Yummy Blog</h2>

                        <div v-if="successMessage" class="status-message status-success" role="status" aria-live="polite">
                            {{ successMessage }}
                        </div>
                        <div v-if="errorMessage" class="status-message status-error" role="alert">
                            {{ errorMessage }}
                        </div>

                        <form @submit.prevent="handleRegister">
                            <div class="form-group">
                                <input
                                    id="register-name"
                                    v-model="form.name"
                                    type="text"
                                    class="form-control"
                                    placeholder="Name"
                                    autocomplete="name"
                                    required
                                >
                                <small v-if="fieldError('name')" class="field-error">{{ fieldError('name') }}</small>
                            </div>

                            <div class="form-group">
                                <input
                                    id="register-email"
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
                                    id="register-password"
                                    v-model="form.password"
                                    type="password"
                                    class="form-control"
                                    placeholder="Password"
                                    autocomplete="new-password"
                                    required
                                >
                                <small v-if="fieldError('password')" class="field-error">{{ fieldError('password') }}</small>
                            </div>

                            <div class="form-group">
                                <input
                                    id="register-password-confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="form-control"
                                    placeholder="Confirm password"
                                    autocomplete="new-password"
                                    required
                                >
                            </div>

                            <button type="submit" class="btn contact-btn" :disabled="isSubmitting">
                                {{ isSubmitting ? 'Creating account...' : 'Create account' }}
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

type RegisterForm = {
    name: string
    email: string
    password: string
    password_confirmation: string
}

const form = reactive<RegisterForm>({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const validationErrors = ref<Record<string, string[]>>({})
const errorMessage = ref('')
const successMessage = ref('')
const isSubmitting = ref(false)
const { register } = useAuth()

const fieldError = (field: string): string => {
    return validationErrors.value[field]?.[0] ?? ''
}

const handleRegister = async () => {
    isSubmitting.value = true
    errorMessage.value = ''
    successMessage.value = ''
    validationErrors.value = {}

    try {
        await register({
            name: form.name,
            email: form.email,
            password: form.password,
            password_confirmation: form.password_confirmation,
        })

        successMessage.value = 'Registration completed successfully.'
        form.name = ''
        form.email = ''
        form.password = ''
        form.password_confirmation = ''
        await navigateTo('/')
    } catch (error: any) {
        console.error('Register failed:', error)
        validationErrors.value = error?.data?.errors ?? {}
        const responseStatus = error?.status ?? error?.statusCode ?? error?.response?.status
        const status = responseStatus ? ` (status ${responseStatus})` : ''
        errorMessage.value = error?.data?.message ?? `Unable to register right now${status}.`
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

.status-success {
    border: 1px solid #a7e5b4;
    background: #ecfdf2;
    color: #1f7a35;
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

.auth-hint {
    margin-top: 20px;
}

.contact-btn[disabled] {
    cursor: not-allowed;
    opacity: 0.75;
}
</style>
