<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    items: { type: Array, default: () => [] },
    // show the subject (client name) on each row — off on a client's own page
    showSubject: { type: Boolean, default: false },
    empty: { type: String, default: 'Nothing recorded yet.' },
});
</script>

<template>
    <p v-if="!items.length" class="panel-cell-muted">{{ empty }}</p>
    <ol v-else class="activity-feed">
        <li v-for="item in items" :key="item.id">
            <span class="activity-dot" :data-event="item.event || 'default'"></span>
            <div class="activity-body">
                <p class="activity-line">
                    <span class="activity-desc">{{ item.description }}</span>
                    <template v-if="showSubject && item.subject_label">
                        &middot;
                        <Link v-if="item.subject_url" :href="item.subject_url" class="panel-link" style="padding: 0">
                            {{ item.subject_label }}
                        </Link>
                        <span v-else>{{ item.subject_label }}</span>
                    </template>
                </p>
                <p class="activity-meta">
                    {{ item.causer }} &middot;
                    <time :datetime="item.at" :title="item.at">{{ item.at_human }}</time>
                </p>
                <ul v-if="item.changes.length" class="activity-changes">
                    <li v-for="(c, i) in item.changes" :key="i">
                        <strong>{{ c.field }}:</strong> {{ c.from }} &rarr; {{ c.to }}
                    </li>
                </ul>
            </div>
        </li>
    </ol>
</template>
