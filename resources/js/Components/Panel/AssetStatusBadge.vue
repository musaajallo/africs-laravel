<script setup>
defineProps({
    status: { type: String, required: true },
});

const labels = {
    in_use: 'In use',
    spare: 'Spare',
    repair: 'In repair',
    retired: 'Retired',
    lost: 'Lost / stolen',
};
</script>

<template>
    <span class="asset-status" :data-status="status">{{ labels[status] || status }}</span>
</template>
