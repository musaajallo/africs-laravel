<script setup>
defineProps({
    status: { type: String, required: true },
});

const labels = {
    draft: 'Draft',
    sent: 'Sent',
    accepted: 'Accepted',
    declined: 'Declined',
    expired: 'Expired',
    converted: 'Converted',
    partially_paid: 'Partly paid',
    paid: 'Paid',
    overdue: 'Overdue',
    void: 'Void',
};
</script>

<template>
    <span class="doc-status" :data-status="status">{{ labels[status] || status }}</span>
</template>
