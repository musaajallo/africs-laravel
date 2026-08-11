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
    <Head title="Business Services — Africs">
        <meta
            name="description"
            content="Strategic advisory and operational discipline that turns ambition into an executable plan."
        />
        <link rel="canonical" href="https://africsinc.com/services/business" />
    </Head>

    <a href="#main" class="skip-link">Skip to content</a>

    <div>
        <SiteHeader :can-login="canLogin" />

        <main id="main">
            <section class="page-hero">
                <div class="container">
                    <p class="section-eyebrow">Services</p>
                    <h1 class="section-title" style="font-size: clamp(2rem, 4vw, 2.75rem)">
                        Business.
                    </h1>
                    <p class="section-lede">
                        Strategic advisory and operational discipline that
                        turns ambition into an executable plan.
                    </p>
                </div>
            </section>

            <section class="section" style="padding-top: 0">
                <div class="container">
                    <div class="divisions-grid">
                        <article class="division-card">
                            <p class="division-code">CONSULTING</p>
                            <h3 class="division-name">Business strategy</h3>
                            <p class="division-desc">
                                Strategic advisory, growth planning, and
                                market strategy for organizations navigating
                                complex change.
                            </p>
                        </article>

                        <article class="division-card">
                            <p class="division-code">OPERATIONS</p>
                            <h3 class="division-name">Operational excellence</h3>
                            <p class="division-desc">
                                Process optimisation and delivery discipline
                                that keeps the value we add compounding well
                                beyond the engagement.
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="cta-section reveal">
                <div class="container">
                    <h2 class="cta-title">Ready to talk business strategy?</h2>
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
