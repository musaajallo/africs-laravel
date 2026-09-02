<script setup>
import { ref } from 'vue';

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    suggestions: { type: Array, default: () => [] },
    placeholder: { type: String, default: 'Add a tag and press Enter' },
});

const emit = defineEmits(['update:modelValue']);

const draft = ref('');
const listId = `tags-${Math.random().toString(36).slice(2)}`;

function add(raw) {
    const value = raw.trim().replace(/,$/, '').trim();
    if (!value) return;
    const exists = props.modelValue.some(
        (t) => t.toLowerCase() === value.toLowerCase(),
    );
    if (!exists) emit('update:modelValue', [...props.modelValue, value]);
    draft.value = '';
}

function remove(index) {
    emit(
        'update:modelValue',
        props.modelValue.filter((_, i) => i !== index),
    );
}

function onKeydown(e) {
    if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        add(draft.value);
    } else if (e.key === 'Backspace' && draft.value === '' && props.modelValue.length) {
        remove(props.modelValue.length - 1);
    }
}
</script>

<template>
    <div class="tag-input">
        <span v-for="(tag, i) in modelValue" :key="tag" class="tag-input-chip">
            {{ tag }}
            <button type="button" aria-label="Remove tag" @click="remove(i)">&times;</button>
        </span>
        <input
            v-model="draft"
            type="text"
            class="tag-input-field"
            :list="listId"
            :placeholder="modelValue.length ? '' : placeholder"
            @keydown="onKeydown"
            @blur="add(draft)"
        />
        <datalist :id="listId">
            <option v-for="s in suggestions" :key="s" :value="s" />
        </datalist>
    </div>
</template>
