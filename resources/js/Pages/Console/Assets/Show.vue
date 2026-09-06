<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import PanelActions from '@/Components/Panel/PanelActions.vue';
import PanelConfirm from '@/Components/Panel/PanelConfirm.vue';
import PanelField from '@/Components/Panel/PanelField.vue';
import PanelTable from '@/Components/Panel/PanelTable.vue';
import AssetStatusBadge from '@/Components/Panel/AssetStatusBadge.vue';
import ActivityFeed from '@/Components/Panel/ActivityFeed.vue';
import { useAuth } from '@/Composables/useAuth.js';

const props = defineProps({
    asset: { type: Object, required: true },
    people: { type: Array, default: () => [] },
    statuses: { type: Object, default: () => ({}) },
    activity: { type: Array, default: () => [] },
});

const { can } = useAuth();
const canManage = computed(() => can('assets.manage') && !props.asset.archived);
const confirmRemove = ref(false);
const today = new Date().toISOString().slice(0, 10);

const money = computed(() =>
    props.asset.purchase_cost
        ? `${props.asset.purchase_currency || ''} ${Number(props.asset.purchase_cost).toLocaleString()}`.trim()
        : null,
);

const details = computed(() => [
    { label: 'Category', value: props.asset.category },
    { label: 'Make / model', value: [props.asset.make, props.asset.model].filter(Boolean).join(' ') },
    { label: 'Serial number', value: props.asset.serial_number },
    { label: 'Asset tag', value: props.asset.asset_tag },
    { label: 'Condition', value: props.asset.condition },
    { label: 'Location', value: props.asset.location },
    { label: 'Manufactured', value: props.asset.manufactured_on },
    { label: 'Purchased', value: props.asset.purchased_on },
    { label: 'Cost', value: money.value },
    { label: 'Supplier', value: props.asset.supplier },
    { label: 'Warranty until', value: props.asset.warranty_until },
    { label: 'Added by', value: props.asset.created_by },
]);

const dep = computed(() => props.asset.depreciation || {});
const methodLabels = { none: 'None', straight_line: 'Straight line', reducing_balance: 'Reducing balance' };
const depAmount = (v) => (v == null ? '—' : `${dep.value.currency || ''} ${Number(v).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`.trim());

const assignForm = useForm({ user_id: '', assigned_on: today, notes: '' });

function assign() {
    assignForm.post(route('console.assets.assign', props.asset.id), {
        preserveScroll: true,
        onSuccess: () => assignForm.reset(),
    });
}
function unassign() {
    router.post(route('console.assets.unassign', props.asset.id), {}, { preserveScroll: true });
}
function setStatus(status) {
    router.put(route('console.assets.status', props.asset.id), { status }, { preserveScroll: true });
}
function remove() {
    router.delete(route('console.assets.destroy', props.asset.id));
}
function restore() {
    router.put(route('console.assets.restore', props.asset.id));
}
</script>

<template>
    <Head :title="asset.name" />

    <ConsoleLayout>
        <template #title>Assets</template>

        <div class="panel-page">
            <PanelPageHeader :title="asset.name" :subtitle="asset.archived ? 'Removed asset' : null">
                <template #actions>
                    <PanelButton variant="secondary" :href="route('console.assets.index')">All assets</PanelButton>

                    <PanelActions>
                        <Link
                            v-if="canManage"
                            :href="route('console.assets.edit', asset.id)"
                            class="panel-actions-item"
                        >Edit</Link>
                        <button
                            v-if="can('assets.manage') && asset.archived"
                            type="button"
                            class="panel-actions-item"
                            @click="restore"
                        >Restore</button>
                        <template v-if="canManage">
                            <div class="panel-actions-sep"></div>
                            <button type="button" class="panel-actions-item is-danger" @click="confirmRemove = true">
                                Remove
                            </button>
                        </template>
                    </PanelActions>
                </template>
            </PanelPageHeader>

            <PanelConfirm
                v-model="confirmRemove"
                title="Remove this asset?"
                :message="`${asset.name} will be hidden from the register. It can be restored.`"
                confirm-label="Remove"
                @confirm="remove"
            />

            <div style="margin: -0.25rem 0 1.25rem; display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap">
                <AssetStatusBadge :status="asset.status" />
            </div>

            <div v-if="canManage" class="panel-toolbar" style="margin-bottom: 1.25rem">
                <PanelButton
                    v-for="(label, key) in statuses"
                    :key="key"
                    variant="secondary"
                    :disabled="asset.status === key"
                    @click="setStatus(key)"
                >
                    Mark {{ label.toLowerCase() }}
                </PanelButton>
            </div>

            <div class="client-detail-grid">
                <section class="panel-card">
                    <h2 class="panel-card-title">Details</h2>
                    <dl class="client-dl">
                        <template v-for="item in details" :key="item.label">
                            <div v-if="item.value">
                                <dt>{{ item.label }}</dt>
                                <dd style="text-transform: capitalize">{{ item.value }}</dd>
                            </div>
                        </template>
                    </dl>
                    <div v-if="asset.notes" class="client-address">
                        <dt>Notes</dt>
                        <dd style="white-space: pre-line">{{ asset.notes }}</dd>
                    </div>
                </section>

                <section class="panel-card">
                    <h2 class="panel-card-title">Assignment</h2>
                    <p v-if="asset.assignee">
                        Held by <strong>{{ asset.assignee }}</strong>
                        <span v-if="asset.assigned_on" class="panel-cell-muted"> since {{ asset.assigned_on }}</span>
                    </p>
                    <p v-else class="panel-cell-muted">In stock — not assigned.</p>

                    <div v-if="canManage" style="margin-top: 0.75rem">
                        <PanelButton
                            v-if="asset.assignee"
                            variant="secondary"
                            @click="unassign"
                        >Return to stock</PanelButton>

                        <form v-else class="panel-form-grid" @submit.prevent="assign">
                            <PanelField label="Assign to" :error="assignForm.errors.user_id" required>
                                <select v-model="assignForm.user_id" class="field-input">
                                    <option value="" disabled>Choose a person</option>
                                    <option v-for="p in people" :key="p.id" :value="p.id">{{ p.name }}</option>
                                </select>
                            </PanelField>
                            <PanelField label="From" :error="assignForm.errors.assigned_on">
                                <input v-model="assignForm.assigned_on" type="date" :max="today" class="field-input" />
                            </PanelField>
                            <PanelField label="Note" :error="assignForm.errors.notes">
                                <input v-model="assignForm.notes" type="text" class="field-input" />
                            </PanelField>
                            <div style="display: flex; align-items: flex-end">
                                <PanelButton type="submit" :disabled="assignForm.processing">Assign</PanelButton>
                            </div>
                        </form>
                    </div>

                    <div v-if="asset.assignments.length" style="margin-top: 1rem">
                        <h3 class="panel-card-title" style="font-size: 0.85rem">History</h3>
                        <PanelTable
                            :columns="['Person', 'From', 'To']"
                            :rows="asset.assignments"
                            empty=""
                        >
                            <template #row="{ row }">
                                <td>{{ row.user }}</td>
                                <td class="panel-cell-muted">{{ row.assigned_on }}</td>
                                <td class="panel-cell-muted">{{ row.returned_on || 'current' }}</td>
                            </template>
                        </PanelTable>
                    </div>
                </section>
            </div>

            <section v-if="asset.purchase_cost" class="panel-card">
                <h2 class="panel-card-title">Depreciation</h2>
                <dl class="client-dl">
                    <div>
                        <dt>Method</dt>
                        <dd>{{ methodLabels[asset.depreciation_method] || asset.depreciation_method }}</dd>
                    </div>
                    <div v-if="asset.depreciation_method === 'straight_line'">
                        <dt>Useful life</dt>
                        <dd>{{ asset.useful_life_months }} months</dd>
                    </div>
                    <div v-if="asset.depreciation_method === 'reducing_balance'">
                        <dt>Rate</dt>
                        <dd>{{ asset.depreciation_rate }}% / year</dd>
                    </div>
                    <div v-if="asset.in_service_on || asset.purchased_on">
                        <dt>In service from</dt>
                        <dd>{{ asset.in_service_on || asset.purchased_on }}</dd>
                    </div>
                    <div v-if="Number(asset.salvage_value)">
                        <dt>Salvage value</dt>
                        <dd>{{ depAmount(asset.salvage_value) }}</dd>
                    </div>
                </dl>

                <div class="doc-totals" style="margin-top: 0.5rem">
                    <div class="doc-totals-row"><span>Purchase cost</span><span>{{ depAmount(dep.cost) }}</span></div>
                    <div v-if="dep.applicable" class="doc-totals-row">
                        <span>Depreciated ({{ dep.months_elapsed }} mo)</span><span>− {{ depAmount(dep.accumulated) }}</span>
                    </div>
                    <div class="doc-totals-row is-grand">
                        <span>Book value today</span><span>{{ depAmount(dep.book_value) }}</span>
                    </div>
                </div>
                <p v-if="dep.fully_depreciated" class="panel-cell-muted" style="margin-top: 0.5rem">
                    Fully depreciated — carried at its salvage value.
                </p>
                <p v-else-if="asset.depreciation_method === 'none'" class="panel-cell-muted" style="margin-top: 0.5rem">
                    No depreciation is being applied. Set a method to track book value over time.
                </p>
            </section>

            <section class="panel-card">
                <h2 class="panel-card-title">Activity</h2>
                <ActivityFeed :items="activity" empty="No changes recorded yet." />
            </section>
        </div>
    </ConsoleLayout>
</template>
