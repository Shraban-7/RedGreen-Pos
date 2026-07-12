<script setup>
import { ref, watch } from 'vue';
import DatePicker from 'primevue/datepicker';

const props = defineProps({
    from: { type: String, default: '' },
    to: { type: String, default: '' },
});

const emit = defineEmits(['update:from', 'update:to', 'change']);

const range = ref([props.from ? new Date(props.from) : null, props.to ? new Date(props.to) : null]);

watch(range, (val) => {
    const from = val?.[0] ? val[0].toISOString().split('T')[0] : '';
    const to = val?.[1] ? val[1].toISOString().split('T')[0] : '';
    emit('update:from', from);
    emit('update:to', to);
    emit('change', { from, to });
});
</script>

<template>
    <DatePicker
        v-model="range"
        selectionMode="range"
        :manualInput="false"
        dateFormat="yy-mm-dd"
        placeholder="Select date range"
        class="w-full md:w-72"
    />
</template>