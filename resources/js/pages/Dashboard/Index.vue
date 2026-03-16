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

        <div v-if="isAdmin && adminBasics" class="relative z-10 mb-6 grid gap-4 lg:grid-cols-12">
            <div class="rounded-2xl border border-[#264318] bg-[linear-gradient(160deg,#111111_0%,#162510_55%,#1f3315_100%)] p-4 text-white shadow-[0_16px_34px_rgba(9,14,8,0.36)] lg:col-span-7">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold">Admin Overview</h2>
                    <span class="rounded-md bg-[#6DBE45] px-2 py-1 text-xs font-semibold text-[#111111]">System</span>
                </div>
                <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-lg border border-white/10 bg-white/5 p-3">
                        <p class="text-xs text-white/65">Users</p>
                        <p class="mt-1 text-xl font-semibold">{{ adminBasics.total_users }}</p>
                    </div>
                    <div class="rounded-lg border border-white/10 bg-white/5 p-3">
                        <p class="text-xs text-white/65">Business</p>
                        <p class="mt-1 text-xl font-semibold">{{ adminBasics.total_business_users }}</p>
                    </div>
                    <div class="rounded-lg border border-white/10 bg-white/5 p-3">
                        <p class="text-xs text-white/65">Admins</p>
                        <p class="mt-1 text-xl font-semibold">{{ adminBasics.total_admin_users }}</p>
                    </div>
                    <div class="rounded-lg border border-white/10 bg-white/5 p-3">
                        <p class="text-xs text-white/65">Cards</p>
                        <p class="mt-1 text-xl font-semibold">{{ adminBasics.total_cards }}</p>
                    </div>
                    <div class="rounded-lg border border-white/10 bg-white/5 p-3">
                        <p class="text-xs text-white/65">Products</p>
                        <p class="mt-1 text-xl font-semibold">{{ adminBasics.total_products }}</p>
                    </div>
                    <div class="rounded-lg border border-white/10 bg-white/5 p-3">
                        <p class="text-xs text-white/65">Appointments / Leads</p>
                        <p class="mt-1 text-xl font-semibold">{{ adminBasics.total_appointments }} / {{ adminBasics.total_leads }}</p>
                    </div>
                </div>
                <div class="mt-3 grid gap-2 sm:grid-cols-3">
                    <div class="rounded-lg border border-emerald-300/20 bg-emerald-500/15 p-3">
                        <p class="text-xs text-emerald-100/80">Total Revenue</p>
                        <p class="mt-1 text-xl font-semibold text-white">{{ formatMoney(adminBasics.revenue_total_cents) }}</p>
                    </div>
                    <div class="rounded-lg border border-cyan-300/20 bg-cyan-500/15 p-3">
                        <p class="text-xs text-cyan-100/80">Current Month</p>
                        <p class="mt-1 text-xl font-semibold text-white">{{ formatMoney(adminBasics.revenue_month_cents) }}</p>
                    </div>
                    <div class="rounded-lg border border-amber-300/20 bg-amber-500/15 p-3">
                        <p class="text-xs text-amber-100/80">Month vs Previous</p>
                        <p class="mt-1 text-xl font-semibold text-white">
                            {{ adminBasics.revenue_month_delta_percent > 0 ? '+' : '' }}{{ adminBasics.revenue_month_delta_percent }}%
                        </p>
                    </div>
                </div>
                <div class="mt-3 rounded-lg border border-white/10 bg-white/5 p-3">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wide text-white/70">Monthly Revenue</p>
                        <p class="text-xs text-white/60">Paying businesses: {{ adminBasics.paid_business_users }}</p>
                    </div>
                    <div class="grid grid-cols-6 gap-2">
                        <div
                            v-for="item in adminBasics.monthly_revenue_series"
                            :key="item.key"
                            class="flex flex-col items-center justify-end gap-1"
                        >
                            <div class="flex h-24 w-full items-end rounded-md bg-white/5 p-1">
                                <div
                                    class="w-full rounded-sm bg-[#6DBE45]"
                                    :style="{ height: `${monthlyBarHeight(item.total_cents, adminBasics.monthly_revenue_series)}%` }"
                                />
                            </div>
                            <p class="text-[10px] text-white/70">{{ item.label }}</p>
                            <p class="text-[10px] font-semibold text-white">{{ compactMoney(item.total_cents) }}</p>
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <Link href="/users" class="rounded-lg bg-[#6DBE45] px-3 py-2 text-xs font-semibold text-[#111111] transition hover:bg-[#8EDB63]">Users</Link>
                    <Link href="/analytics" class="rounded-lg border border-white/20 bg-white/10 px-3 py-2 text-xs font-semibold text-white transition hover:bg-white/15">Comparative Analytics</Link>
                </div>
            </div>

            <div class="rounded-2xl border border-[#264318] bg-[linear-gradient(160deg,#111111_0%,#162510_55%,#1f3315_100%)] p-4 text-white shadow-[0_16px_34px_rgba(9,14,8,0.36)] lg:col-span-5">
                <h2 class="text-sm font-semibold">Latest Users</h2>
                <div v-if="adminBasics.recent_users?.length" class="mt-3 space-y-2">
                    <div
                        v-for="item in adminBasics.recent_users"
                        :key="item.id"
                        class="rounded-lg border border-white/10 bg-white/5 px-3 py-2"
                    >
                        <p class="truncate text-sm font-semibold text-white">{{ item.name }}</p>
                        <p class="truncate text-xs text-white/70">{{ item.email }}</p>
                        <p class="mt-1 text-[11px] text-white/60">{{ item.role }} · {{ item.plan }} · {{ formatDate(item.created_at) }}</p>
                    </div>
                </div>
                <p v-else class="mt-3 text-sm text-white/70">No users found.</p>
            </div>
        </div>

        <div
            v-if="needsFirstCard"
            class="relative z-10 mb-6 overflow-hidden rounded-2xl border border-[#6DBE45]/35 bg-[linear-gradient(125deg,#0f160d_0%,#152311_45%,#1e3316_72%,#6DBE45_145%)] p-5 text-white shadow-[0_18px_42px_rgba(11,18,9,0.45)]"
        >
            <div class="pointer-events-none absolute -right-14 -top-14 h-44 w-44 rounded-full bg-[#9be476]/18 blur-2xl" />
            <div class="pointer-events-none absolute -bottom-16 -left-14 h-40 w-40 rounded-full bg-white/8 blur-2xl" />

            <div class="relative">
                <div class="inline-flex items-center rounded-full border border-white/25 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-[#c7f4b2]">
                    New account setup
                </div>
                <p class="mt-3 text-sm font-semibold text-white">
                    You need to create your digital card to start sharing your professional profile.
                </p>
                <p class="mt-1 text-sm text-white/80">
                Create your first card now from the cards module.
                </p>
                <Link
                    href="/cards"
                    class="mt-4 inline-flex items-center rounded-lg border border-[#6DBE45]/40 bg-[#6DBE45] px-3.5 py-2 text-sm font-semibold text-[#111111] transition hover:bg-[#87d85e]"
                >
                    Go to Cards
                </Link>
            </div>
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
    isAdmin: {
        type: Boolean,
        default: false,
    },
    adminBasics: {
        type: Object,
        default: null,
    },
});

const cardsGridRef = ref(null);

const formatDate = (value) => {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleDateString();
};

const formatMoney = (amountCents, currency = 'usd') => {
    const amount = Number(amountCents || 0) / 100;
    return new Intl.NumberFormat(undefined, { style: 'currency', currency: currency.toUpperCase() }).format(amount);
};

const compactMoney = (amountCents, currency = 'usd') => {
    const amount = Number(amountCents || 0) / 100;
    return new Intl.NumberFormat(undefined, {
        style: 'currency',
        currency: currency.toUpperCase(),
        notation: 'compact',
        maximumFractionDigits: 1,
    }).format(amount);
};

const monthlyBarHeight = (value, series) => {
    const max = Math.max(...(series || []).map((item) => Number(item.total_cents || 0)), 1);
    const pct = (Number(value || 0) / max) * 100;
    return Math.max(8, Math.min(100, pct));
};
</script>
