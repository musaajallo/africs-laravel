<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
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
            <section class="initiative-hero">
                <div class="container">
                    <div class="initiative-hero-grid">
                        <div>
                            <p class="section-eyebrow">Portfolio</p>
                            <h1 class="initiative-hero-title">Work we've added value to.</h1>
                            <p class="initiative-hero-lede">
                                A selection of projects across technology,
                                strategy, and design — built with clients who
                                trusted us with the problem, not just the brief.
                            </p>
                            <div class="initiative-hero-actions">
                                <Link :href="route('contact')" class="btn btn-primary btn-lg">
                                    Start a project
                                </Link>
                            </div>
                        </div>

                        <figure class="initiative-hero-figure" aria-hidden="true"></figure>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="container">
                    <div class="section-head">
                        <p class="section-eyebrow">Case studies</p>
                        <h2 class="section-title">Selected engagements.</h2>
                        <p class="section-lede">
                            Each one is a problem a client brought us before it
                            had an obvious answer.
                        </p>
                    </div>

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

            <section class="cta-section reveal">
                <div class="container">
                    <h2 class="cta-title">Want your project on this page?</h2>
                    <p class="cta-sub">
                        Tell us about the challenge you're facing — we'll tell
                        you honestly how we can help.
                    </p>
                    <div class="cta-actions">
                        <Link :href="route('contact')" class="btn btn-primary btn-lg">
                            Get in touch
                        </Link>
                        <a href="mailto:info@africsinc.com" class="btn btn-on-dark btn-lg">
                            info@africsinc.com
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <SiteFooter />
    </div>
</template>
