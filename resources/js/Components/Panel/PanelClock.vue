<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const now = ref(new Date());
let timer = null;

const time = computed(() =>
    now.value.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false }),
);
const seconds = computed(() => String(now.value.getSeconds()).padStart(2, '0'));
const day = computed(() =>
    now.value.toLocaleDateString([], { weekday: 'short', day: 'numeric', month: 'short' }),
);
const full = computed(() => now.value.toLocaleString());

onMounted(() => {
    timer = setInterval(() => (now.value = new Date()), 1000);
});
onUnmounted(() => clearInterval(timer));
</script>

<template>
    <div class="panel-clock" :title="full" role="timer" aria-live="off">
        <span class="panel-clock-day">{{ day }}</span>
        <span class="panel-clock-time">
            {{ time }}<span class="panel-clock-secs">{{ seconds }}</span>
        </span>
    </div>
</template>
