<script setup>
import { Head } from '@inertiajs/vue3';
import CmsLayout from '@/Layouts/CmsLayout.vue';
import { useAuth } from '@/Composables/useAuth.js';

const { user } = useAuth();
</script>

<template>
    <Head title="CMS" />

    <CmsLayout>
        <template #title>Dashboard</template>

        <div class="panel-page">
            <h1 class="panel-page-title">Welcome back, {{ user?.name }}</h1>
            <p class="panel-page-lead">
                This is the Africs CMS — where the public website content is
                managed. Editable sections will appear in the sidebar as they
                are wired up.
            </p>

            <div class="panel-card-grid">
                <div class="panel-card">
                    <h2 class="panel-card-title">Getting started</h2>
                    <p>The foundation is in place. Content editing screens come
                    after the Console build.</p>
                </div>
            </div>
        </div>
    </CmsLayout>
</template>
