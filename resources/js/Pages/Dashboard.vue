<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="page-title">Dashboard</h2>
        </template>

        <div class="page-content">
            <div class="panel">You're logged in!</div>
        </div>
    </AuthenticatedLayout>
</template>
