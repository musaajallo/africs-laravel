<script setup>
defineProps({
    // column headers: string[] or [{ label, align }]
    columns: { type: Array, default: () => [] },
    rows: { type: Array, default: () => [] },
    rowKey: { type: String, default: 'id' },
    empty: { type: String, default: 'Nothing to show yet.' },
});

function head(col) {
    return typeof col === 'string' ? { label: col } : col;
}
</script>

<template>
    <div class="panel-table-wrap">
        <table class="panel-table">
            <thead>
                <tr>
                    <th
                        v-for="(col, i) in columns"
                        :key="i"
                        :style="head(col).align ? { textAlign: head(col).align } : null"
                    >
                        {{ head(col).label }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="!rows.length">
                    <td :colspan="columns.length" class="panel-table-empty">
                        {{ empty }}
                    </td>
                </tr>
                <tr v-for="row in rows" :key="row[rowKey]">
                    <slot name="row" :row="row" />
                </tr>
            </tbody>
        </table>
    </div>
</template>
