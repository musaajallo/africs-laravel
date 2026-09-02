<script setup>
defineProps({
    status: { type: String, required: true },
});

const labels = {
    new: 'New',
    contacted: 'Contacted',
    qualified: 'Qualified',
    lost: 'Lost',
    converted: 'Converted',
};
</script>

<template>
    <span class="lead-status" :data-status="status">{{ labels[status] || status }}</span>
</template>
