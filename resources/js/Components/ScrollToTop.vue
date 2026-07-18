<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { cookieBannerVisible } from '@/cookieConsent';

const visible = ref(false);

function onScroll() {
    visible.value = window.scrollY > 400;
}

onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
});

onUnmounted(() => window.removeEventListener('scroll', onScroll));

const style = computed(() => ({
    bottom: cookieBannerVisible.value ? '6.5rem' : '1.5rem',
}));

function scrollToTop() {
    const behavior = window.matchMedia('(prefers-reduced-motion: reduce)')
        .matches
        ? 'auto'
        : 'smooth';

    window.scrollTo({ top: 0, behavior });
}
</script>

<template>
    <Transition name="fade">
        <button
            v-if="visible"
            type="button"
            class="scroll-top-btn"
            :style="style"
            aria-label="Scroll to top"
            @click="scrollToTop"
        >
            <svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M10 15V5M10 5L4 11M10 5l6 6"
                />
            </svg>
        </button>
    </Transition>
</template>
