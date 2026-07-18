<script setup>
import { onMounted, onUnmounted, ref } from 'vue';

defineProps({
    align: {
        type: String,
        default: 'right',
    },
});

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const open = ref(false);
</script>

<template>
    <div class="dropdown">
        <div @click="open = !open">
            <slot name="trigger" />
        </div>

        <div v-show="open" class="dropdown-overlay" @click="open = false"></div>

        <Transition name="fade">
            <div v-show="open" class="dropdown-menu" @click="open = false">
                <slot name="content" />
            </div>
        </Transition>
    </div>
</template>
