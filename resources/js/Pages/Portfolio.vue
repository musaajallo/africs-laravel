<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import SiteHeader from '@/Components/SiteHeader.vue';
import SiteFooter from '@/Components/SiteFooter.vue';
import { useScrollReveal } from '@/Composables/useScrollReveal';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

const pageRoot = ref(null);

useScrollReveal(pageRoot);

// Case studies go here as they're added — { name, url, category, description }.
const projects = [];
</script>

<template>
    <Head title="Portfolio — Africs">
        <meta
            name="description"
            content="A selection of the projects Africs has added value to across technology, strategy, and design."
        />
        <link rel="canonical" href="https://africsinc.com/portfolio" />
        <meta property="og:title" content="Portfolio — Africs" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="https://africsinc.com/portfolio" />
    </Head>

    <a href="#main" class="skip-link">Skip to content</a>

    <div ref="pageRoot">
        <SiteHeader :can-login="canLogin" />

        <main id="main">
            <section class="page-hero">
                <div class="container">
                    <p class="section-eyebrow">Portfolio</p>
                    <h1 class="section-title" style="font-size: clamp(2rem, 4vw, 2.75rem)">
                        Work we've added value to.
                    </h1>
                    <p class="section-lede">
                        A selection of projects across technology, strategy,
                        and design — built with clients who trusted us with
                        the problem, not just the brief.
                    </p>
                </div>
            </section>

            <section class="section" style="padding-top: 0">
                <div class="container">
                    <div v-if="projects.length" class="portfolio-grid">
                        <a
                            v-for="project in projects"
                            :key="project.url"
                            :href="project.url"
                            class="portfolio-card"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <p class="division-code">{{ project.category }}</p>
                            <h3 class="division-name">{{ project.name }}</h3>
                            <p class="division-desc">{{ project.description }}</p>
                            <span class="portfolio-link">
                                Visit site
                                <span aria-hidden="true">&rarr;</span>
                            </span>
                        </a>
                    </div>

                    <div v-else class="portfolio-empty">
                        <p>We're putting our project archive together — check back soon.</p>
                    </div>
                </div>
            </section>
        </main>

        <SiteFooter />
    </div>
</template>
