<script setup>
const baseUrl = 'http://nginx';

const { data, pending, error } = await useFetch('/api/ping', {
    baseURL: baseUrl,
    headers: {
        'Accept': 'application/json'
    }
});

// Для отладки в консоли браузера
console.log('Data:', data.value);
console.log('Error:', error.value);
</script>

<template>
    <div>
        <div v-if="pending">Загрузка...</div>
        <div v-else-if="error">
            <p>Ошибка запроса:</p>
            <pre>{{ error }}</pre>
        </div>
        <pre v-else>{{ data }}</pre>
    </div>
</template>
