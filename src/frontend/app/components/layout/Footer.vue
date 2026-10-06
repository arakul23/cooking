<template>
    <footer class="footer_area">
        <div class="container text-center">
            <NuxtLink to="/" class="yummy-logo">Yummy Blog</NuxtLink>
            <p class="mt-3">
                © {{ new Date().getFullYear() }} All rights reserved
            </p>
        </div>
    </footer>
</template>

<style scoped>
.footer_area .yummy-logo {
    display: inline-block;
    text-decoration: none;
}
</style>
