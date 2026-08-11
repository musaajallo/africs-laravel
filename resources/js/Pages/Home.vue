<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import SiteHeader from '@/Components/SiteHeader.vue';
import SiteFooter from '@/Components/SiteFooter.vue';
import { useScrollReveal } from '@/Composables/useScrollReveal';

defineProps({
    canLogin: {
        type: Boolean,
    },
    clientLogos: {
        type: Array,
        default: () => [],
    },
});

const page = ref(null);
const logosViewport = ref(null);
let marqueeFrame = null;
let resumeTimeout = null;
let marqueePaused = false;

useScrollReveal(page);

const prefersReducedMotion =
    typeof window !== 'undefined' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function stepMarquee() {
    const el = logosViewport.value;
    if (el && !marqueePaused) {
        el.scrollLeft += 0.6;
        const half = el.scrollWidth / 2;
        if (el.scrollLeft >= half) {
            el.scrollLeft -= half;
        }
    }
    marqueeFrame = requestAnimationFrame(stepMarquee);
}

function pauseMarquee() {
    marqueePaused = true;
    // Safety net: if a mouseleave/touchend never fires (e.g. a stuck
    // synthetic hover state on touch devices), don't stay paused forever.
    resumeMarquee(6000);
}

function resumeMarquee(delay = 0) {
    clearTimeout(resumeTimeout);
    resumeTimeout = setTimeout(() => {
        marqueePaused = false;
    }, delay);
}

function scrollLogos(direction) {
    pauseMarquee();
    logosViewport.value?.scrollBy({ left: direction * 280, behavior: 'smooth' });
    resumeMarquee(2500);
}

onMounted(() => {
    if (!prefersReducedMotion) {
        marqueeFrame = requestAnimationFrame(stepMarquee);
    }
});

onUnmounted(() => {
    if (marqueeFrame) cancelAnimationFrame(marqueeFrame);
    clearTimeout(resumeTimeout);
});

// Placeholder until real client logos are available — add an image and set
// `src` to a path under /public/images/clients/, e.g. src: '/images/clients/acme.svg'.
// Placeholder copy until real client testimonials are available — replace
// quote/name/role with an actual quote before this goes live.
const testimonials = [
    {
        quote: 'Add a short quote here about the outcome of working with Africs — what changed, and why it mattered.',
        name: 'Client Name',
        role: 'Title, Company',
    },
    {
        quote: 'Add a second testimonial here, ideally from a different type of engagement (e.g. a technology or design project).',
        name: 'Client Name',
        role: 'Title, Company',
    },
    {
        quote: 'Add a third testimonial here — real names and companies build far more trust than placeholder text like this.',
        name: 'Client Name',
        role: 'Title, Company',
    },
];
</script>

<template>
    <Head title="Africs — Business, Technology &amp; Design Solutions">
        <meta
            name="description"
            content="Africs adds value to every engagement — combining business strategy, technology, and design to help organizations across the public and private sectors achieve sustainable growth."
        />
        <link rel="canonical" href="https://africsinc.com/" />
        <meta property="og:title" content="Africs — Business, Technology &amp; Design Solutions" />
        <meta
            property="og:description"
            content="Adding value to every engagement — through business strategy, technology, and design."
        />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="https://africsinc.com/" />
        <meta name="twitter:card" content="summary" />
    </Head>

    <a href="#main" class="skip-link">Skip to content</a>

    <div ref="page">
        <SiteHeader :can-login="canLogin" />

        <main id="main">
            <!-- Hero -->
            <section class="hero">
                <div class="hero-pattern" aria-hidden="true">
                    <div class="hero-pattern-surface"></div>
                </div>
                <div class="hero-glow" aria-hidden="true"></div>
                <div class="hero-inner">
                    <h1 class="hero-title">
                        Solutions engineered<br />
                        to add <em>value</em>.
                    </h1>
                    <p class="hero-sub">
                        We combine business strategy, technology, and
                        design to add value to every engagement — for
                        organizations and individuals across the public
                        and private sectors.
                    </p>
                    <div class="hero-actions">
                        <Link :href="route('contact')" class="btn btn-primary btn-lg">
                            Start a project
                        </Link>
                        <a href="#process" class="btn btn-on-dark btn-lg">
                            See how we work
                        </a>
                    </div>
                </div>
            </section>

            <!-- Client logos -->
            <section class="logos-strip reveal">
                <div class="container">
                    <div class="logos-wrapper">
                        <button
                            type="button"
                            class="logos-nav"
                            aria-label="Scroll logos left"
                            @click="scrollLogos(-1)"
                        >
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12.5 15L7.5 10L12.5 5" />
                            </svg>
                        </button>

                        <div
                            ref="logosViewport"
                            class="logos-viewport"
                            @mouseenter="pauseMarquee"
                            @mouseleave="resumeMarquee(0)"
                            @touchstart="pauseMarquee"
                            @touchend="resumeMarquee(1500)"
                        >
                            <div class="logos-track">
                                <div
                                    v-for="(logo, index) in [...clientLogos, ...clientLogos]"
                                    :key="index"
                                    class="logo-tile"
                                >
                                    <img v-if="logo.src" :src="logo.src" :alt="logo.name" />
                                    <span v-else>{{ logo.name }}</span>
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="logos-nav"
                            aria-label="Scroll logos right"
                            @click="scrollLogos(1)"
                        >
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M7.5 5L12.5 10L7.5 15" />
                            </svg>
                        </button>
                    </div>
                </div>
            </section>

            <!-- Services / Divisions -->
            <section id="services" class="section reveal">
                <div class="container">
                    <div class="section-head">
                        <p class="section-eyebrow">Services</p>
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
                            <h3 class="division-name">Digital transformation</h3>
                            <p class="division-desc">
                                Software, cloud infrastructure, and IT
                                strategy that turn technology into a genuine
                                business advantage.
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
                                Early-stage research that adds value where
                                an off-the-shelf answer doesn't exist yet.
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

            <!-- Process -->
            <section id="process" class="section section-dark reveal">
                <div class="container">
                    <div class="section-head">
                        <p class="section-eyebrow">How we work</p>
                        <h2 class="section-title" style="color: var(--color-cream-text)">
                            One process, engineered to add value.
                        </h2>
                    </div>

                    <div class="process-list">
                        <div class="process-step">
                            <p class="process-number">01</p>
                            <h3 class="process-title">Discover</h3>
                            <p class="process-desc">
                                We get precise about the business problem,
                                the people affected, and the constraints
                                before we propose anything.
                            </p>
                        </div>

                        <div class="process-step">
                            <p class="process-number">02</p>
                            <h3 class="process-title">Design</h3>
                            <p class="process-desc">
                                We prototype the experience and validate
                                direction with real users before committing
                                to a build.
                            </p>
                        </div>

                        <div class="process-step">
                            <p class="process-number">03</p>
                            <h3 class="process-title">Build</h3>
                            <p class="process-desc">
                                We engineer the solution with the right
                                technology choices for your scale, budget,
                                and team.
                            </p>
                        </div>

                        <div class="process-step">
                            <p class="process-number">04</p>
                            <h3 class="process-title">Deliver</h3>
                            <p class="process-desc">
                                We measure and iterate alongside your team —
                                not disappear once the engagement ends.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Testimonials -->
            <section class="section section-bone reveal">
                <div class="container">
                    <div class="section-head">
                        <p class="section-eyebrow">What clients say</p>
                        <h2 class="section-title">Proof, in their words.</h2>
                    </div>

                    <div class="testimonials-grid">
                        <article
                            v-for="(testimonial, index) in testimonials"
                            :key="index"
                            class="testimonial-card"
                        >
                            <p class="testimonial-mark" aria-hidden="true">&ldquo;</p>
                            <p class="testimonial-quote">{{ testimonial.quote }}</p>
                            <div class="testimonial-author">
                                <span class="testimonial-avatar">{{ testimonial.name.charAt(0) }}</span>
                                <div>
                                    <p class="testimonial-name">{{ testimonial.name }}</p>
                                    <p class="testimonial-role">{{ testimonial.role }}</p>
                                </div>
                            </div>
                        </article>
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
                                Courses in business, technology, and design
                                fundamentals for people who want to add value
                                the way we do.
                            </p>
                        </div>
                        <span class="badge">Coming soon</span>
                    </div>
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
                        <span>real, measurable value.</span>
                    </blockquote>
                </div>
            </section>

            <!-- Contact CTA -->
            <section class="cta-section reveal">
                <div class="container">
                    <h2 class="cta-title">Ready to add value to your next project?</h2>
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
