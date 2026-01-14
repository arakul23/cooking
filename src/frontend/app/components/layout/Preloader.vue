<template>
    <div id="preloader">
        <div class="yummy-load"></div>
    </div>
</template>

<script setup>
onMounted(() => {
    setTimeout(() => {
        document.getElementById('preloader')?.remove()
    }, 500)
})
</script>
