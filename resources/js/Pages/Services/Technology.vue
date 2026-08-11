<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SiteHeader from '@/Components/SiteHeader.vue';
import SiteFooter from '@/Components/SiteFooter.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Technology Services — Africs">
        <meta
            name="description"
            content="Software, infrastructure, and applied research that turn technology into a genuine advantage."
        />
        <link rel="canonical" href="https://africsinc.com/services/technology" />
    </Head>

    <a href="#main" class="skip-link">Skip to content</a>

    <div>
        <SiteHeader :can-login="canLogin" />

        <main id="main">
            <section class="page-hero">
                <div class="container">
                    <p class="section-eyebrow">Services</p>
                    <h1 class="section-title" style="font-size: clamp(2rem, 4vw, 2.75rem)">
                        Technology.
                    </h1>
                    <p class="section-lede">
                        Software, infrastructure, and applied research that
                        turn technology into a genuine advantage.
                    </p>
                </div>
            </section>

            <section class="section" style="padding-top: 0">
                <div class="container">
                    <div class="divisions-grid">
                        <article class="division-card">
                            <p class="division-code">TECHNOLOGY</p>
                            <h3 class="division-name">Digital transformation</h3>
                            <p class="division-desc">
                                Software, cloud infrastructure, and IT
                                strategy that turn technology into a genuine
                                business advantage.
                            </p>
                        </article>

                        <article class="division-card">
                            <p class="division-code">INNOVATION</p>
                            <h3 class="division-name">Research &amp; development</h3>
                            <p class="division-desc">
                                Early-stage research that adds value where
                                an off-the-shelf answer doesn't exist yet.
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="cta-section reveal">
                <div class="container">
                    <h2 class="cta-title">Ready to build the right technology?</h2>
                    <p class="cta-sub">
                        Tell us about the challenge you're facing — we'll
                        tell you honestly how we can help.
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
