<template>
    <Preloader />
    <Header />
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
                    Message sent successfully
                </div>
                <div class="row">
                    <div class="col-12 item">
                        <div class="contact-form wow fadeInUpBig" data-wow-delay="0.6s">
                            <h2 class="contact-form-title mb-30">If You Have Any Question Contact Me Today !</h2>
                            <!-- Contact Form -->
                            <form @submit.prevent="handleSubmit">
                                <div class="form-group">
                                    <input v-model="formData.name" type="text" class="form-control" id="contact-name" placeholder="Name" required>
                                </div>
                                <div class="form-group">
                                    <input v-model="formData.email" type="email" class="form-control" id="contact-email" placeholder="Email" required>
                                </div>
                                <div class="form-group">
                                    <textarea v-model="formData.message" class="form-control" name="message" id="message" cols="30" rows="10" placeholder="Message" required></textarea>
                                </div>
                                <button type="submit" class="btn contact-btn">Send Message</button>
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
                            <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i> Follow me</a>
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
                            <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i> Follow me</a>
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
                            <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i> Follow me</a>
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
                            <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i> Follow me</a>
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
                            <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i> Follow me</a>
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
                            <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i> Follow me</a>
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
                            <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i> Follow me</a>
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
                            <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i> Follow me</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- ****** Our Creative Portfolio Area End ****** -->

    <Footer />
</template>

<style scoped>
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
import Preloader from '@/app/components/layout/Preloader.vue'
import Header from '@/app/components/layout/Header.vue'
import Footer from '@/app/components/layout/Footer.vue'
import { ref } from 'vue'
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
        errorMessage.value = error.value.data?.message || 'Ошибка при сохранении'
        console.error('Ошибка бэка:', error.value)
        return
    }

    isSuccess.value = true
    formData.value = { title: '', description: '', category_id: '' }
}
</script>
