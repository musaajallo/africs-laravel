<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { useAuth } from '@/Composables/useAuth.js';

const props = defineProps({
    // 'console' | 'cms' — drives the accent colour and home link.
    panel: { type: String, required: true },
    label: { type: String, required: true },
    homeRoute: { type: String, required: true },
    // [{ label, routeName, icon? }]
    nav: { type: Array, default: () => [] },
});

const { user } = useAuth();
const sidebarOpen = ref(false);

function isActive(routeName) {
    return route().current(routeName);
}
</script>

<template>
    <div class="panel-shell" :data-panel="props.panel">
        <aside class="panel-sidebar" :class="{ 'is-open': sidebarOpen }">
            <div class="panel-sidebar-head">
                <Link :href="props.homeRoute" class="panel-brand">
                    <ApplicationLogo class="panel-brand-logo" />
                    <span class="panel-brand-label">{{ props.label }}</span>
                </Link>
                <button
                    type="button"
                    class="panel-sidebar-close"
                    @click="sidebarOpen = false"
                    aria-label="Close menu"
                >
                    &times;
                </button>
            </div>

            <nav class="panel-nav">
                <Link
                    v-for="item in props.nav"
                    :key="item.routeName"
                    :href="route(item.routeName)"
                    class="panel-nav-link"
                    :class="{ 'is-active': isActive(item.routeName) }"
                    @click="sidebarOpen = false"
                >
                    {{ item.label }}
                </Link>
            </nav>
        </aside>

        <div
            v-if="sidebarOpen"
            class="panel-scrim"
            @click="sidebarOpen = false"
        ></div>

        <div class="panel-main">
            <header class="panel-topbar">
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

                <div class="panel-topbar-title">
                    <slot name="title">{{ props.label }}</slot>
                </div>

                <Dropdown align="right">
                    <template #trigger>
                        <button type="button" class="panel-user-button">
                            <span>{{ user?.name }}</span>
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
            </header>

            <main class="panel-content">
                <slot />
            </main>
        </div>
    </div>
</template>
