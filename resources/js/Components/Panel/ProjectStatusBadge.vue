<script setup>
defineProps({
    status: { type: String, required: true },
});

const labels = {
    proposed: 'Proposed',
    active: 'Active',
    on_hold: 'On hold',
    completed: 'Completed',
    cancelled: 'Cancelled',
};
</script>

<template>
    <span class="proj-status" :data-status="status">{{ labels[status] || status }}</span>
</template>
