<template>
    <Head title="Dashboard" />

    <div class="relative">
        <div class="relative z-10 mb-6 rounded-2xl bg-[linear-gradient(130deg,#111111_0%,#173010_58%,#6DBE45_115%)] p-5 text-white shadow-[0_22px_45px_rgba(8,12,8,0.42)]">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#c7f4b2]">Analytics Hub</p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-white">Dashboard</h1>
                    <p class="mt-1 text-sm text-white/75">Business overview with live indicators.</p>
                </div>
                <Badge tone="positive">Live Data</Badge>
            </div>
        </div>

        <div
            v-if="needsFirstCard"
            class="relative z-10 mb-6 rounded-xl border border-amber-300 bg-[linear-gradient(135deg,#5a3c00,#a06a00)] p-4 text-amber-100 shadow-lg"
        >
            <p class="text-sm font-semibold text-amber-50">
                You need to create your digital card to start sharing your professional profile.
            </p>
            <p class="mt-1 text-sm text-amber-100/90">
                Create your first card now from the cards module.
            </p>
            <Link
                href="/cards"
                class="mt-3 inline-flex items-center rounded-lg bg-[#6DBE45] px-3 py-2 text-sm font-semibold text-[#111111] transition hover:bg-[#8EDB63]"
            >
                Go to Cards
            </Link>
        </div>

        <div ref="cardsGridRef" class="relative z-10 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatsCard
                v-for="card in stats"
                :key="card.label"
                :label="card.label"
                :value="card.value"
                :trend="card.trend"
                :status="card.status"
                :accent="card.accent || 'default'"
            />
        </div>

        <div class="relative z-10 mt-6 grid gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-[#264318] bg-[linear-gradient(160deg,#111111_0%,#162510_55%,#1f3315_100%)] p-4 text-white shadow-[0_16px_34px_rgba(9,14,8,0.36)]">
                <h2 class="text-sm font-semibold text-white">Top Cards (Last 30 Days)</h2>
                <div v-if="analytics.topCards.length" class="mt-3 space-y-2">
                    <div
                        v-for="card in analytics.topCards"
                        :key="card.id"
                        class="grid grid-cols-[1fr,auto,auto,auto] items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm"
                    >
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-white">{{ card.name || 'Untitled card' }}</p>
                            <p class="truncate text-xs text-white/65">@{{ card.username }}</p>
                        </div>
                        <p class="text-xs font-medium text-white/80">{{ card.visits }} views</p>
                        <p class="text-xs font-medium text-white/80">{{ card.clicks }} clicks</p>
                        <p class="rounded-md bg-[#6DBE45] px-2 py-1 text-xs font-semibold text-[#111111]">{{ card.ctr }}%</p>
                    </div>
                </div>
                <p v-else class="mt-3 text-sm text-white/70">No analytics data yet.</p>
            </div>

            <div class="rounded-xl border border-[#264318] bg-[linear-gradient(160deg,#111111_0%,#162510_55%,#1f3315_100%)] p-4 text-white shadow-[0_16px_34px_rgba(9,14,8,0.36)]">
                <h2 class="text-sm font-semibold text-white">Traffic Breakdown (Last 30 Days)</h2>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-white/10 bg-white/5 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-white/60">Browsers</p>
                        <div v-if="analytics.browsers.length" class="mt-2 space-y-1.5 text-sm">
                            <div
                                v-for="row in analytics.browsers"
                                :key="`browser-${row.label}`"
                                class="flex items-center justify-between text-white/85"
                            >
                                <span class="truncate">{{ row.label }}</span>
                                <span class="font-semibold">{{ row.total }}</span>
                            </div>
                        </div>
                        <p v-else class="mt-2 text-sm text-white/70">No browser data yet.</p>
                    </div>

                    <div class="rounded-lg border border-white/10 bg-white/5 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-white/60">Devices</p>
                        <div v-if="analytics.devices.length" class="mt-2 space-y-1.5 text-sm">
                            <div
                                v-for="row in analytics.devices"
                                :key="`device-${row.label}`"
                                class="flex items-center justify-between text-white/85"
                            >
                                <span class="truncate capitalize">{{ row.label }}</span>
                                <span class="font-semibold">{{ row.total }}</span>
                            </div>
                        </div>
                        <p v-else class="mt-2 text-sm text-white/70">No device data yet.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import StatsCard from '@/components/dashboard/StatsCard.vue';
import Badge from '@/components/ui/Badge.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

// No definir layout aquí, se asigna automáticamente desde app.js

defineProps({
    stats: {
        type: Array,
        default: () => [],
    },
    needsFirstCard: {
        type: Boolean,
        default: false,
    },
    cardsCount: {
        type: Number,
        default: 0,
    },
    analytics: {
        type: Object,
        default: () => ({
            totals: { visits: 0, clicks: 0 },
            topCards: [],
            browsers: [],
            devices: [],
        }),
    },
});

const cardsGridRef = ref(null);
</script>
