<script setup>
import { computed } from 'vue';

const props = defineProps({
    // [{ label, value }] or a plain array of numbers
    series: { type: Array, default: () => [] },
    height: { type: Number, default: 44 },
    tone: { type: String, default: 'brand' }, // brand | positive | negative | info
});

const values = computed(() =>
    props.series.map((p) => (typeof p === 'number' ? p : Number(p.value) || 0)),
);

const W = 260;
const PAD_Y = 4;

const geometry = computed(() => {
    const v = values.value;
    if (v.length < 2 || Math.max(...v) === Math.min(...v)) return null;

    const h = props.height;
    const max = Math.max(...v);
    const min = Math.min(...v);
    const span = max - min || 1;
    const step = W / (v.length - 1);

    const pts = v.map((n, i) => {
        const x = i * step;
        const y = PAD_Y + (h - PAD_Y * 2) * (1 - (n - min) / span);
        return [Math.round(x * 100) / 100, Math.round(y * 100) / 100];
    });

    return {
        line: pts.map((p) => p.join(',')).join(' '),
        area: `0,${h} ${pts.map((p) => p.join(',')).join(' ')} ${W},${h}`,
        last: pts[pts.length - 1],
    };
});

const stroke = computed(
    () =>
        ({
            brand: 'var(--ui-brand)',
            positive: 'var(--ui-positive)',
            negative: 'var(--ui-negative)',
            info: 'var(--ui-info)',
        })[props.tone] || 'var(--ui-brand)',
);
</script>

<template>
    <div class="overflow-hidden" :style="{ height: height + 'px' }">
        <svg
            v-if="geometry"
            :viewBox="`0 0 ${W} ${height}`"
            preserveAspectRatio="none"
            class="block h-full w-full"
            role="img"
        >
            <defs>
                <linearGradient :id="`sg-${tone}`" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" :stop-color="stroke" stop-opacity="0.16" />
                    <stop offset="100%" :stop-color="stroke" stop-opacity="0" />
                </linearGradient>
            </defs>
            <polygon :points="geometry.area" :fill="`url(#sg-${tone})`" />
            <polyline
                :points="geometry.line"
                fill="none"
                :stroke="stroke"
                stroke-width="2"
                stroke-linejoin="round"
                stroke-linecap="round"
                vector-effect="non-scaling-stroke"
            />
        </svg>
        <div
            v-else
            class="flex h-full items-center justify-center text-[11px] text-ink-faint"
        >
            Not enough movement to chart.
        </div>
    </div>
</template>
