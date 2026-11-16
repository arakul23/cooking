<template>
    <AccountShell :title="$t('account_home_title')">
        <div class="account-section">
            <h2>{{ $t('account_home_title') }}</h2>
            <p>
                {{ $t('account_home_description') }}
            </p>
        </div>
    </AccountShell>
</template>

<script setup lang="ts">
import AccountShell from '~/components/account/AccountShell.vue'

definePageMeta({
    middleware: 'auth',
})
</script>

<style scoped>
.account-section h2 {
    margin-bottom: 12px;
    color: #232d37;
    font-size: 24px;
}

.account-section p {
    margin-bottom: 0;
    color: #51545f;
    line-height: 1.7;
}
</style>
