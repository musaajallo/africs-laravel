<script setup>
import { Head } from '@inertiajs/vue3';
import SiteHeader from '@/Components/SiteHeader.vue';
import SiteFooter from '@/Components/SiteFooter.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Cookie Policy — Africs">
        <meta
            name="description"
            content="How Africs uses cookies — what's set today, why, and how to control them."
        />
        <link rel="canonical" href="https://africsinc.com/cookie-policy" />
    </Head>

    <a href="#main" class="skip-link">Skip to content</a>

    <div>
        <SiteHeader :can-login="canLogin" />

        <main id="main">
            <section class="page-hero">
                <div class="container">
                    <p class="section-eyebrow">Legal</p>
                    <h1 class="section-title" style="font-size: clamp(2rem, 4vw, 2.75rem)">
                        Cookie Policy
                    </h1>
                    <p class="legal-updated">Last updated: 18 July 2026</p>
                </div>
            </section>

            <section class="section" style="padding-top: 0">
                <div class="container">
                    <div class="legal-content">
                        <h2>What are cookies?</h2>
                        <p>
                            Cookies are small text files a website stores on
                            your device to remember information between
                            visits. Some are essential for the site to work
                            at all; others are used for analytics,
                            preferences, or advertising.
                        </p>

                        <h2>What we use today</h2>
                        <p>
                            Africs currently uses only <strong>strictly
                            necessary</strong> cookies — the ones our
                            website can't function without:
                        </p>
                        <ul>
                            <li>
                                <strong>Session cookie</strong> — keeps you
                                signed in and remembers your session while
                                you browse.
                            </li>
                            <li>
                                <strong>CSRF token cookie</strong> — protects
                                forms (like our contact form) from
                                cross-site request forgery.
                            </li>
                        </ul>
                        <p>
                            We also store one preference in your browser's
                            local storage (not a cookie) to remember whether
                            you've accepted or declined this policy, so we
                            don't ask you again on every visit.
                        </p>

                        <h2>What we don't use</h2>
                        <p>
                            We don't currently use analytics, advertising,
                            or third-party tracking cookies of any kind. If
                            that changes in the future, we'll update this
                            page and ask for your consent before setting
                            them.
                        </p>

                        <h2>Controlling cookies</h2>
                        <p>
                            Because we only set strictly necessary cookies,
                            blocking them may prevent core features (like
                            staying signed in or submitting forms) from
                            working. Most browsers let you view, delete, or
                            block cookies through their settings if you'd
                            like to manage this yourself.
                        </p>

                        <h2>Questions</h2>
                        <p>
                            If you have questions about this policy, email
                            us at
                            <a href="mailto:info@africsinc.com">info@africsinc.com</a>.
                        </p>
                    </div>
                </div>
            </section>
        </main>

        <SiteFooter />
    </div>
</template>
