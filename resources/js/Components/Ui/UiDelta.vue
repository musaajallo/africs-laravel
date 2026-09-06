<script setup>
import { computed } from 'vue';

const props = defineProps({
    // fractional change, e.g. 0.12 for +12%. null hides the pill.
    value: { type: Number, default: null },
    // when true a decrease is "good" (e.g. days-to-pay, overdue)
    invert: { type: Boolean, default: false },
    // label shown after the percentage, e.g. "vs prev 90d"
    caption: { type: String, default: '' },
});

const flat = computed(
    () => props.value === null || Math.abs(props.value) < 0.005,
);

const positive = computed(() =>
    props.invert ? props.value < 0 : props.value > 0,
);

const tone = computed(() => {
    if (flat.value) return 'bg-line-soft text-ink-soft';
    return positive.value
        ? 'bg-positive-wash text-positive'
        : 'bg-negative-wash text-negative';
});

const text = computed(() => {
    if (props.value === null) return '—';
    if (flat.value) return 'no change';
    const arrow = props.value > 0 ? '▲' : '▼';
    // Past a ~3× swing a percentage stops being legible — show a multiple.
    if (Math.abs(props.value) >= 2) {
        return `${arrow} ${(Math.abs(props.value) + 1).toFixed(1)}×`;
    }
    const pct = Math.abs(props.value * 100);
    return `${arrow} ${pct.toLocaleString(undefined, { maximumFractionDigits: pct < 10 ? 1 : 0 })}%`;
});
</script>

<template>
    <span class="inline-flex items-center gap-1 text-[11px] font-medium">
        <span
            class="inline-flex items-center gap-1 rounded-full px-1.5 py-0.5 font-semibold tabular-nums"
            :class="tone"
        >
            {{ text }}
        </span>
        <span v-if="caption" class="text-ink-faint">{{ caption }}</span>
    </span>
</template>
