<template>
    <div class="breadcumb-area">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-12">
                    <div class="bradcumb-title text-center">
                        <h2>{{ $t('menu_contact') }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="breadcumb-nav">
        <div class="container">
            <nav :aria-label="$t('breadcrumb_navigation')">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <NuxtLink to="/">
                            <i class="fa fa-home" aria-hidden="true"></i>
                            {{ $t('menu_home') }}
                        </NuxtLink>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $t('menu_contact') }}</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- ****** Contatc Area Start ****** -->
    <div class="contact-area section_padding_80">
        <div class="container">
<!--            &lt;!&ndash; Contact Info Area Start &ndash;&gt;
            <div class="contact-info-area section_padding_80_50">
                <div class="row">
                    &lt;!&ndash; Single Contact Info &ndash;&gt;
                    <div class="col-12 col-md-4">
                        <div class="single-contact-info mb-30 text-center wow fadeInUp" data-wow-delay="0.3s">
                            <h4>France</h4>
                            <p>40 Baria Sreet 133/2 NewYork City, US <br> Email: info.contact@gmail.com <br> Phone: 123-456-7890</p>
                        </div>
                    </div>
                    &lt;!&ndash; Single Contact Info &ndash;&gt;
                    <div class="col-12 col-md-4">
                        <div class="single-contact-info mb-30 text-center wow fadeInUp" data-wow-delay="0.6s">
                            <h4>United States</h4>
                            <p>40 Baria Sreet 133/2 NewYork City, US <br> Email: info.contact@gmail.com <br> Phone: 123-456-7890</p>
                        </div>
                    </div>
                    &lt;!&ndash; Single Contact Info &ndash;&gt;
                    <div class="col-12 col-md-4">
                        <div class="single-contact-info mb-30 text-center wow fadeInUp" data-wow-delay="0.9s">
                            <h4>Viet Nam</h4>
                            <p>40 Baria Sreet 133/2 NewYork City, US <br> Email: info.contact@gmail.com <br> Phone: 123-456-7890</p>
                        </div>
                    </div>
                </div>
            </div>-->

            <!-- Contact Form  -->
            <div class="contact-form-area">
                <div v-if="isSuccess" class="success-message" role="status" aria-live="polite">
                    {{ $t('contact_success') }}
                </div>
                <div v-if="errorMessage" class="status-message status-error" role="alert">
                    {{ errorMessage }}
                </div>
                <div class="row">
                    <div class="col-12 item">
                        <div class="contact-form wow fadeInUpBig" data-wow-delay="0.6s">
                            <h2 class="contact-form-title mb-30">{{ $t('contact_heading') }}</h2>
                            <!-- Contact Form -->
                            <form @submit.prevent="handleSubmit">
                                <div class="form-group">
                                    <input v-model="formData.name" type="text" class="form-control" id="contact-name" :placeholder="$t('contact_name')" required>
                                </div>
                                <div class="form-group">
                                    <input v-model="formData.email" type="email" class="form-control" id="contact-email" :placeholder="$t('auth_email')" required>
                                </div>
                                <div class="form-group">
                                    <textarea v-model="formData.message" class="form-control" name="message" id="message" cols="30" rows="10" :placeholder="$t('contact_message')" required></textarea>
                                </div>
                                <button type="submit" class="btn contact-btn" :disabled="isSubmitting">
                                    {{ isSubmitting ? $t('contact_sending') : $t('contact_send') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- ****** Contact Area End ****** -->

    <!-- ****** Instagram Area Start ****** -->
    <div class="instargram_area owl-carousel section_padding_100_0 clearfix" id="portfolio">

        <!-- Instagram Item -->
        <div class="instagram_gallery_item">
            <!-- Instagram Thumb -->
            <img src="https://placehold.co/600x600?text=Temp+Insta" alt="">
            <!-- Hover -->
            <div class="hover_overlay">
                <div class="yummy-table">
                    <div class="yummy-table-cell">
                        <div class="follow-me text-center">
                            <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i> {{ $t('home_follow_me') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instagram Item -->
        <div class="instagram_gallery_item">
            <!-- Instagram Thumb -->
            <img src="https://placehold.co/600x600?text=Temp+Insta" alt="">
            <!-- Hover -->
            <div class="hover_overlay">
                <div class="yummy-table">
                    <div class="yummy-table-cell">
                        <div class="follow-me text-center">
                            <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i> {{ $t('home_follow_me') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instagram Item -->
        <div class="instagram_gallery_item">
            <!-- Instagram Thumb -->
            <img src="https://placehold.co/600x600?text=Temp+Insta" alt="">
            <!-- Hover -->
            <div class="hover_overlay">
                <div class="yummy-table">
                    <div class="yummy-table-cell">
                        <div class="follow-me text-center">
                            <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i> {{ $t('home_follow_me') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instagram Item -->
        <div class="instagram_gallery_item">
            <!-- Instagram Thumb -->
            <img src="https://placehold.co/600x600?text=Temp+Insta" alt="">
            <!-- Hover -->
            <div class="hover_overlay">
                <div class="yummy-table">
                    <div class="yummy-table-cell">
                        <div class="follow-me text-center">
                            <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i> {{ $t('home_follow_me') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instagram Item -->
        <div class="instagram_gallery_item">
            <!-- Instagram Thumb -->
            <img src="https://placehold.co/600x600?text=Temp+Insta" alt="">
            <!-- Hover -->
            <div class="hover_overlay">
                <div class="yummy-table">
                    <div class="yummy-table-cell">
                        <div class="follow-me text-center">
                            <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i> {{ $t('home_follow_me') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instagram Item -->
        <div class="instagram_gallery_item">
            <!-- Instagram Thumb -->
            <img src="https://placehold.co/600x600?text=Temp+Insta" alt="">
            <!-- Hover -->
            <div class="hover_overlay">
                <div class="yummy-table">
                    <div class="yummy-table-cell">
                        <div class="follow-me text-center">
                            <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i> {{ $t('home_follow_me') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instagram Item -->
        <div class="instagram_gallery_item">
            <!-- Instagram Thumb -->
            <img src="https://placehold.co/600x600?text=Temp+Insta" alt="">
            <!-- Hover -->
            <div class="hover_overlay">
                <div class="yummy-table">
                    <div class="yummy-table-cell">
                        <div class="follow-me text-center">
                            <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i> {{ $t('home_follow_me') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instagram Item -->
        <div class="instagram_gallery_item">
            <!-- Instagram Thumb -->
            <img src="https://placehold.co/600x600?text=Temp+Insta" alt="">
            <!-- Hover -->
            <div class="hover_overlay">
                <div class="yummy-table">
                    <div class="yummy-table-cell">
                        <div class="follow-me text-center">
                            <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i> {{ $t('home_follow_me') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- ****** Our Creative Portfolio Area End ****** -->
</template>

<style scoped>
.instagram_gallery_item {
    overflow: hidden;
    aspect-ratio: 1;
}

.instagram_gallery_item > img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.success-message {
    margin-bottom: 24px;
    padding: 16px 20px;
    border: 1px solid #86efac;
    border-left: 4px solid #16a34a;
    border-radius: 8px;
    background: #f0fdf4;
    color: #166534;
    font-size: 16px;
    font-weight: 600;
    line-height: 1.4;
}
</style>

<script setup>
import { ref } from 'vue'
const { t } = useI18n()
const config = useRuntimeConfig()
const isSubmitting = ref(false)
const errorMessage = ref('')
const isSuccess = ref(false)

// Данные формы
const formData = ref({
    name: '',
    email: '',
    message: ''
})

const handleSubmit = async () => {
    isSubmitting.value = true
    errorMessage.value = ''

    const { data, error } = await useFetch(`${config.public.apiBase}/contact/send`, {
        method: 'POST',
        body: formData.value,
    })

    isSubmitting.value = false

    if (error.value) {
        errorMessage.value = t('contact_error')
        console.error('Ошибка бэка:', error.value)
        return
    }

    isSuccess.value = true
    formData.value = { title: '', description: '', category_id: '' }
}
</script>
