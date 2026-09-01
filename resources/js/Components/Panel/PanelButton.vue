<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    // 'primary' | 'secondary' | 'danger'
    variant: { type: String, default: 'primary' },
    // when set, renders an Inertia <Link> instead of a <button>
    href: { type: String, default: null },
    type: { type: String, default: 'button' },
    disabled: { type: Boolean, default: false },
});

const classes = computed(() => ['btn', `btn-${props.variant}`, 'btn-sm']);
</script>

<template>
    <Link v-if="href" :href="href" :class="classes">
        <slot />
    </Link>
    <button v-else :type="type" :class="classes" :disabled="disabled">
        <slot />
    </button>
</template>
