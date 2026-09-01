<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    label: { type: String, required: true },
    items: { type: Array, required: true },
});

const emit = defineEmits(['navigate']);

const open = ref(false);
</script>

<template>
    <div class="site-mobile-group">
        <button
            type="button"
            class="site-nav-link site-mobile-group-trigger"
            :aria-expanded="open"
            @click="open = !open"
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

        <div v-show="open" class="site-mobile-submenu">
            <Link
                v-for="item in items"
                :key="item.href"
                :href="item.href"
                class="site-nav-link"
                @click="emit('navigate')"
            >
                {{ item.label }}
            </Link>
        </div>
    </div>
</template>
