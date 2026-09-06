<script setup>
import { computed, onUnmounted, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ConsoleLayout from '@/Layouts/ConsoleLayout.vue';
import PanelPageHeader from '@/Components/Panel/PanelPageHeader.vue';
import PanelButton from '@/Components/Panel/PanelButton.vue';
import PanelActions from '@/Components/Panel/PanelActions.vue';
import PanelConfirm from '@/Components/Panel/PanelConfirm.vue';
import ActivityFeed from '@/Components/Panel/ActivityFeed.vue';
import VaultUnlockDialog from '@/Components/Panel/VaultUnlockDialog.vue';
import { useAuth } from '@/Composables/useAuth.js';

const props = defineProps({
    entry: { type: Object, required: true },
    unlocked: { type: Boolean, default: false },
    unlockTtl: { type: Number, default: 300 },
    canManage: { type: Boolean, default: false },
    activity: { type: Array, default: () => [] },
});

const { can } = useAuth();
const unlocked = ref(props.unlocked);
const showUnlock = ref(false);
const revealed = ref(null); // { password, notes, totp_secret, custom: [...] }
const confirmDelete = ref(false);
let relockTimer = null;

function relock() {
    revealed.value = null;
    unlocked.value = false;
}

async function fetchSecrets() {
    const { data } = await window.axios.get(route('console.vault.reveal', props.entry.id));
    revealed.value = data;
    clearTimeout(relockTimer);
    relockTimer = setTimeout(relock, Math.max(30, props.unlockTtl) * 1000);
}

function reveal() {
    if (!unlocked.value) {
        showUnlock.value = true;
        return;
    }
    fetchSecrets();
}
function onUnlocked() {
    unlocked.value = true;
    fetchSecrets();
}

onUnmounted(() => clearTimeout(relockTimer));

async function copy(text) {
    if (text == null) return;
    try {
        await navigator.clipboard.writeText(text);
    } catch (e) {
        /* clipboard unavailable */
    }
}

async function copyPassword() {
    if (revealed.value?.password != null) return copy(revealed.value.password);
    if (!unlocked.value) {
        showUnlock.value = true;
        return;
    }
    await fetchSecrets();
    copy(revealed.value?.password);
}

const secretCustom = computed(() => props.entry.custom.filter((f) => f.secret));
const plainCustom = computed(() => props.entry.custom.filter((f) => !f.secret));

function revealedCustom(label) {
    return revealed.value?.custom?.find((f) => f.label === label)?.value;
}

function del() {
    router.delete(route('console.vault.destroy', props.entry.id));
}
function restore() {
    router.put(route('console.vault.restore', props.entry.id));
}
</script>

<template>
    <Head :title="entry.title" />

    <ConsoleLayout>
        <template #title>Secrets vault</template>

        <div class="panel-page">
            <PanelPageHeader :title="entry.title" :subtitle="entry.archived ? 'Deleted entry' : entry.folder || 'Unfiled'">
                <template #actions>
                    <PanelButton variant="secondary" :href="route('console.vault.index')">All entries</PanelButton>
                    <PanelButton v-if="canManage && !entry.archived" :href="route('console.vault.edit', entry.id)">Edit</PanelButton>
                    <PanelButton v-if="canManage && entry.archived" @click="restore">Restore</PanelButton>
                    <PanelActions v-if="canManage && !entry.archived">
                        <button type="button" class="panel-actions-item is-danger" @click="confirmDelete = true">Delete</button>
                    </PanelActions>
                </template>
            </PanelPageHeader>

            <PanelConfirm
                v-model="confirmDelete"
                title="Delete this entry?"
                :message="`${entry.title} will be removed. It can be restored from the trash.`"
                confirm-label="Delete"
                @confirm="del"
            />

            <div class="client-detail-grid">
                <section class="panel-card">
                    <h2 class="panel-card-title">Credential</h2>
                    <dl class="client-dl vault-dl">
                        <div v-if="entry.username">
                            <dt>Username</dt>
                            <dd>
                                {{ entry.username }}
                                <button type="button" class="vault-copy" @click="copy(entry.username)">copy</button>
                            </dd>
                        </div>
                        <div v-if="entry.has_password">
                            <dt>Password</dt>
                            <dd>
                                <code v-if="revealed?.password != null" class="vault-secret">{{ revealed.password }}</code>
                                <span v-else class="vault-mask">••••••••••</span>
                                <button type="button" class="vault-copy" @click="revealed?.password != null ? relock() : reveal()">
                                    {{ revealed?.password != null ? 'hide' : 'reveal' }}
                                </button>
                                <button type="button" class="vault-copy" @click="copyPassword">copy</button>
                            </dd>
                        </div>
                        <div v-if="entry.url">
                            <dt>URL</dt>
                            <dd><a :href="entry.url" target="_blank" rel="noopener" class="panel-link" style="padding: 0">{{ entry.url }}</a></dd>
                        </div>
                        <div v-if="entry.has_otp">
                            <dt>2FA seed</dt>
                            <dd>
                                <code v-if="revealed?.totp_secret != null" class="vault-secret">{{ revealed.totp_secret }}</code>
                                <span v-else class="vault-mask">••••••</span>
                                <button type="button" class="vault-copy" @click="revealed?.totp_secret != null ? relock() : reveal()">
                                    {{ revealed?.totp_secret != null ? 'hide' : 'reveal' }}
                                </button>
                            </dd>
                        </div>
                        <div v-for="f in plainCustom" :key="f.label">
                            <dt>{{ f.label }}</dt>
                            <dd>{{ f.value }} <button type="button" class="vault-copy" @click="copy(f.value)">copy</button></dd>
                        </div>
                        <div v-for="f in secretCustom" :key="f.label">
                            <dt>{{ f.label }}</dt>
                            <dd>
                                <code v-if="revealedCustom(f.label) != null" class="vault-secret">{{ revealedCustom(f.label) }}</code>
                                <span v-else class="vault-mask">••••••</span>
                                <button type="button" class="vault-copy" @click="revealed ? relock() : reveal()">
                                    {{ revealedCustom(f.label) != null ? 'hide' : 'reveal' }}
                                </button>
                            </dd>
                        </div>
                    </dl>

                    <div v-if="entry.has_notes" style="margin-top: 1rem">
                        <dt style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--pnl-muted)">Notes</dt>
                        <dd v-if="revealed?.notes != null" style="white-space: pre-line; margin: 0.35rem 0 0">{{ revealed.notes }}</dd>
                        <button v-else type="button" class="vault-copy" style="margin-top: 0.35rem" @click="reveal">reveal notes</button>
                    </div>

                    <p class="panel-cell-muted" style="margin-top: 1rem; font-size: 0.8rem">
                        Added by {{ entry.created_by || 'system' }} · updated {{ entry.updated_at }}
                    </p>
                </section>

                <section class="panel-card">
                    <h2 class="panel-card-title">Access log</h2>
                    <ActivityFeed :items="activity" empty="No reveals or changes yet." />
                </section>
            </div>
        </div>

        <VaultUnlockDialog v-model:show="showUnlock" @unlocked="onUnlocked" />
    </ConsoleLayout>
</template>
