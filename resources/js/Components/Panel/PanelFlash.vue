<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const message = ref(null);
const tone = ref('success');
let timer = null;

function show(value, kind) {
    if (!value) return;
    message.value = value;
    tone.value = kind;
    clearTimeout(timer);
    timer = setTimeout(() => (message.value = null), kind === 'error' ? 6000 : 4000);
}

watch(() => page.props.flash?.success, (value) => show(value, 'success'), { immediate: true });
watch(() => page.props.flash?.error, (value) => show(value, 'error'), { immediate: true });
</script>

<template>
    <Transition name="fade">
        <div v-if="message" class="panel-flash" :class="`is-${tone}`" role="status">
            {{ message }}
            <button type="button" class="panel-flash-close" @click="message = null">
                &times;
            </button>
        </div>
    </Transition>
</template>
