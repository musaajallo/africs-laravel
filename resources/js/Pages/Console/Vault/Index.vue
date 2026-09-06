<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import PanelActions from '@/Components/Panel/PanelActions.vue';
import PanelConfirm from '@/Components/Panel/PanelConfirm.vue';
import PanelTable from '@/Components/Panel/PanelTable.vue';
import PanelPagination from '@/Components/Panel/PanelPagination.vue';
import PanelField from '@/Components/Panel/PanelField.vue';
import Modal from '@/Components/Modal.vue';
import VaultUnlockDialog from '@/Components/Panel/VaultUnlockDialog.vue';
import { useAuth } from '@/Composables/useAuth.js';

const props = defineProps({
    entries: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    folders: { type: Array, default: () => [] },
    unlocked: { type: Boolean, default: false },
    unlockTtl: { type: Number, default: 300 },
    canManage: { type: Boolean, default: false },
    kdbxEnabled: { type: Boolean, default: false },
});

const { can } = useAuth();
const unlocked = ref(props.unlocked);
const showUnlock = ref(false);
let pendingExport = null;

const search = ref(props.filters.search ?? '');
const folder = ref(props.filters.folder ?? '');
let timer = null;

function apply() {
    router.get(
        route('console.vault.index'),
        { search: search.value || undefined, folder: folder.value || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}
watch(search, () => {
    clearTimeout(timer);
    timer = setTimeout(apply, 300);
});
watch(folder, apply);

function lockNow() {
    window.axios.post(route('console.vault.lock')).then(() => (unlocked.value = false));
}

function requireUnlock(fn) {
    if (unlocked.value) return fn();
    pendingExport = fn;
    showUnlock.value = true;
}
function onUnlocked() {
    unlocked.value = true;
    const fn = pendingExport;
    pendingExport = null;
    if (fn) fn();
}

function exportXml() {
    window.location.href = route('console.vault.export.xml');
}

// kdbx export dialog
const showKdbx = ref(false);
const kdbxPassword = ref('');
const kdbxError = ref('');
const kdbxBusy = ref(false);

function openKdbx() {
    requireUnlock(() => {
        kdbxPassword.value = '';
        kdbxError.value = '';
        showKdbx.value = true;
    });
}

async function submitKdbx() {
    kdbxBusy.value = true;
    kdbxError.value = '';
    try {
        const res = await window.axios.post(
            route('console.vault.export.kdbx'),
            { password: kdbxPassword.value },
            { responseType: 'blob' },
        );
        const url = URL.createObjectURL(res.data);
        const a = document.createElement('a');
        a.href = url;
        a.download = `africs-vault-${new Date().toISOString().slice(0, 10)}.kdbx`;
        a.click();
        URL.revokeObjectURL(url);
        showKdbx.value = false;
    } catch (e) {
        if (e.response?.data instanceof Blob) {
            try {
                kdbxError.value = JSON.parse(await e.response.data.text()).errors?.password?.[0] || 'Export failed.';
            } catch {
                kdbxError.value = 'Export failed.';
            }
        } else {
            kdbxError.value = 'Export failed.';
        }
    } finally {
        kdbxBusy.value = false;
    }
}

// folder management
const folderForm = useForm({ name: '' });
const editingFolder = ref(null);
const folderEdit = useForm({ name: '' });
function addFolder() {
    folderForm.post(route('console.vault.folders.store'), {
        preserveScroll: true,
        onSuccess: () => folderForm.reset(),
    });
}
function startFolderEdit(f) {
    editingFolder.value = f.id;
    folderEdit.name = f.name;
}
function saveFolder(f) {
    folderEdit.put(route('console.vault.folders.update', f.id), {
        preserveScroll: true,
        onSuccess: () => (editingFolder.value = null),
    });
}
function removeFolder(f) {
    router.delete(route('console.vault.folders.destroy', f.id), { preserveScroll: true });
}
function removeEntry(entry) {
    router.delete(route('console.vault.destroy', entry.id), { preserveScroll: true });
}

async function copy(text) {
    try {
        await navigator.clipboard.writeText(text);
    } catch (e) {
        /* clipboard unavailable */
    }
}
</script>

<template>
    <Head title="Secrets vault" />

    <ConsoleLayout>
        <template #title>Secrets vault</template>

        <div class="panel-page">
            <PanelPageHeader
                title="Secrets vault"
                subtitle="Logins and credentials. Values are encrypted; every reveal is logged."
            >
                <template #actions>
                    <span v-if="unlocked" class="vault-pill is-open">
                        Unlocked · <button type="button" class="panel-link" style="padding: 0" @click="lockNow">Lock now</button>
                    </span>
                    <span v-else class="vault-pill">
                        Locked · <button type="button" class="panel-link" style="padding: 0" @click="showUnlock = true">Unlock</button>
                    </span>

                    <PanelButton v-if="canManage" :href="route('console.vault.create')">Add entry</PanelButton>

                    <PanelActions v-if="canManage" label="Export">
                        <button type="button" class="panel-actions-item" @click="requireUnlock(exportXml)">
                            KeePass 2 XML (import)
                        </button>
                        <button
                            type="button"
                            class="panel-actions-item"
                            :disabled="!kdbxEnabled"
                            @click="openKdbx"
                        >
                            KeePass database (.kdbx){{ kdbxEnabled ? '' : ' — not configured' }}
                        </button>
                    </PanelActions>
                </template>
            </PanelPageHeader>

            <div class="client-detail-grid">
                <section class="panel-card" style="max-width: 20rem">
                    <div class="client-card-head">
                        <h2 class="panel-card-title">Folders</h2>
                    </div>
                    <ul class="vault-folder-list">
                        <li>
                            <button
                                type="button"
                                class="panel-link"
                                style="padding: 0"
                                :class="{ 'is-strong': !folder }"
                                @click="folder = ''"
                            >All entries</button>
                        </li>
                        <li v-for="f in folders" :key="f.id">
                            <template v-if="editingFolder === f.id">
                                <input v-model="folderEdit.name" type="text" class="field-input" style="max-width: 9rem" />
                                <button type="button" class="panel-link" @click="saveFolder(f)">Save</button>
                                <button type="button" class="panel-link" @click="editingFolder = null">Cancel</button>
                            </template>
                            <template v-else>
                                <button
                                    type="button"
                                    class="panel-link"
                                    style="padding: 0"
                                    :class="{ 'is-strong': String(folder) === String(f.id) }"
                                    @click="folder = f.id"
                                >{{ f.name }}</button>
                                <span class="panel-cell-muted">{{ f.entries_count }}</span>
                                <span v-if="canManage" class="vault-folder-actions">
                                    <button type="button" class="panel-link" @click="startFolderEdit(f)">Edit</button>
                                    <PanelConfirm
                                        title="Delete this folder?"
                                        message="Entries in it become unfiled."
                                        confirm-label="Delete"
                                        @confirm="removeFolder(f)"
                                    >
                                        <template #trigger>
                                            <button type="button" class="panel-link is-danger">Delete</button>
                                        </template>
                                    </PanelConfirm>
                                </span>
                            </template>
                        </li>
                    </ul>
                    <form v-if="canManage" class="vault-folder-add" @submit.prevent="addFolder">
                        <input v-model="folderForm.name" type="text" class="field-input" placeholder="New folder" />
                        <PanelButton type="submit" variant="secondary" :disabled="folderForm.processing">Add</PanelButton>
                    </form>
                </section>

                <section class="panel-card" style="flex: 1">
                    <input
                        v-model="search"
                        type="search"
                        class="field-input"
                        placeholder="Search title, username, URL"
                        style="margin-bottom: 1rem; max-width: 22rem"
                    />

                    <PanelTable
                        :columns="['Title', 'Username', 'URL', 'Folder', { label: 'Actions', align: 'right' }]"
                        :rows="entries.data"
                        empty="No entries."
                    >
                        <template #row="{ row }">
                            <td>
                                <Link :href="route('console.vault.show', row.id)" class="panel-link" style="padding: 0">
                                    {{ row.title }}
                                </Link>
                                <span v-if="row.has_otp" class="vault-tag">2FA</span>
                            </td>
                            <td>
                                <button
                                    v-if="row.username"
                                    type="button"
                                    class="panel-link"
                                    style="padding: 0"
                                    @click="copy(row.username)"
                                >{{ row.username }}</button>
                                <span v-else class="panel-cell-muted">—</span>
                            </td>
                            <td class="panel-cell-muted">
                                <a v-if="row.url" :href="row.url" target="_blank" rel="noopener" class="panel-link" style="padding: 0">
                                    {{ row.url.replace(/^https?:\/\//, '') }}
                                </a>
                                <span v-else>—</span>
                            </td>
                            <td class="panel-cell-muted">{{ row.folder || 'Unfiled' }}</td>
                            <td class="panel-row-actions">
                                <Link :href="route('console.vault.show', row.id)" class="panel-link">View</Link>
                                <Link v-if="canManage" :href="route('console.vault.edit', row.id)" class="panel-link">Edit</Link>
                                <PanelConfirm
                                    v-if="canManage"
                                    title="Delete this entry?"
                                    :message="`${row.title} will be removed. It can be restored from the trash.`"
                                    confirm-label="Delete"
                                    @confirm="removeEntry(row)"
                                >
                                    <template #trigger>
                                        <button type="button" class="panel-link is-danger">Delete</button>
                                    </template>
                                </PanelConfirm>
                            </td>
                        </template>
                    </PanelTable>

                    <PanelPagination
                        :links="entries.links"
                        :from="entries.from"
                        :to="entries.to"
                        :total="entries.total"
                    />
                </section>
            </div>
        </div>

        <VaultUnlockDialog v-model:show="showUnlock" @unlocked="onUnlocked" />

        <Modal :show="showKdbx" @close="showKdbx = false">
            <form class="panel-confirm-body" @submit.prevent="submitKdbx">
                <h2 class="panel-confirm-title">Export as .kdbx</h2>
                <p class="panel-confirm-message">
                    Choose a password for the KeePass file. You'll need it to open the download.
                </p>
                <PanelField label="File password" :error="kdbxError" hint="At least 8 characters.">
                    <input v-model="kdbxPassword" type="password" class="field-input" autocomplete="new-password" />
                </PanelField>
                <div class="panel-confirm-actions">
                    <PanelButton type="button" variant="secondary" @click="showKdbx = false">Cancel</PanelButton>
                    <PanelButton type="submit" :disabled="kdbxBusy || kdbxPassword.length < 8">Download</PanelButton>
                </div>
            </form>
        </Modal>
    </ConsoleLayout>
</template>
