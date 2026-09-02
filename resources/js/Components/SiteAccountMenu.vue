<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    // Text shown on the trigger button (usually the user's first name).
    label: { type: String, required: true },
    // Panels the user may open: [{ label: 'Console', href: '/console' }]
    panels: { type: Array, required: true },
});

const open = ref(false);
const root = ref(null);
let closeTimeout = null;

function openMenu() {
    clearTimeout(closeTimeout);
    open.value = true;
}

function scheduleClose() {
    clearTimeout(closeTimeout);
    closeTimeout = setTimeout(() => {
        open.value = false;
    }, 150);
}

const onEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

const onClickOutside = (e) => {
    if (open.value && root.value && !root.value.contains(e.target)) {
        open.value = false;
    }
};

onMounted(() => {
    document.addEventListener('keydown', onEscape);
    document.addEventListener('click', onClickOutside);
});
onUnmounted(() => {
    document.removeEventListener('keydown', onEscape);
    document.removeEventListener('click', onClickOutside);
    clearTimeout(closeTimeout);
});
</script>

<template>
    <div
        ref="root"
        class="site-nav-dropdown site-account-menu"
        @mouseenter="openMenu"
        @mouseleave="scheduleClose"
    >
        <button
            type="button"
            class="btn btn-secondary site-nav-login site-account-trigger"
            aria-haspopup="true"
            :aria-expanded="open"
            @click="openMenu"
        >
            {{ label }}
            <svg
                width="12"
                height="12"
                viewBox="0 0 20 20"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="site-nav-dropdown-caret"
                :class="{ 'is-open': open }"
            >
                <path d="M5 7.5L10 12.5L15 7.5" />
            </svg>
        </button>

        <div v-show="open" class="site-nav-dropdown-menu site-account-menu-list">
            <Link
                v-for="panel in panels"
                :key="panel.href"
                :href="panel.href"
                class="site-nav-dropdown-item"
                @click="open = false"
            >
                {{ panel.label }}
            </Link>
            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="site-nav-dropdown-item site-account-logout"
                @click="open = false"
            >
                Log out
            </Link>
        </div>
    </div>
</template>
