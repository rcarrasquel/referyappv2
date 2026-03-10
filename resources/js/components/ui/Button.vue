<template>
    <button
        v-motion
        :while-hover="{ scale: 1.02 }"
        class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-[#6DBE45] focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
        :class="variantClasses"
    >
        <slot />
    </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
    },
});

const variantClasses = computed(() => {
    if (props.variant === 'secondary') {
        return 'bg-[#111111] text-white border border-[#111111] hover:bg-black';
    }

    if (props.variant === 'danger') {
        return 'bg-rose-600 text-white hover:bg-rose-500';
    }

    return 'bg-[#6DBE45] text-[#111111] hover:bg-[#8EDB63]';
});
</script>
