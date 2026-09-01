<script setup>
import { Head } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import { useAuth } from '@/Composables/useAuth.js';

const { user } = useAuth();
</script>

<template>
    <Head title="Console" />

    <ConsoleLayout>
        <template #title>Dashboard</template>

        <div class="panel-page">
            <h1 class="panel-page-title">Welcome back, {{ user?.name }}</h1>
            <p class="panel-page-lead">
                This is the Africs Console — the internal ERP. Modules will
                appear in the sidebar as they come online.
            </p>

            <div class="panel-card-grid">
                <div class="panel-card">
                    <h2 class="panel-card-title">Getting started</h2>
                    <p>The foundation is in place: authentication, roles, and
                    panel access control. Next up is the first Console module.</p>
                </div>
            </div>
        </div>
    </ConsoleLayout>
</template>
