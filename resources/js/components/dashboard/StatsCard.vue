<template>
    <div
        v-motion
        :while-hover="{ y: -2 }"
        class="stats-card relative z-10 overflow-hidden rounded-xl border border-[#264318] p-6 shadow-[0_16px_34px_rgba(9,14,8,0.36)] transition hover:shadow-[0_18px_40px_rgba(10,20,8,0.52)]"
        :style="{
            background: 'linear-gradient(160deg, #111111 0%, #162510 55%, #1f3315 100%)',
            color: '#ffffff'
        }"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium" :style="{ color: 'rgba(255, 255, 255, 0.7)' }">{{ label }}</p>
                <p class="mt-2 text-2xl font-semibold" :style="{ color: '#ffffff' }">{{ value }}</p>
            </div>

            <div class="shrink-0">
                <span
                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                    :class="badgeClasses"
                >
                    {{ trend }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: String,
        required: true,
    },
    trend: {
        type: String,
        required: true,
    },
    status: {
        type: String,
        default: 'neutral',
    },
    accent: {
        type: String,
        default: 'default',
    },
});

const badgeClasses = computed(() => {
    if (props.status === 'positive') {
        return 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30';
    }
    if (props.status === 'negative') {
        return 'bg-rose-500/20 text-rose-300 border border-rose-500/30';
    }
    return 'bg-slate-500/20 text-slate-300 border border-slate-500/30';
});
</script>
