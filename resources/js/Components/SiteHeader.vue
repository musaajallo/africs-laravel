<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

const mobileMenuOpen = ref(false);
</script>

<template>
    <header class="site-nav">
        <div class="site-nav-inner">
            <Link href="/" class="site-nav-brand">
                <ApplicationLogo class="site-nav-logo" alt="Africs" />
            </Link>

            <nav class="site-nav-links" aria-label="Primary">
                <Link href="/" class="site-nav-link">Home</Link>
                <a href="/#capabilities" class="site-nav-link">Capabilities</a>
                <a href="/#process" class="site-nav-link">How we work</a>
                <a href="/#academy" class="site-nav-link">Academy</a>
                <Link :href="route('contact')" class="site-nav-link">Contact</Link>
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
            <Link href="/" class="site-nav-link" @click="mobileMenuOpen = false">Home</Link>
            <a href="/#capabilities" class="site-nav-link" @click="mobileMenuOpen = false">Capabilities</a>
            <a href="/#process" class="site-nav-link" @click="mobileMenuOpen = false">How we work</a>
            <a href="/#academy" class="site-nav-link" @click="mobileMenuOpen = false">Academy</a>
            <Link :href="route('contact')" class="site-nav-link" @click="mobileMenuOpen = false">Contact</Link>
        </nav>
    </header>
</template>
