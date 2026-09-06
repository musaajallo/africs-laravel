<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import Dropdown from '@/Components/Dropdown.vue';
import ActivityFeed from '@/Components/Panel/ActivityFeed.vue';
import UiCard from '@/Components/Ui/UiCard.vue';
import UiStat from '@/Components/Ui/UiStat.vue';
import UiTabs from '@/Components/Ui/UiTabs.vue';
import UiSegmented from '@/Components/Ui/UiSegmented.vue';
import UiSparkline from '@/Components/Ui/UiSparkline.vue';
import UiBars from '@/Components/Ui/UiBars.vue';
import UiMeter from '@/Components/Ui/UiMeter.vue';
import { useAuth } from '@/Composables/useAuth.js';

const props = defineProps({
    base: { type: String, required: true },
    metrics: { type: Array, default: () => [] },
    overdueInvoices: { type: Array, default: null },
    expiringProformas: { type: Array, default: null },
    activity: { type: Array, default: null },
    canInsights: { type: Boolean, default: false },
    insightsRange: { type: String, default: '90d' },
    insightsRanges: { type: Array, default: () => [] },
    insights: { type: Object, default: null },
});

const { user, can } = useAuth();
const firstName = computed(() => (user.value?.name || '').split(' ')[0]);

/* ---- tabs + range ---- */

const tabs = computed(() =>
    [
        { key: 'overview', label: 'Overview' },
        props.canInsights && { key: 'insights', label: 'Business insights' },
    ].filter(Boolean),
);

const startOnInsights =
    typeof window !== 'undefined' &&
    new URLSearchParams(window.location.search).get('tab') === 'insights';

const tab = ref(startOnInsights && props.canInsights ? 'insights' : 'overview');
const range = ref(props.insightsRange);
const loadingInsights = ref(false);

function loadInsights() {
    loadingInsights.value = true;
    router.reload({
        only: ['insights'],
        data: { range: range.value },
        onFinish: () => (loadingInsights.value = false),
    });
}

function selectTab(key) {
    tab.value = key;
    const url = new URL(window.location.href);
    if (key === 'insights') url.searchParams.set('tab', 'insights');
    else url.searchParams.delete('tab');
    window.history.replaceState({}, '', url);
    if (key === 'insights' && !props.insights) loadInsights();
}

watch(range, loadInsights);

onMounted(() => {
    if (tab.value === 'insights' && !props.insights) loadInsights();
});

/* ---- formatting ---- */

function money(value, opts = {}) {
    if (value === null || value === undefined) return '—';
    return Number(value).toLocaleString(undefined, {
        maximumFractionDigits: 0,
        ...opts,
    });
}

function pct(value, digits = 0) {
    if (value === null || value === undefined) return '—';
    return `${(value * 100).toLocaleString(undefined, { maximumFractionDigits: digits })}%`;
}

const round = (v) => Number(v).toLocaleString(undefined, { maximumFractionDigits: 0 });

function plural(n, word) {
    return `${n} ${word}${n === 1 ? '' : 's'}`;
}

/* ---- overview quick actions ---- */

const quickActions = computed(() =>
    [
        can('proformas.manage') && {
            label: 'New proforma',
            href: route('console.proformas.create'),
        },
        can('invoices.manage') && {
            label: 'New invoice',
            href: route('console.invoices.create'),
        },
        can('payments.manage') && {
            label: 'Record payment',
            href: route('console.payments.create'),
        },
        can('leads.manage') && { label: 'Add lead', href: route('console.leads.create') },
        can('assets.manage') && {
            label: 'Add asset',
            href: route('console.assets.create'),
        },
    ].filter(Boolean),
);

const hasAttention = computed(
    () =>
        (props.overdueInvoices && props.overdueInvoices.length) ||
        (props.expiringProformas && props.expiringProformas.length),
);

/* ---- insights shortcuts ---- */

const rev = computed(() => props.insights?.revenue ?? {});
const cli = computed(() => props.insights?.clients ?? {});
const prj = computed(() => props.insights?.projects ?? {});
const sal = computed(() => props.insights?.sales ?? {});
const rangeMeta = computed(() => props.insights?.range ?? {});
const assumptions = computed(() => props.insights?.assumptions ?? {});

const lifespan = computed(() => {
    const m = cli.value.avg_lifespan_months;
    if (!m) return { value: '—', suffix: '' };
    if (m < 1) return { value: '<1', suffix: 'month' };
    return { value: m.toFixed(m < 10 ? 1 : 0), suffix: 'months' };
});
</script>

<template>
    <Head title="Console" />

    <ConsoleLayout>
        <template #title>Dashboard</template>

        <div class="ui" data-ui-root>
            <div
                class="-mx-8 -mb-16 -mt-9 min-h-[calc(100vh-3.75rem)] bg-canvas px-8 pb-16 pt-7 max-[820px]:-mx-[1.15rem] max-[820px]:px-[1.15rem]"
            >
                <div class="mx-auto flex max-w-[78rem] flex-col gap-5">
                    <!-- header -->
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h1 class="text-xl font-bold text-ink">
                                Welcome back, {{ firstName }}
                            </h1>
                            <p class="mt-0.5 text-sm text-ink-soft">
                                {{
                                    tab === 'insights'
                                        ? 'How the business is trending — indicative management figures.'
                                        : 'A snapshot of what needs your attention.'
                                }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <UiSegmented
                                v-if="tab === 'insights' && insightsRanges.length"
                                v-model="range"
                                :options="insightsRanges"
                            />
                            <Dropdown v-if="quickActions.length" align="right">
                                <template #trigger>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1.5 rounded-md bg-brand px-3 py-2 text-sm font-semibold text-white shadow-card transition-opacity hover:opacity-95"
                                    >
                                        Create
                                        <svg
                                            width="14"
                                            height="14"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </template>
                                <template #content>
                                    <div class="min-w-[12rem] py-1">
                                        <Link
                                            v-for="a in quickActions"
                                            :key="a.label"
                                            :href="a.href"
                                            class="block px-3 py-2 text-sm text-ink hover:bg-brand-wash"
                                        >
                                            {{ a.label }}
                                        </Link>
                                    </div>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <UiTabs
                        v-if="tabs.length > 1"
                        :tabs="tabs"
                        :model-value="tab"
                        @update:model-value="selectTab"
                    />

                    <!-- ============ OVERVIEW ============ -->
                    <div v-show="tab === 'overview'" class="flex flex-col gap-5">
                        <div
                            v-if="metrics.length"
                            class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4"
                        >
                            <component
                                :is="m.href ? Link : 'div'"
                                v-for="m in metrics"
                                :key="m.key"
                                :href="m.href"
                                class="rounded-card border bg-surface px-4 py-3.5 shadow-card transition-colors"
                                :class="[
                                    m.href ? 'hover:border-brand/40' : '',
                                    m.tone === 'danger' ? 'border-negative/30' : 'border-line',
                                ]"
                            >
                                <UiStat
                                    :label="m.label"
                                    :value="m.currency ? round(m.value) : Number(m.value).toLocaleString()"
                                    :prefix="m.currency || ''"
                                    :hint="m.sub || ''"
                                    :muted="m.currency && Number(m.value) === 0"
                                />
                            </component>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-2">
                            <UiCard title="Needs attention">
                                <template
                                    v-if="overdueInvoices && overdueInvoices.length"
                                >
                                    <p
                                        class="mb-1.5 mt-1 text-[11px] font-semibold uppercase tracking-wide text-ink-faint"
                                    >
                                        Overdue invoices
                                    </p>
                                    <ul class="flex flex-col divide-y divide-line-soft">
                                        <li
                                            v-for="inv in overdueInvoices"
                                            :key="inv.id"
                                            class="flex items-center justify-between gap-3 py-2 text-sm"
                                        >
                                            <span class="min-w-0 truncate">
                                                <Link
                                                    :href="route('console.invoices.show', inv.id)"
                                                    class="font-medium text-brand hover:underline"
                                                >{{ inv.number }}</Link>
                                                <span class="text-ink-faint">
                                                    · {{ inv.client }}</span
                                                >
                                            </span>
                                            <span
                                                class="flex shrink-0 items-center gap-2 tabular-nums"
                                            >
                                                {{ inv.currency }} {{ money(inv.balance) }}
                                                <span
                                                    class="rounded-full bg-negative-wash px-1.5 py-0.5 text-[11px] font-semibold text-negative"
                                                >{{ inv.days_overdue }}d</span>
                                            </span>
                                        </li>
                                    </ul>
                                </template>

                                <template
                                    v-if="expiringProformas && expiringProformas.length"
                                >
                                    <p
                                        class="mb-1.5 mt-3 text-[11px] font-semibold uppercase tracking-wide text-ink-faint"
                                    >
                                        Proformas expiring soon
                                    </p>
                                    <ul class="flex flex-col divide-y divide-line-soft">
                                        <li
                                            v-for="pf in expiringProformas"
                                            :key="pf.id"
                                            class="flex items-center justify-between gap-3 py-2 text-sm"
                                        >
                                            <span class="min-w-0 truncate">
                                                <Link
                                                    :href="route('console.proformas.show', pf.id)"
                                                    class="font-medium text-brand hover:underline"
                                                >{{ pf.number }}</Link>
                                                <span class="text-ink-faint">
                                                    · {{ pf.client }}</span
                                                >
                                            </span>
                                            <span
                                                class="flex shrink-0 items-center gap-2 tabular-nums text-ink-soft"
                                            >
                                                {{ pf.currency }} {{ money(pf.total) }}
                                                <span
                                                    class="rounded-full bg-line-soft px-1.5 py-0.5 text-[11px] font-medium"
                                                >until {{ pf.valid_until }}</span>
                                            </span>
                                        </li>
                                    </ul>
                                </template>

                                <p
                                    v-if="!hasAttention"
                                    class="py-2 text-sm text-ink-faint"
                                >
                                    Nothing overdue or expiring. All clear.
                                </p>
                            </UiCard>

                            <UiCard v-if="activity" title="Recent activity">
                                <template #actions>
                                    <Link
                                        :href="route('console.activity.index')"
                                        class="text-xs font-medium text-brand hover:underline"
                                    >View all</Link>
                                </template>
                                <ActivityFeed
                                    :items="activity"
                                    empty="Nothing recorded yet."
                                />
                            </UiCard>
                        </div>
                    </div>

                    <!-- ============ BUSINESS INSIGHTS ============ -->
                    <div v-if="tab === 'insights'" class="flex flex-col gap-5">
                        <!-- loading skeleton -->
                        <div
                            v-if="!insights"
                            class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
                        >
                            <div
                                v-for="i in 6"
                                :key="i"
                                class="h-44 animate-pulse rounded-card border border-line bg-line-soft/50"
                            />
                        </div>

                        <template v-else>
                            <p class="-mt-1 text-xs text-ink-faint">
                                {{ rangeMeta.from }} → {{ rangeMeta.to }} ·
                                vs the {{ rangeMeta.prev_label }} · figures in
                                {{ insights.currency }}
                            </p>

                            <!-- ---- Revenue & profit ---- -->
                            <section class="flex flex-col gap-2.5">
                                <h2 class="dash-section">Revenue &amp; profit</h2>
                                <div class="grid items-start gap-4 lg:grid-cols-3">
                                    <UiCard
                                        class="lg:col-span-2"
                                        title="Cash collected"
                                        subtitle="Payments received, converted to the base currency"
                                    >
                                        <div class="flex items-start justify-between gap-4">
                                            <UiStat
                                                label="Collected"
                                                :prefix="insights.currency"
                                                :value="money(rev.collected)"
                                                :delta="rev.collected_delta"
                                                :delta-caption="`vs ${insights.currency} ${money(rev.collected_prev)}`"
                                            />
                                            <span class="mt-1 shrink-0 text-[11px] text-ink-faint">
                                                cumulative ↗
                                            </span>
                                        </div>

                                        <UiSparkline
                                            class="mt-2"
                                            :series="rev.series"
                                            :height="48"
                                        />

                                        <div
                                            class="mt-3 grid grid-cols-2 gap-x-4 gap-y-3 border-t border-line-soft pt-3 sm:grid-cols-3"
                                        >
                                            <UiStat
                                                label="Billed"
                                                :prefix="insights.currency"
                                                :value="money(rev.billed)"
                                                :delta="rev.billed_delta"
                                            />
                                            <div class="flex flex-col gap-1.5">
                                                <span class="dash-stat-label">Collection rate</span>
                                                <span class="dash-stat-value">{{
                                                    pct(rev.collection_rate)
                                                }}</span>
                                                <UiMeter
                                                    class="mt-0.5"
                                                    :value="rev.collection_rate || 0"
                                                />
                                            </div>
                                            <UiStat
                                                label="Est. gross profit"
                                                :prefix="insights.currency"
                                                :value="money(rev.gross_profit)"
                                                :hint="`${assumptions.gross_margin_pct}% margin`"
                                            />
                                        </div>
                                    </UiCard>

                                    <UiCard title="Top clients" subtitle="By cash collected">
                                        <UiBars :data="cli.top" tone="brand" empty="No payments in this period." />
                                        <p
                                            v-if="cli.concentration"
                                            class="mt-3 border-t border-line-soft pt-2 text-xs text-ink-faint"
                                        >
                                            Largest client is
                                            <span class="font-semibold text-ink-soft">{{
                                                pct(cli.concentration)
                                            }}</span>
                                            of collections.
                                        </p>
                                    </UiCard>
                                </div>
                            </section>

                            <!-- ---- Customer economics ---- -->
                            <section class="flex flex-col gap-2.5">
                                <h2 class="dash-section">Customer economics</h2>
                                <div class="grid items-start gap-4 lg:grid-cols-3">
                                    <UiCard class="lg:col-span-2" title="Unit economics">
                                        <div
                                            class="grid grid-cols-2 gap-x-4 gap-y-4 sm:grid-cols-3"
                                        >
                                            <UiStat
                                                label="Avg lifetime value"
                                                :prefix="insights.currency"
                                                :value="money(cli.avg_ltv)"
                                                :muted="cli.avg_ltv === null"
                                                :hint="cli.paying ? plural(cli.paying, 'paying client') : 'no payments yet'"
                                            />
                                            <UiStat
                                                v-if="cli.cac !== null"
                                                label="Acquisition cost"
                                                :prefix="insights.currency"
                                                :value="money(cli.cac)"
                                                :hint="plural(cli.new, 'new client')"
                                            />
                                            <div v-else class="flex flex-col gap-1.5">
                                                <span class="dash-stat-label">Acquisition cost</span>
                                                <span class="text-sm font-semibold text-ink-faint"
                                                    >Not set</span
                                                >
                                                <Link
                                                    :href="route('console.settings.edit')"
                                                    class="text-xs font-medium text-brand hover:underline"
                                                    >Add S&amp;M spend →</Link
                                                >
                                            </div>
                                            <UiStat
                                                label="Avg relationship"
                                                :value="lifespan.value"
                                                :suffix="lifespan.suffix"
                                                :muted="lifespan.value === '—'"
                                            />
                                        </div>

                                        <div class="mt-4 border-t border-line-soft pt-3">
                                            <div class="flex items-end justify-between gap-3">
                                                <span class="dash-stat-label">LTV : CAC</span>
                                                <span
                                                    v-if="cli.ltv_cac_ratio !== null"
                                                    class="text-[13px] text-ink-faint"
                                                >
                                                    target 3×
                                                </span>
                                            </div>
                                            <template v-if="cli.ltv_cac_ratio !== null">
                                                <div class="mt-1 flex items-center gap-3">
                                                    <span
                                                        class="text-[26px] font-bold leading-none tabular-nums text-ink"
                                                        >{{ cli.ltv_cac_ratio.toFixed(1)
                                                        }}<span class="text-base text-ink-faint">×</span></span
                                                    >
                                                    <UiMeter
                                                        class="flex-1"
                                                        :value="Math.min(1, cli.ltv_cac_ratio / 5)"
                                                        :target="0.6"
                                                        :tone="cli.ltv_cac_ratio >= 3 ? 'positive' : 'gold'"
                                                    />
                                                </div>
                                                <p class="mt-1.5 text-xs text-ink-faint">
                                                    Gross-profit lifetime value returned for every
                                                    {{ insights.currency }} 1 of acquisition spend.
                                                </p>
                                            </template>
                                            <p v-else class="mt-1 text-sm text-ink-faint">
                                                Needs acquisition spend and payment history.
                                            </p>
                                        </div>
                                    </UiCard>

                                    <UiCard title="Client base">
                                        <div class="flex flex-col gap-4">
                                            <UiStat
                                                label="New this period"
                                                :value="cli.new ?? 0"
                                                :delta="cli.new_delta"
                                                :hint="`${cli.new_prev} in the ${rangeMeta.prev_label}`"
                                            />
                                            <UiStat label="Active clients" :value="cli.active ?? 0" />
                                            <UiStat
                                                label="Have ever paid"
                                                :value="cli.paying ?? 0"
                                            />
                                        </div>
                                    </UiCard>
                                </div>
                            </section>

                            <!-- ---- Projects & sales ---- -->
                            <section class="flex flex-col gap-2.5">
                                <h2 class="dash-section">Projects &amp; sales</h2>
                                <div class="grid items-start gap-4 lg:grid-cols-3">
                                    <UiCard
                                        v-if="can('projects.view')"
                                        title="Average project value"
                                    >
                                        <UiStat
                                            label="Mean budget"
                                            :prefix="insights.currency"
                                            :value="money(prj.avg_value)"
                                            :delta="prj.avg_value_delta"
                                            :muted="prj.avg_value === null"
                                            :hint="`${plural(prj.valued_count, 'budgeted project')} · ${insights.currency} ${money(prj.pipeline_value)} total`"
                                        />
                                        <div class="mt-3 border-t border-line-soft pt-3">
                                            <p class="dash-mini-label">By service line</p>
                                            <UiBars
                                                :data="prj.by_service_line"
                                                tone="brand"
                                                empty="No budgeted projects."
                                            />
                                        </div>
                                    </UiCard>

                                    <UiCard
                                        v-if="can('proformas.view')"
                                        title="Sales performance"
                                    >
                                        <div class="flex items-end justify-between gap-3">
                                            <span class="dash-stat-label">Proforma win rate</span>
                                        </div>
                                        <div class="mt-1 flex items-center gap-3">
                                            <span
                                                class="text-[26px] font-bold leading-none tabular-nums text-ink"
                                                >{{ pct(sal.proforma_win_rate) }}</span
                                            >
                                            <UiMeter
                                                class="flex-1"
                                                :value="sal.proforma_win_rate || 0"
                                                tone="positive"
                                            />
                                        </div>
                                        <p class="mt-1.5 text-xs text-ink-faint">
                                            {{ sal.proforma_won }} won of
                                            {{ sal.proforma_decided }} decided
                                        </p>
                                        <div
                                            class="mt-3 grid grid-cols-2 gap-4 border-t border-line-soft pt-3"
                                        >
                                            <UiStat
                                                label="Avg proforma"
                                                :prefix="insights.currency"
                                                :value="money(sal.avg_proforma_value)"
                                                :muted="sal.avg_proforma_value === null"
                                            />
                                            <UiStat
                                                label="Issue → paid"
                                                :value="sal.avg_days_to_pay ?? '—'"
                                                :suffix="sal.avg_days_to_pay !== null ? 'days' : ''"
                                                :delta="sal.avg_days_to_pay_delta"
                                                invert-delta
                                                :muted="sal.avg_days_to_pay === null"
                                            />
                                        </div>
                                    </UiCard>

                                    <UiCard
                                        v-if="can('leads.view')"
                                        title="Lead funnel"
                                    >
                                        <UiStat
                                            label="Lead → client conversion"
                                            :value="pct(sal.lead_conversion)"
                                            :hint="`${sal.leads_converted} converted of ${sal.leads_total} leads`"
                                            :muted="sal.lead_conversion === null"
                                        />
                                        <div class="mt-3 border-t border-line-soft pt-3">
                                            <p class="dash-mini-label">By channel</p>
                                            <UiBars
                                                :data="sal.leads_by_channel"
                                                tone="info"
                                                empty="No leads in this period."
                                            />
                                        </div>
                                    </UiCard>
                                </div>
                            </section>

                            <p class="text-[11px] leading-relaxed text-ink-faint">
                                Indicative management figures. Gross profit and LTV:CAC
                                assume a {{ assumptions.gross_margin_pct }}% blended
                                margin<template v-if="assumptions.configured"> and
                                    {{ insights.currency }}
                                    {{ money(assumptions.monthly_acquisition_spend) }}/month
                                    acquisition spend</template
                                >, both set in
                                <Link
                                    :href="route('console.settings.edit')"
                                    class="font-medium text-brand hover:underline"
                                    >Settings</Link
                                >. Foreign currency is converted at the latest recorded
                                rate.
                            </p>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </ConsoleLayout>
</template>
