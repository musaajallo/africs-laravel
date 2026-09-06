<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import ActivityFeed from '@/Components/Panel/ActivityFeed.vue';
import { useAuth } from '@/Composables/useAuth.js';

const props = defineProps({
    base: { type: String, required: true },
    metrics: { type: Array, default: () => [] },
    overdueInvoices: { type: Array, default: null },
    expiringProformas: { type: Array, default: null },
    activity: { type: Array, default: null },
});

const { user, can } = useAuth();

const firstName = computed(() => (user.value?.name || '').split(' ')[0]);

function money(value, currency) {
    return `${currency} ${Number(value).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

// Whole units only — tiles are summaries, not statements.
const round = (v) => Number(v).toLocaleString(undefined, { maximumFractionDigits: 0 });

const quickActions = computed(() =>
    [
        can('proformas.manage') && { label: 'New proforma', href: route('console.proformas.create') },
        can('invoices.manage') && { label: 'New invoice', href: route('console.invoices.create') },
        can('payments.manage') && { label: 'Record payment', href: route('console.payments.create') },
        can('leads.manage') && { label: 'Add lead', href: route('console.leads.create') },
        can('assets.manage') && { label: 'Add asset', href: route('console.assets.create') },
    ].filter(Boolean),
);

const hasAttention = computed(
    () =>
        (props.overdueInvoices && props.overdueInvoices.length) ||
        (props.expiringProformas && props.expiringProformas.length),
);
</script>

<template>
    <Head title="Console" />

    <ConsoleLayout>
        <template #title>Dashboard</template>

        <div class="panel-page">
            <div class="panel-page-header">
                <div>
                    <h1 class="panel-page-title">Welcome back, {{ firstName }}</h1>
                    <p class="panel-page-lead">A snapshot of what needs your attention.</p>
                </div>
                <div v-if="quickActions.length" class="panel-page-header-actions">
                    <PanelButton
                        v-for="(a, i) in quickActions"
                        :key="a.label"
                        :href="a.href"
                        :variant="i === 0 ? 'primary' : 'secondary'"
                    >{{ a.label }}</PanelButton>
                </div>
            </div>

            <div v-if="metrics.length" class="dash-tiles">
                <component
                    :is="m.href ? Link : 'div'"
                    v-for="m in metrics"
                    :key="m.key"
                    :href="m.href"
                    class="dash-tile"
                    :class="{ 'is-link': m.href, 'is-danger': m.tone === 'danger' }"
                >
                    <span class="dash-tile-label">{{ m.label }}</span>
                    <span class="dash-tile-value">
                        <span v-if="m.currency" class="dash-tile-cur">{{ m.currency }}</span>{{ m.currency ? round(m.value) : Number(m.value).toLocaleString() }}
                    </span>
                    <span v-if="m.sub" class="dash-tile-sub">{{ m.sub }}</span>
                </component>
            </div>

            <div class="client-detail-grid" style="margin-top: 1.5rem">
                <section class="panel-card">
                    <h2 class="panel-card-title">Needs attention</h2>

                    <template v-if="overdueInvoices && overdueInvoices.length">
                        <h3 class="dash-sub">Overdue invoices</h3>
                        <ul class="dash-list">
                            <li v-for="inv in overdueInvoices" :key="inv.id">
                                <Link :href="route('console.invoices.show', inv.id)" class="panel-link" style="padding: 0">
                                    {{ inv.number }}
                                </Link>
                                <span class="panel-cell-muted"> · {{ inv.client }}</span>
                                <span class="dash-list-right">
                                    {{ money(inv.balance, inv.currency) }}
                                    <span class="dash-badge is-danger">{{ inv.days_overdue }}d</span>
                                </span>
                            </li>
                        </ul>
                    </template>

                    <template v-if="expiringProformas && expiringProformas.length">
                        <h3 class="dash-sub">Proformas expiring soon</h3>
                        <ul class="dash-list">
                            <li v-for="pf in expiringProformas" :key="pf.id">
                                <Link :href="route('console.proformas.show', pf.id)" class="panel-link" style="padding: 0">
                                    {{ pf.number }}
                                </Link>
                                <span class="panel-cell-muted"> · {{ pf.client }}</span>
                                <span class="dash-list-right">
                                    {{ money(pf.total, pf.currency) }}
                                    <span class="dash-badge">until {{ pf.valid_until }}</span>
                                </span>
                            </li>
                        </ul>
                    </template>

                    <p v-if="!hasAttention" class="panel-cell-muted">
                        Nothing overdue or expiring. All clear.
                    </p>
                </section>

                <section v-if="activity" class="panel-card">
                    <div class="client-card-head">
                        <h2 class="panel-card-title">Recent activity</h2>
                        <Link :href="route('console.activity.index')" class="panel-link">View all</Link>
                    </div>
                    <ActivityFeed :items="activity" empty="Nothing recorded yet." />
                </section>
            </div>
        </div>
    </ConsoleLayout>
</template>
