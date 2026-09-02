<script setup>
import { Head } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';

defineProps({
    module: { type: Object, required: true },
});
</script>

<template>
    <Head :title="module.title" />

    <ConsoleLayout>
        <template #title>{{ module.title }}</template>

        <div class="panel-page">
            <PanelPageHeader :title="module.title" :subtitle="`Not built yet — ${module.phase}`" />

            <div class="panel-card roadmap-card">
                <span class="roadmap-badge">Coming soon</span>
                <p class="roadmap-blurb">{{ module.blurb }}</p>
                <p class="roadmap-note">
                    This is a placeholder so the shape of the Console is visible while
                    it's built. The link disappears once the module ships.
                </p>
                <a
                    class="panel-link"
                    style="padding: 0"
                    href="https://claude.ai/code/artifact/ad4ba146-2ae4-4252-9d31-89a707515aad"
                    target="_blank"
                    rel="noreferrer"
                >
                    See the full build plan &rarr;
                </a>
            </div>
        </div>
    </ConsoleLayout>
</template>
