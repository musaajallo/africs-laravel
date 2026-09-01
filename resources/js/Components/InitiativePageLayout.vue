<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import SiteHeader from '@/Components/SiteHeader.vue';
import SiteFooter from '@/Components/SiteFooter.vue';
import { useScrollReveal } from '@/Composables/useScrollReveal';

const props = defineProps({
    canLogin: { type: Boolean, default: false },
    metaTitle: { type: String, required: true },
    metaDescription: { type: String, default: '' },
    canonical: { type: String, default: '' },
    eyebrow: { type: String, default: 'Initiative' },
    title: { type: String, required: true },
    lede: { type: String, default: '' },
    heroImage: { type: String, default: '' },
    heroImageAlt: { type: String, default: '' },
    primaryLabel: { type: String, default: '' },
    primaryHref: { type: String, default: '' },
    sectionEyebrow: { type: String, default: '' },
    sectionTitle: { type: String, default: '' },
    sectionLede: { type: String, default: '' },
    cards: { type: Array, default: () => [] },
    ctaTitle: { type: String, default: '' },
    ctaText: { type: String, default: '' },
    ctaLabel: { type: String, default: '' },
    ctaHref: { type: String, default: '' },
    ctaEmail: { type: String, default: '' },
});

const pageRoot = ref(null);

useScrollReveal(pageRoot);

// Drop a real photo at the `heroImage` path to replace the textured fallback.
const heroImageFailed = ref(!props.heroImage);
</script>

<template>
    <Head :title="metaTitle">
        <meta v-if="metaDescription" name="description" :content="metaDescription" />
        <link v-if="canonical" rel="canonical" :href="canonical" />
    </Head>

    <a href="#main" class="skip-link">Skip to content</a>

    <div ref="pageRoot">
        <SiteHeader :can-login="canLogin" />

        <main id="main">
            <section class="initiative-hero">
                <div class="container">
                    <div class="initiative-hero-grid">
                        <div>
                            <p class="section-eyebrow">{{ eyebrow }}</p>
                            <h1 class="initiative-hero-title">{{ title }}</h1>
                            <p v-if="lede" class="initiative-hero-lede">{{ lede }}</p>
                            <div v-if="primaryLabel || $slots['hero-extra']" class="initiative-hero-actions">
                                <Link
                                    v-if="primaryLabel"
                                    :href="primaryHref"
                                    class="btn btn-primary btn-lg"
                                >
                                    {{ primaryLabel }}
                                </Link>
                                <slot name="hero-extra" />
                            </div>
                        </div>

                        <figure class="initiative-hero-figure">
                            <img
                                v-if="!heroImageFailed"
                                :src="heroImage"
                                :alt="heroImageAlt"
                                loading="lazy"
                                @error="heroImageFailed = true"
                            />
                        </figure>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="container">
                    <div class="section-head">
                        <p v-if="sectionEyebrow" class="section-eyebrow">{{ sectionEyebrow }}</p>
                        <h2 v-if="sectionTitle" class="section-title">{{ sectionTitle }}</h2>
                        <p v-if="sectionLede" class="section-lede">{{ sectionLede }}</p>
                    </div>

                    <div class="programs-grid">
                        <article
                            v-for="card in cards"
                            :key="card.code"
                            class="program-card"
                        >
                            <p class="division-code">{{ card.code }}</p>
                            <h3 class="division-name">{{ card.name }}</h3>
                            <p class="division-desc">{{ card.desc }}</p>
                        </article>
                    </div>

                    <slot name="after-cards" />
                </div>
            </section>

            <section class="cta-section reveal">
                <div class="container">
                    <h2 class="cta-title">{{ ctaTitle }}</h2>
                    <p v-if="ctaText" class="cta-sub">{{ ctaText }}</p>
                    <div class="cta-actions">
                        <Link
                            v-if="ctaLabel"
                            :href="ctaHref"
                            class="btn btn-primary btn-lg"
                        >
                            {{ ctaLabel }}
                        </Link>
                        <a
                            v-if="ctaEmail"
                            :href="`mailto:${ctaEmail}`"
                            class="btn btn-on-dark btn-lg"
                        >
                            {{ ctaEmail }}
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <SiteFooter />
    </div>
</template>
