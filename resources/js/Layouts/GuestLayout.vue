<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    heading: { type: String, default: null },
    subheading: { type: String, default: null },
});
</script>

<template>
    <div class="auth-shell">
        <header class="auth-topbar">
            <div class="auth-topbar-inner">
                <Link href="/" class="auth-brand">
                    <ApplicationLogo class="auth-brand-logo" alt="Africs" />
                    <span class="auth-brand-divider" aria-hidden="true"></span>
                    <span class="auth-brand-label">Console</span>
                </Link>

                <Link href="/" class="auth-topbar-link">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7" />
                    </svg>
                    <span>Back to site</span>
                </Link>
            </div>
        </header>

        <main class="auth-main">
            <div class="auth-split">
                <aside class="auth-aside">
                    <div class="auth-aside-top">
                        <ApplicationLogo class="auth-aside-logo" alt="Africs" />
                        <p class="auth-aside-lead">
                            The operations platform behind Africs — manage the
                            website, clients, and internal workflows from one place.
                        </p>
                    </div>

                    <ul class="auth-aside-list">
                        <li>
                            <span class="auth-aside-dot"></span>
                            <div>
                                <strong>Console</strong>
                                <span>Internal ERP &amp; team tools</span>
                            </div>
                        </li>
                        <li>
                            <span class="auth-aside-dot"></span>
                            <div>
                                <strong>CMS</strong>
                                <span>Public website content</span>
                            </div>
                        </li>
                    </ul>
                </aside>

                <div class="auth-form-side">
                    <div class="auth-card">
                        <div v-if="heading || subheading" class="auth-card-head">
                            <h1 v-if="heading" class="auth-card-title">{{ heading }}</h1>
                            <p v-if="subheading" class="auth-card-subtitle">{{ subheading }}</p>
                        </div>

                        <slot />
                    </div>

                    <p class="auth-footnote">
                        Authorised access only &middot; Africs Inc
                    </p>
                </div>
            </div>
        </main>
    </div>
</template>
