<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import PanelFlash from '@/Components/Panel/PanelFlash.vue';
import { useAuth } from '@/Composables/useAuth.js';

const props = defineProps({
    // 'console' | 'cms' — drives the accent colour and home link.
    panel: { type: String, required: true },
    label: { type: String, required: true },
    homeRoute: { type: String, required: true },
    // [{ label, routeName, activeMatch?, permission? }]
    nav: { type: Array, default: () => [] },
});

const { user } = useAuth();
const sidebarOpen = ref(false);

function initials(name) {
    return (name || '?')
        .split(' ')
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
}

function isActive(item) {
    return route().current(item.activeMatch || item.routeName);
}
</script>

<template>
    <div class="panel-shell" :data-panel="props.panel">
        <header class="panel-header">
            <div class="panel-header-left">
                <button
                    type="button"
                    class="panel-menu-toggle"
                    @click="sidebarOpen = true"
                    aria-label="Open menu"
                >
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <Link :href="props.homeRoute" class="panel-brand">
                    <ApplicationLogo class="panel-brand-logo" alt="Africs" />
                    <span class="panel-brand-divider" aria-hidden="true"></span>
                    <span class="panel-brand-label">{{ props.label }}</span>
                </Link>

                <template v-if="$slots.title">
                    <span class="panel-brand-divider is-faint" aria-hidden="true"></span>
                    <span class="panel-header-context"><slot name="title" /></span>
                </template>
            </div>

            <div class="panel-header-right">
                <Dropdown align="right">
                    <template #trigger>
                        <button type="button" class="panel-user-button">
                            <span class="panel-user-avatar">{{ initials(user?.name) }}</span>
                            <span class="panel-user-name-inline">{{ user?.name }}</span>
                            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>
                    </template>
                    <template #content>
                        <div class="panel-user-meta">
                            <div class="panel-user-name">{{ user?.name }}</div>
                            <div class="panel-user-email">{{ user?.email }}</div>
                        </div>
                        <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                        <DropdownLink :href="route('logout')" method="post" as="button">
                            Log out
                        </DropdownLink>
                    </template>
                </Dropdown>
            </div>
        </header>

        <aside class="panel-sidebar" :class="{ 'is-open': sidebarOpen }">
            <nav class="panel-nav">
                <Link
                    v-for="item in props.nav"
                    :key="item.routeName"
                    :href="route(item.routeName)"
                    class="panel-nav-link"
                    :class="{ 'is-active': isActive(item) }"
                    @click="sidebarOpen = false"
                >
                    {{ item.label }}
                </Link>
            </nav>

            <div class="panel-sidebar-foot">
                <Link href="/" class="panel-nav-link is-muted">← Back to site</Link>
            </div>
        </aside>

        <div
            v-if="sidebarOpen"
            class="panel-scrim"
            @click="sidebarOpen = false"
        ></div>

        <main class="panel-main">
            <div class="panel-content">
                <slot />
            </div>
        </main>

        <PanelFlash />
    </div>
</template>
