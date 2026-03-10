<template>
    <span
        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
        :class="toneClasses"
    >
        <slot />
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    tone: {
        type: String,
        default: 'neutral',
    },
});

const toneClasses = computed(() => {
    if (props.tone === 'positive') {
        return 'bg-emerald-100 text-emerald-700';
    }

    if (props.tone === 'negative') {
        return 'bg-rose-100 text-rose-700';
    }

    return 'bg-slate-100 text-slate-700';
});
</script>
