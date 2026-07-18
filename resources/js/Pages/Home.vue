<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import ConstellationDiagram from '@/Components/ConstellationDiagram.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

const page = ref(null);
let observer;

onMounted(() => {
    const prefersReducedMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)',
    ).matches;

    const targets = page.value?.querySelectorAll('.reveal') ?? [];

    if (prefersReducedMotion) {
        targets.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.15, rootMargin: '0px 0px -80px 0px' },
    );

    targets.forEach((el) => observer.observe(el));
});

onUnmounted(() => observer?.disconnect());

const year = new Date().getFullYear();
const mobileMenuOpen = ref(false);
</script>

<template>
    <Head title="Africs — Business, Technology &amp; Design Solutions">
        <meta
            name="description"
            content="Africs is a digital product studio that partners with organizations to research, design, and build the technology their business strategy promises — combining business consulting, engineering, and design under one roof."
        />
        <meta property="og:title" content="Africs — Business, Technology &amp; Design Solutions" />
        <meta
            property="og:description"
            content="A digital product studio combining business consulting, technology, and design to build the products organizations need."
        />
        <meta property="og:type" content="website" />
        <meta name="twitter:card" content="summary" />
    </Head>

    <a href="#main" class="skip-link">Skip to content</a>

    <div ref="page">
        <header class="site-nav">
            <div class="site-nav-inner">
                <Link href="/" class="site-nav-brand">
                    <ApplicationLogo class="site-nav-logo" alt="Africs" />
                    <span class="site-nav-wordmark">Africs</span>
                </Link>

                <nav class="site-nav-links" aria-label="Primary">
                    <a href="#capabilities" class="site-nav-link">Capabilities</a>
                    <a href="#process" class="site-nav-link">How we work</a>
                    <a href="#academy" class="site-nav-link">Academy</a>
                    <a href="#contact" class="site-nav-link">Contact</a>
                </nav>

                <Link
                    v-if="canLogin"
                    :href="route('login')"
                    class="btn btn-secondary"
                    style="margin-left: auto"
                >
                    Client login
                </Link>

                <button
                    type="button"
                    class="site-nav-toggle"
                    :aria-expanded="mobileMenuOpen"
                    aria-label="Toggle menu"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                >
                    <svg width="24" height="24" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path
                            v-if="!mobileMenuOpen"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                        <path
                            v-else
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <nav v-show="mobileMenuOpen" class="site-mobile-menu" aria-label="Mobile">
                <a href="#capabilities" class="site-nav-link" @click="mobileMenuOpen = false">Capabilities</a>
                <a href="#process" class="site-nav-link" @click="mobileMenuOpen = false">How we work</a>
                <a href="#academy" class="site-nav-link" @click="mobileMenuOpen = false">Academy</a>
                <a href="#contact" class="site-nav-link" @click="mobileMenuOpen = false">Contact</a>
            </nav>
        </header>

        <main id="main">
            <!-- Hero -->
            <section class="hero">
                <div class="hero-inner">
                    <div>
                        <p class="hero-eyebrow">Africs Studio — Banjul, The Gambia</p>
                        <h1 class="hero-title">
                            Business ideas,<br />
                            <em>engineered</em> into products.
                        </h1>
                        <p class="hero-sub">
                            We partner with organizations across West Africa
                            and beyond to research, design, and build the
                            digital products their strategy depends on —
                            combining business consulting, technology, and
                            design under one roof.
                        </p>
                        <div class="hero-actions">
                            <a href="#contact" class="btn btn-primary btn-lg">
                                Start a project
                            </a>
                            <a href="#process" class="btn btn-on-dark btn-lg">
                                See how we work
                            </a>
                        </div>
                    </div>

                    <ConstellationDiagram />
                </div>
            </section>

            <!-- Mission -->
            <section class="section reveal">
                <div class="container">
                    <blockquote class="quote">
                        “Adding value to every engagement” isn't a slogan for
                        us — it's the test every project has to pass. We
                        specialise in solutions that bridge
                        <span>business objectives</span>, technological
                        capability, and design thinking into one outcome:
                        <span>products that work.</span>
                    </blockquote>
                </div>
            </section>

            <!-- Capabilities / Divisions -->
            <section id="capabilities" class="section section-bone reveal">
                <div class="container">
                    <div class="section-head">
                        <p class="section-eyebrow">Capabilities</p>
                        <h2 class="section-title">Four disciplines, one team.</h2>
                        <p class="section-lede">
                            Every engagement draws on whichever mix of these
                            four disciplines the problem actually needs —
                            rather than handing you off between departments.
                        </p>
                    </div>

                    <div class="divisions-grid">
                        <article class="division-card">
                            <p class="division-code">TECHNOLOGY</p>
                            <h3 class="division-name">Product &amp; engineering</h3>
                            <p class="division-desc">
                                Software product design, engineering, cloud
                                infrastructure, and IT strategy — the build
                                side of the business case.
                            </p>
                        </article>

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
                            <p class="division-code">INNOVATION</p>
                            <h3 class="division-name">Research &amp; development</h3>
                            <p class="division-desc">
                                Early-stage research and prototyping for
                                problems that don't have an off-the-shelf
                                answer yet.
                            </p>
                        </article>

                        <article class="division-card">
                            <p class="division-code">OPERATIONS</p>
                            <h3 class="division-name">Operational excellence</h3>
                            <p class="division-desc">
                                Process optimisation and delivery discipline
                                so what gets built keeps running long after
                                launch.
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <!-- Process -->
            <section id="process" class="section section-dark reveal">
                <div class="container">
                    <div class="section-head">
                        <p class="section-eyebrow">How we work</p>
                        <h2 class="section-title" style="color: var(--color-cream-text)">
                            One process, from problem to product.
                        </h2>
                    </div>

                    <div class="process-list">
                        <div class="process-step">
                            <p class="process-number">01</p>
                            <h3 class="process-title">Discover</h3>
                            <p class="process-desc">
                                We get precise about the business problem,
                                the users, and the constraints before anyone
                                designs or writes a line of code.
                            </p>
                        </div>

                        <div class="process-step">
                            <p class="process-number">02</p>
                            <h3 class="process-title">Design</h3>
                            <p class="process-desc">
                                We prototype the product experience and
                                validate direction with real users before
                                committing to a build.
                            </p>
                        </div>

                        <div class="process-step">
                            <p class="process-number">03</p>
                            <h3 class="process-title">Build</h3>
                            <p class="process-desc">
                                We engineer the product with the right
                                technology choices for your scale, budget,
                                and team.
                            </p>
                        </div>

                        <div class="process-step">
                            <p class="process-number">04</p>
                            <h3 class="process-title">Ship</h3>
                            <p class="process-desc">
                                We launch, measure, and iterate alongside
                                your team — not disappear the day it goes
                                live.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Academy -->
            <section id="academy" class="section reveal">
                <div class="container">
                    <div class="academy-banner">
                        <div>
                            <p class="section-eyebrow">Africs Academy</p>
                            <h2 class="section-title" style="font-size: 1.6rem">
                                Training the next generation of builders.
                            </h2>
                            <p class="section-lede" style="margin-top: 0.6rem">
                                Courses in product, engineering, and business
                                fundamentals for people who want to build the
                                way we do.
                            </p>
                        </div>
                        <span class="badge">Coming soon</span>
                    </div>
                </div>
            </section>

            <!-- Contact CTA -->
            <section id="contact" class="cta-section reveal">
                <div class="container">
                    <h2 class="cta-title">Have a product to build?</h2>
                    <p class="cta-sub">
                        Tell us about the problem you're solving — we'll tell
                        you honestly whether we're the right team for it.
                    </p>
                    <div class="cta-actions">
                        <a href="mailto:info@africs.net" class="btn btn-primary btn-lg">
                            info@africs.net
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <footer class="site-footer">
            <div class="footer-inner">
                <div class="footer-brand">
                    <ApplicationLogo style="height: 1.75rem" alt="Africs" />
                    <p class="footer-tagline">
                        A digital product studio combining business,
                        technology, and design — based in Banjul, The
                        Gambia.
                    </p>
                </div>

                <div class="footer-links">
                    <div>
                        <p class="footer-col-title">Studio</p>
                        <div class="footer-col-links">
                            <a href="#capabilities">Capabilities</a>
                            <a href="#process">How we work</a>
                            <a href="#academy">Academy</a>
                        </div>
                    </div>
                    <div>
                        <p class="footer-col-title">Contact</p>
                        <div class="footer-col-links">
                            <a href="mailto:info@africs.net">info@africs.net</a>
                            <a href="#contact">Start a project</a>
                        </div>
                    </div>
                </div>
            </div>

            <p class="footer-bottom">&copy; {{ year }} Africs.</p>
        </footer>
    </div>
</template>
