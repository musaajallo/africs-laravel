<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import PanelConfirm from '@/Components/Panel/PanelConfirm.vue';
import PanelTable from '@/Components/Panel/PanelTable.vue';
import TagBadge from '@/Components/Panel/TagBadge.vue';
import { useAuth } from '@/Composables/useAuth.js';

const props = defineProps({
    tags: { type: Array, default: () => [] },
    colors: { type: Array, default: () => [] },
});

const { can } = useAuth();
const canManage = can('tags.manage');

const createForm = useForm({ name: '', color: props.colors[0] ?? 'gray' });
const editingId = ref(null);
const editForm = useForm({ name: '', color: 'gray' });

function create() {
    createForm.post(route('console.tags.store'), {
        preserveScroll: true,
        onSuccess: () => createForm.reset('name'),
    });
}

function startEdit(tag) {
    editingId.value = tag.id;
    editForm.name = tag.name;
    editForm.color = tag.color;
    editForm.clearErrors();
}

function saveEdit(tag) {
    editForm.put(route('console.tags.update', tag.id), {
        preserveScroll: true,
        onSuccess: () => (editingId.value = null),
    });
}

function remove(tag) {
    router.delete(route('console.tags.destroy', tag.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Tags" />

    <ConsoleLayout>
        <template #title>Tags</template>

        <div class="panel-page">
            <PanelPageHeader
                title="Tags"
                subtitle="Labels you can attach to clients (and, later, other records)."
            />

            <form v-if="canManage" class="panel-card tag-create" @submit.prevent="create">
                <div class="tag-create-row">
                    <div class="panel-field" style="flex: 1">
                        <label class="panel-field-label">New tag</label>
                        <input v-model="createForm.name" type="text" class="field-input" placeholder="e.g. Retainer, VIP, Overdue" />
                        <p v-if="createForm.errors.name" class="panel-field-error">{{ createForm.errors.name }}</p>
                    </div>
                    <div class="panel-field">
                        <label class="panel-field-label">Colour</label>
                        <div class="tag-swatch-row">
                            <button
                                v-for="c in colors"
                                :key="c"
                                type="button"
                                class="tag-swatch"
                                :class="{ 'is-selected': createForm.color === c }"
                                :data-color="c"
                                :aria-label="c"
                                @click="createForm.color = c"
                            ></button>
                        </div>
                    </div>
                    <PanelButton type="submit" :disabled="createForm.processing">Add tag</PanelButton>
                </div>
            </form>

            <PanelTable
                :columns="['Tag', 'Colour', { label: 'Used by', align: 'right' }, { label: 'Actions', align: 'right' }]"
                :rows="tags"
                empty="No tags yet."
            >
                <template #row="{ row }">
                    <template v-if="editingId === row.id">
                        <td><input v-model="editForm.name" type="text" class="field-input" style="max-width: 16rem" /></td>
                        <td>
                            <div class="tag-swatch-row">
                                <button
                                    v-for="c in colors"
                                    :key="c"
                                    type="button"
                                    class="tag-swatch"
                                    :class="{ 'is-selected': editForm.color === c }"
                                    :data-color="c"
                                    @click="editForm.color = c"
                                ></button>
                            </div>
                        </td>
                        <td style="text-align: right">{{ row.usage_count }}</td>
                        <td class="panel-row-actions">
                            <button type="button" class="panel-link" @click="saveEdit(row)">Save</button>
                            <button type="button" class="panel-link" @click="editingId = null">Cancel</button>
                        </td>
                    </template>
                    <template v-else>
                        <td><TagBadge :name="row.name" :color="row.color" /></td>
                        <td class="panel-cell-muted">{{ row.color }}</td>
                        <td style="text-align: right">{{ row.usage_count }}</td>
                        <td class="panel-row-actions">
                            <button v-if="canManage" type="button" class="panel-link" @click="startEdit(row)">Edit</button>
                            <PanelConfirm
                                v-if="canManage"
                                :title="`Delete “${row.name}”?`"
                                :message="row.usage_count ? `This tag is on ${row.usage_count} record(s); it will be removed from all of them.` : 'This tag will be deleted.'"
                                confirm-label="Delete"
                                @confirm="remove(row)"
                            >
                                <template #trigger>
                                    <button type="button" class="panel-link is-danger">Delete</button>
                                </template>
                            </PanelConfirm>
                        </td>
                    </template>
                </template>
            </PanelTable>
        </div>
    </ConsoleLayout>
</template>
