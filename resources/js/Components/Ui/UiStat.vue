<script setup>
import { computed } from 'vue';
import UiDelta from '@/Components/Ui/UiDelta.vue';

const props = defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    // small text before the value, e.g. the currency code
    prefix: { type: String, default: '' },
    // small text after the value, e.g. "%", "days"
    suffix: { type: String, default: '' },
    hint: { type: String, default: '' },
    delta: { type: Number, default: null },
    deltaCaption: { type: String, default: '' },
    invertDelta: { type: Boolean, default: false },
    // "muted" when there is no data to show
    muted: { type: Boolean, default: false },
});

const display = computed(() => {
    if (typeof props.value === 'number') {
        return props.value.toLocaleString(undefined, { maximumFractionDigits: 0 });
    }
    return props.value;
});
</script>

<template>
    <div class="flex flex-col gap-1.5">
        <span
            class="text-[11px] font-semibold uppercase tracking-[0.07em] text-ink-faint"
        >
            {{ label }}
        </span>
        <span
            class="flex items-baseline gap-1 font-bold leading-none tabular-nums"
            :class="muted ? 'text-ink-faint' : 'text-ink'"
        >
            <span v-if="prefix" class="text-[12px] font-semibold text-ink-faint">
                {{ prefix }}
            </span>
            <span class="text-[22px]">{{ display }}</span>
            <span v-if="suffix" class="text-[12px] font-semibold text-ink-faint">
                {{ suffix }}
            </span>
        </span>
        <div v-if="delta !== null || hint" class="flex flex-wrap items-center gap-x-2 gap-y-1">
            <UiDelta
                v-if="delta !== null"
                :value="delta"
                :invert="invertDelta"
                :caption="deltaCaption"
            />
            <span v-if="hint" class="text-xs text-ink-faint">{{ hint }}</span>
        </div>
    </div>
</template>
