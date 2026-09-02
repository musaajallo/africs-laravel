<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import NavDropdown from '@/Components/NavDropdown.vue';
import NavMobileGroup from '@/Components/NavMobileGroup.vue';
import SiteAccountMenu from '@/Components/SiteAccountMenu.vue';
import { useAuth } from '@/Composables/useAuth';

defineProps({
    canLogin: {
        type: Boolean,
    },
});

const mobileMenuOpen = ref(false);
const isDark = ref(false);

const { user, can } = useAuth();

// Panels the signed-in user is allowed to open, in landing-priority order
// (mirrors App\Support\PanelRouter). Drives whether the nav shows a single
// link straight to their panel or a dropdown of the panels they can reach.
const panels = computed(() => {
    const list = [];

    if (can('console.access')) {
        list.push({ label: 'Console', href: route('console.dashboard') });
    }

    if (can('cms.access')) {
        list.push({ label: 'Website CMS', href: route('cms.dashboard') });
    }

    return list;
});

const accountLabel = computed(
    () => user.value?.name?.trim().split(/\s+/)[0] || 'Account',
);

const servicesItems = [
    { label: 'Business', href: route('services.business') },
    { label: 'Technology', href: route('services.technology') },
    { label: 'Design', href: route('services.design') },
];

const moreItems = [
    { label: 'Partnerships', href: route('partnerships') },
    { label: 'Network', href: route('network') },
    { label: 'Careers', href: route('careers') },
];
</script>

<template>
    <header class="site-nav">
        <div class="site-nav-inner">
            <Link href="/" class="site-nav-brand">
                <ApplicationLogo class="site-nav-logo" alt="Africs" />
            </Link>

            <nav class="site-nav-links" aria-label="Primary">
                <Link href="/" class="site-nav-link">Home</Link>

                <NavDropdown label="Services" :items="servicesItems" />

                <a href="/#process" class="site-nav-link">How we work</a>
                <Link :href="route('portfolio')" class="site-nav-link">Portfolio</Link>

                <NavDropdown label="More" :items="moreItems" />

                <Link :href="route('contact')" class="site-nav-link">Contact</Link>
                <Link :href="route('academy')" class="btn btn-accent site-nav-btn">Academy</Link>
                <Link :href="route('limitless-africs')" class="btn btn-primary site-nav-btn">Limitless Africs</Link>
            </nav>

            <div class="site-nav-actions">
                <button
                    type="button"
                    class="theme-toggle"
                    role="switch"
                    :aria-checked="isDark"
                    aria-label="Toggle dark mode"
                    @click="isDark = !isDark"
                >
                    <svg v-if="!isDark" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4.5" />
                        <path d="M12 2.5v2.5M12 19v2.5M4.9 4.9l1.8 1.8M17.3 17.3l1.8 1.8M2.5 12H5M19 12h2.5M4.9 19.1l1.8-1.8M17.3 6.7l1.8-1.8" />
                    </svg>
                    <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 14.5A8.5 8.5 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5Z" />
                    </svg>
                </button>

                <template v-if="user">
                    <Link
                        v-if="panels.length === 1"
                        :href="panels[0].href"
                        class="btn btn-secondary site-nav-login"
                    >
                        {{ panels[0].label }}
                    </Link>
                    <SiteAccountMenu
                        v-else
                        :label="accountLabel"
                        :panels="panels"
                    />
                </template>
                <Link
                    v-else-if="canLogin"
                    :href="route('login')"
                    class="btn btn-secondary site-nav-login"
                >
                    Login
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
        </div>

        <nav v-show="mobileMenuOpen" class="site-mobile-menu" aria-label="Mobile">
            <Link href="/" class="site-nav-link" @click="mobileMenuOpen = false">Home</Link>

            <NavMobileGroup
                label="Services"
                :items="servicesItems"
                @navigate="mobileMenuOpen = false"
            />

            <a href="/#process" class="site-nav-link" @click="mobileMenuOpen = false">How we work</a>
            <Link :href="route('portfolio')" class="site-nav-link" @click="mobileMenuOpen = false">Portfolio</Link>

            <NavMobileGroup
                label="More"
                :items="moreItems"
                @navigate="mobileMenuOpen = false"
            />

            <Link :href="route('contact')" class="site-nav-link" @click="mobileMenuOpen = false">Contact</Link>
            <Link :href="route('academy')" class="btn btn-accent site-nav-btn" @click="mobileMenuOpen = false">Academy</Link>
            <Link :href="route('limitless-africs')" class="btn btn-primary site-nav-btn" @click="mobileMenuOpen = false">Limitless Africs</Link>
            <template v-if="user">
                <Link
                    v-for="panel in panels"
                    :key="panel.href"
                    :href="panel.href"
                    class="site-nav-link"
                    @click="mobileMenuOpen = false"
                >
                    {{ panel.label }}
                </Link>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="site-nav-link site-mobile-logout"
                    @click="mobileMenuOpen = false"
                >
                    Log out
                </Link>
            </template>
            <Link
                v-else-if="canLogin"
                :href="route('login')"
                class="site-nav-link"
                @click="mobileMenuOpen = false"
            >
                Login
            </Link>
        </nav>
    </header>
</template>
