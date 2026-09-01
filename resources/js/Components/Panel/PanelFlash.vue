<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const message = ref(null);
let timer = null;

watch(
    () => page.props.flash?.success,
    (value) => {
        if (!value) return;
        message.value = value;
        clearTimeout(timer);
        timer = setTimeout(() => (message.value = null), 4000);
    },
    { immediate: true },
);
</script>

<template>
    <Transition name="fade">
        <div v-if="message" class="panel-flash" role="status">
            {{ message }}
            <button type="button" class="panel-flash-close" @click="message = null">
                &times;
            </button>
        </div>
    </Transition>
</template>
