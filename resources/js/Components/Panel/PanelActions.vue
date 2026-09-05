<script setup>
import { onMounted, onUnmounted, ref } from 'vue';

defineProps({
    label: { type: String, default: 'Actions' },
    align: { type: String, default: 'right' }, // 'right' | 'left'
});

const open = ref(false);
const root = ref(null);

const close = () => (open.value = false);
const onKey = (e) => e.key === 'Escape' && close();
const onOutside = (e) => root.value && !root.value.contains(e.target) && close();

onMounted(() => {
    document.addEventListener('keydown', onKey);
    document.addEventListener('click', onOutside);
});
onUnmounted(() => {
    document.removeEventListener('keydown', onKey);
    document.removeEventListener('click', onOutside);
});
</script>

<template>
    <div ref="root" class="panel-actions">
        <button
            type="button"
            class="btn btn-secondary btn-sm panel-actions-trigger"
            aria-haspopup="menu"
            :aria-expanded="open"
            @click.stop="open = !open"
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
                class="panel-actions-caret"
                :class="{ 'is-open': open }"
            >
                <path d="M5 7.5L10 12.5L15 7.5" />
            </svg>
        </button>

        <div
            v-show="open"
            class="panel-actions-menu"
            :class="`is-${align}`"
            role="menu"
            @click="close"
        >
            <slot />
        </div>
    </div>
</template>
