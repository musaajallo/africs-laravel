<script setup>
import { Link } from '@inertiajs/vue3';

// Expects a Laravel length-aware paginator object (props.meta.links or
// top-level links depending on the resource). We use the `links` array that
// Inertia's `paginate()->through()` includes at the top level.
defineProps({
    links: { type: Array, default: () => [] },
    from: { type: Number, default: null },
    to: { type: Number, default: null },
    total: { type: Number, default: null },
});
</script>

<template>
    <div v-if="links.length > 3" class="panel-pagination">
        <p class="panel-pagination-summary">
            <span v-if="total !== null">{{ from }}–{{ to }} of {{ total }}</span>
        </p>
        <div class="panel-pagination-links">
            <template v-for="(link, i) in links" :key="i">
                <span
                    v-if="!link.url"
                    class="panel-pagination-link is-disabled"
                    v-html="link.label"
                />
                <Link
                    v-else
                    :href="link.url"
                    class="panel-pagination-link"
                    :class="{ 'is-active': link.active }"
                    preserve-scroll
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>
