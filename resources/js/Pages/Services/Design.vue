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
    <Head title="Design Services — Africs">
        <meta
            name="description"
            content="Product and service design that earns trust before a single line of code ships."
        />
        <link rel="canonical" href="https://africsinc.com/services/design" />
    </Head>

    <a href="#main" class="skip-link">Skip to content</a>

    <div>
        <SiteHeader :can-login="canLogin" />

        <main id="main">
            <section class="page-hero">
                <div class="container">
                    <p class="section-eyebrow">Services</p>
                    <h1 class="section-title" style="font-size: clamp(2rem, 4vw, 2.75rem)">
                        Design.
                    </h1>
                    <p class="section-lede">
                        Product and service design that earns trust before
                        a single line of code ships.
                    </p>
                </div>
            </section>

            <section class="section" style="padding-top: 0">
                <div class="container">
                    <div class="divisions-grid">
                        <article class="division-card">
                            <p class="division-code">PRODUCT DESIGN</p>
                            <h3 class="division-name">User experience &amp; interface design</h3>
                            <p class="division-desc">
                                Mapping how people actually move through a
                                product, then designing the interface around
                                that reality — not the org chart.
                            </p>
                        </article>

                        <article class="division-card">
                            <p class="division-code">VALIDATION</p>
                            <h3 class="division-name">Prototyping &amp; user testing</h3>
                            <p class="division-desc">
                                We test direction with real users before
                                committing engineering time to it, so the
                                expensive mistakes get caught on paper.
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="cta-section reveal">
                <div class="container">
                    <h2 class="cta-title">Ready to design something people trust?</h2>
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
