<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
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
const searchInput = ref(null);
const isMac = ref(false);

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

function onKeydown(e) {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        searchInput.value?.focus();
    }
}

onMounted(() => {
    isMac.value = /Mac|iPhone|iPad/.test(navigator.platform);
    window.addEventListener('keydown', onKeydown);
});

onUnmounted(() => window.removeEventListener('keydown', onKeydown));
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
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <Link :href="props.homeRoute" class="panel-brand">
                    <ApplicationLogo class="panel-brand-logo" alt="Africs" />
                    <span class="panel-brand-divider" aria-hidden="true"></span>
                    <span class="panel-brand-label">{{ props.label }}</span>
                </Link>
            </div>

            <form class="panel-search" role="search" @submit.prevent>
                <svg class="panel-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="11" cy="11" r="7" />
                    <path d="M21 21l-4.3-4.3" />
                </svg>
                <input
                    ref="searchInput"
                    type="search"
                    class="panel-search-input"
                    placeholder="Search…"
                    aria-label="Search"
                />
                <kbd class="panel-search-kbd">{{ isMac ? '⌘' : 'Ctrl' }} K</kbd>
            </form>

            <div class="panel-header-right">
                <Dropdown align="right">
                    <template #trigger>
                        <button type="button" class="panel-icon-button" aria-label="Notifications">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
                                <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                            </svg>
                        </button>
                    </template>
                    <template #content>
                        <div class="panel-menu-head">Notifications</div>
                        <div class="panel-menu-empty">You're all caught up.</div>
                    </template>
                </Dropdown>

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
