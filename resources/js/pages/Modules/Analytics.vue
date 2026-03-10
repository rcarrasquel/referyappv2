<template>
    <Head title="Analytics" />

    <div class="relative space-y-6">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_right,_rgba(109,190,69,0.2),_transparent_46%),radial-gradient(circle_at_bottom_left,_rgba(17,17,17,0.12),_transparent_40%)]" />

        <section class="rounded-2xl bg-[linear-gradient(130deg,#111111_0%,#1a3111_60%,#6DBE45_120%)] p-5 text-white shadow-[0_24px_48px_rgba(8,12,8,0.4)]">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#c8f5b3]">Analytics Center</p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight">Performance Analytics</h1>
                    <p class="mt-1 text-sm text-white/75">
                        Compare ranges, filter by card/device/browser, and analyze detailed traffic and clicks.
                    </p>
                </div>
                <Badge tone="positive">Live Tracking</Badge>
            </div>
        </section>

        <Card class="border border-slate-200/80 bg-white/95">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-base font-semibold text-slate-900">Filters & Comparison</h2>
                <Button variant="secondary" type="button" @click="resetFilters">
                    <span class="inline-flex items-center gap-2">
                        <ArrowPathIcon class="h-4 w-4" />
                        Reset
                    </span>
                </Button>
            </div>

            <form class="grid gap-3 md:grid-cols-3 xl:grid-cols-6" @submit.prevent="applyFilters">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Start date</label>
                    <input v-model="filterState.start_date" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">End date</label>
                    <input v-model="filterState.end_date" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Compare</label>
                    <select v-model="filterState.compare" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="none">No comparison</option>
                        <option value="previous_period">Previous period</option>
                        <option value="previous_year">Same period last year</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Card</label>
                    <select v-model="filterState.card_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="">All cards</option>
                        <option v-for="card in filterOptions.cards" :key="card.id" :value="card.id">
                            {{ card.name || 'Untitled' }} (@{{ card.username }})
                        </option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Device</label>
                    <select v-model="filterState.device" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="">All devices</option>
                        <option v-for="device in filterOptions.devices" :key="device" :value="device">{{ device }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Browser</label>
                    <select v-model="filterState.browser" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="">All browsers</option>
                        <option v-for="browser in filterOptions.browsers" :key="browser" :value="browser">{{ browser }}</option>
                    </select>
                </div>

                <div class="md:col-span-3 xl:col-span-6">
                    <Button type="submit">
                        <span class="inline-flex items-center gap-2">
                            <FunnelIcon class="h-4 w-4" />
                            Apply filters
                        </span>
                    </Button>
                </div>
            </form>
        </Card>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatsCard label="Views" :value="number(summary.views)" :trend="deltaLabel(comparisons.views_delta)" :status="deltaTone(comparisons.views_delta)" />
            <StatsCard label="Unique Visitors" :value="number(summary.unique_visitors)" trend="Distinct users" status="positive" />
            <StatsCard label="Link Clicks" :value="number(summary.clicks)" :trend="deltaLabel(comparisons.clicks_delta)" :status="deltaTone(comparisons.clicks_delta)" />
            <StatsCard label="CTR" :value="`${summary.ctr}%`" :trend="deltaLabel(comparisons.ctr_delta)" :status="deltaTone(comparisons.ctr_delta)" />
        </div>

        <Card class="border border-slate-200/80 bg-white/95">
            <div class="mb-3 flex items-center justify-between gap-3">
                <h2 class="text-base font-semibold text-slate-900">Views vs Clicks (Daily)</h2>
                <span class="text-xs text-slate-500">{{ ranges.current.start }} - {{ ranges.current.end }}</span>
            </div>

            <div class="rounded-xl border border-slate-100 bg-[linear-gradient(180deg,#f8fafc,#eef3f8)] p-3">
                <div class="mb-3 flex flex-wrap items-center gap-3 text-xs font-medium">
                    <span class="inline-flex items-center gap-1 text-[#6DBE45]"><span class="h-2 w-2 rounded-full bg-[#6DBE45]" />Views</span>
                    <span class="inline-flex items-center gap-1 text-[#111111]"><span class="h-2 w-2 rounded-full bg-[#111111]" />Clicks</span>
                    <span v-if="showCompareSeries" class="inline-flex items-center gap-1 text-slate-500"><span class="h-2 w-2 rounded-full border border-dashed border-slate-500" />Previous range</span>
                </div>

                <svg :viewBox="`0 0 ${chartWidth} ${chartHeight}`" class="h-[300px] w-full">
                    <defs>
                        <linearGradient id="viewsFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#6DBE45" stop-opacity="0.32" />
                            <stop offset="100%" stop-color="#6DBE45" stop-opacity="0.03" />
                        </linearGradient>
                    </defs>

                    <g v-for="(tick, idx) in yTicks" :key="`tick-${idx}`">
                        <line :x1="chartPadding.left" :x2="chartWidth - chartPadding.right" :y1="tick.y" :y2="tick.y" stroke="#d8e0ea" stroke-width="1" />
                        <text :x="chartPadding.left - 8" :y="tick.y + 4" text-anchor="end" font-size="10" fill="#64748b">{{ tick.value }}</text>
                    </g>

                    <path :d="viewsAreaPath" fill="url(#viewsFill)" />
                    <polyline :points="viewsLinePoints" fill="none" stroke="#6DBE45" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    <polyline :points="clicksLinePoints" fill="none" stroke="#111111" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />

                    <polyline
                        v-if="showCompareSeries"
                        :points="previousViewsLinePoints"
                        fill="none"
                        stroke="#6DBE45"
                        stroke-width="1.4"
                        stroke-dasharray="4 4"
                        opacity="0.75"
                    />
                    <polyline
                        v-if="showCompareSeries"
                        :points="previousClicksLinePoints"
                        fill="none"
                        stroke="#111111"
                        stroke-width="1.4"
                        stroke-dasharray="4 4"
                        opacity="0.75"
                    />
                </svg>

                <div class="mt-1 grid grid-cols-2 gap-2 text-xs text-slate-500 md:grid-cols-6">
                    <p v-for="(label, idx) in xTickLabels" :key="`x-${idx}`" class="truncate">{{ label }}</p>
                </div>
            </div>
        </Card>

        <div class="grid gap-4 xl:grid-cols-2">
            <Card class="border border-slate-200/80 bg-white/95">
                <h2 class="text-base font-semibold text-slate-900">Top Cards</h2>
                <div v-if="topCards.length" class="mt-3 space-y-2">
                    <div v-for="card in topCards" :key="card.card_id" class="grid grid-cols-[1fr,auto,auto,auto] items-center gap-2 rounded-lg border border-slate-100 px-3 py-2">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ card.name || 'Untitled card' }}</p>
                            <p class="truncate text-xs text-slate-500">@{{ card.username }}</p>
                        </div>
                        <p class="text-xs text-slate-600">{{ card.views }} views</p>
                        <p class="text-xs text-slate-600">{{ card.clicks }} clicks</p>
                        <p class="rounded bg-[#6DBE45]/15 px-2 py-1 text-xs font-semibold text-[#111111]">{{ card.ctr }}%</p>
                    </div>
                </div>
                <p v-else class="mt-3 text-sm text-slate-500">No data available with current filters.</p>
            </Card>

            <Card class="border border-slate-200/80 bg-white/95">
                <h2 class="text-base font-semibold text-slate-900">Top Links</h2>
                <div v-if="topLinks.length" class="mt-3 space-y-2">
                    <div v-for="(link, idx) in topLinks" :key="`${link.url}-${idx}`" class="rounded-lg border border-slate-100 px-3 py-2">
                        <div class="mb-1 flex items-center justify-between gap-2">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ link.name }}</p>
                            <p class="text-xs font-semibold text-[#111111]">{{ link.clicks }}</p>
                        </div>
                        <p class="truncate text-xs text-slate-500">{{ link.url || '-' }}</p>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-[linear-gradient(90deg,#111111,#6DBE45)]" :style="{ width: `${barWidth(link.clicks, maxTopLinkClicks)}%` }" />
                        </div>
                    </div>
                </div>
                <p v-else class="mt-3 text-sm text-slate-500">No link click data yet.</p>
            </Card>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            <Card class="border border-slate-200/80 bg-white/95">
                <h2 class="text-base font-semibold text-slate-900">Browser Breakdown</h2>
                <div class="mt-3 space-y-2">
                    <div v-for="row in breakdowns.browsers" :key="`b-${row.label}`" class="rounded-lg border border-slate-100 px-3 py-2 text-sm">
                        <div class="mb-1 flex items-center justify-between">
                            <span class="truncate text-slate-700">{{ row.label }}</span>
                            <span class="font-semibold text-slate-900">{{ row.total }}</span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-[#111111]" :style="{ width: `${barWidth(row.total, maxBrowserTotal)}%` }" />
                        </div>
                    </div>
                    <p v-if="!breakdowns.browsers.length" class="text-sm text-slate-500">No browser data.</p>
                </div>
            </Card>

            <Card class="border border-slate-200/80 bg-white/95">
                <h2 class="text-base font-semibold text-slate-900">Device / OS / Referrer</h2>
                <div class="mt-3 grid gap-4 md:grid-cols-3">
                    <div class="flex flex-col items-center rounded-lg border border-slate-100 p-2">
                        <svg viewBox="0 0 120 120" class="h-28 w-28">
                            <circle cx="60" cy="60" r="42" fill="none" stroke="#e2e8f0" stroke-width="14" />
                            <circle
                                v-for="segment in donutSegments"
                                :key="segment.key"
                                cx="60"
                                cy="60"
                                r="42"
                                fill="none"
                                :stroke="segment.color"
                                stroke-width="14"
                                stroke-linecap="butt"
                                :stroke-dasharray="`${segment.length} ${donutCircumference - segment.length}`"
                                :stroke-dashoffset="segment.offset"
                                transform="rotate(-90 60 60)"
                            />
                            <text x="60" y="58" text-anchor="middle" class="fill-slate-800 text-[10px] font-semibold">Devices</text>
                            <text x="60" y="72" text-anchor="middle" class="fill-slate-500 text-[9px]">{{ number(deviceTotal) }}</text>
                        </svg>
                        <div class="mt-1 w-full space-y-1">
                            <div v-for="segment in donutSegments" :key="`${segment.key}-label`" class="flex items-center justify-between text-[11px]">
                                <span class="inline-flex items-center gap-1 text-slate-600">
                                    <span class="h-2 w-2 rounded-full" :style="{ backgroundColor: segment.color }" />
                                    {{ segment.label }}
                                </span>
                                <span class="font-semibold text-slate-800">{{ segment.value }}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Operating Systems</p>
                        <div class="space-y-1.5">
                            <div v-for="row in breakdowns.os" :key="`o-${row.label}`" class="flex items-center justify-between rounded border border-slate-100 px-2 py-1.5 text-xs">
                                <span class="truncate">{{ row.label }}</span>
                                <span class="font-semibold">{{ row.total }}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Top Referrers</p>
                        <div class="space-y-1.5">
                            <div v-for="row in breakdowns.referrers" :key="`r-${row.label}`" class="flex items-center justify-between rounded border border-slate-100 px-2 py-1.5 text-xs">
                                <span class="truncate">{{ row.label }}</span>
                                <span class="font-semibold">{{ row.total }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>
        </div>
    </div>
</template>

<script setup>
import StatsCard from '@/components/dashboard/StatsCard.vue';
import Badge from '@/components/ui/Badge.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import { ArrowPathIcon, FunnelIcon } from '@heroicons/vue/24/outline';
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive, watch } from 'vue';

const props = defineProps({
    filters: { type: Object, required: true },
    filterOptions: { type: Object, required: true },
    summary: { type: Object, required: true },
    comparisons: { type: Object, required: true },
    ranges: { type: Object, required: true },
    charts: { type: Object, required: true },
    topCards: { type: Array, required: true },
    topLinks: { type: Array, required: true },
    breakdowns: { type: Object, required: true },
});

const filterState = reactive({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
    compare: props.filters.compare,
    card_id: props.filters.card_id ?? '',
    device: props.filters.device ?? '',
    browser: props.filters.browser ?? '',
});

watch(
    () => props.filters,
    (value) => {
        filterState.start_date = value.start_date;
        filterState.end_date = value.end_date;
        filterState.compare = value.compare;
        filterState.card_id = value.card_id ?? '';
        filterState.device = value.device ?? '';
        filterState.browser = value.browser ?? '';
    }
);

const queryPayload = () => ({
    start_date: filterState.start_date,
    end_date: filterState.end_date,
    compare: filterState.compare,
    card_id: filterState.card_id || undefined,
    device: filterState.device || undefined,
    browser: filterState.browser || undefined,
});

const applyFilters = () => {
    router.get('/analytics', queryPayload(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    filterState.start_date = new Date(Date.now() - (29 * 24 * 3600 * 1000)).toISOString().slice(0, 10);
    filterState.end_date = new Date().toISOString().slice(0, 10);
    filterState.compare = 'previous_period';
    filterState.card_id = '';
    filterState.device = '';
    filterState.browser = '';
    applyFilters();
};

const number = (value) => new Intl.NumberFormat('en-US').format(Number(value || 0));

const deltaLabel = (delta) => {
    if (!delta) return 'No data';
    if (delta.direction === 'flat') return '0.00%';
    const sign = delta.value > 0 ? '+' : '';
    return `${sign}${delta.value}%`;
};

const deltaTone = (delta) => {
    if (!delta || delta.direction === 'flat') return 'neutral';
    return delta.direction === 'up' ? 'positive' : 'negative';
};

const barWidth = (value, max) => {
    if (!max || max <= 0) return 0;
    return Math.max(2, (value / max) * 100);
};

const chartWidth = 920;
const chartHeight = 320;
const chartPadding = { top: 16, right: 18, bottom: 28, left: 36 };

const currentLabels = computed(() => props.charts.current?.labels ?? []);
const currentViews = computed(() => props.charts.current?.views ?? []);
const currentClicks = computed(() => props.charts.current?.clicks ?? []);
const previousViews = computed(() => props.charts.previous?.views ?? []);
const previousClicks = computed(() => props.charts.previous?.clicks ?? []);

const showCompareSeries = computed(() => previousViews.value.length > 0 || previousClicks.value.length > 0);

const maxChartValue = computed(() => {
    const values = [
        ...currentViews.value,
        ...currentClicks.value,
        ...previousViews.value,
        ...previousClicks.value,
    ];

    return Math.max(...values, 1);
});

const chartPoints = computed(() => {
    const count = Math.max(currentLabels.value.length, 1);
    const usableWidth = chartWidth - chartPadding.left - chartPadding.right;
    const usableHeight = chartHeight - chartPadding.top - chartPadding.bottom;

    return currentLabels.value.map((label, idx) => {
        const x = chartPadding.left + (idx / Math.max(count - 1, 1)) * usableWidth;
        const viewsY = chartPadding.top + (1 - ((currentViews.value[idx] ?? 0) / maxChartValue.value)) * usableHeight;
        const clicksY = chartPadding.top + (1 - ((currentClicks.value[idx] ?? 0) / maxChartValue.value)) * usableHeight;

        return { label, x, viewsY, clicksY };
    });
});

const lineFromValues = (values) => {
    const count = Math.max(currentLabels.value.length, 1);
    const usableWidth = chartWidth - chartPadding.left - chartPadding.right;
    const usableHeight = chartHeight - chartPadding.top - chartPadding.bottom;

    return currentLabels.value.map((_, idx) => {
        const x = chartPadding.left + (idx / Math.max(count - 1, 1)) * usableWidth;
        const y = chartPadding.top + (1 - ((values[idx] ?? 0) / maxChartValue.value)) * usableHeight;
        return `${x},${y}`;
    }).join(' ');
};

const viewsLinePoints = computed(() => lineFromValues(currentViews.value));
const clicksLinePoints = computed(() => lineFromValues(currentClicks.value));
const previousViewsLinePoints = computed(() => lineFromValues(previousViews.value));
const previousClicksLinePoints = computed(() => lineFromValues(previousClicks.value));

const viewsAreaPath = computed(() => {
    if (!chartPoints.value.length) return '';
    const first = chartPoints.value[0];
    const last = chartPoints.value[chartPoints.value.length - 1];
    const baselineY = chartHeight - chartPadding.bottom;
    const linePart = chartPoints.value.map((point, idx) => `${idx === 0 ? 'M' : 'L'} ${point.x} ${point.viewsY}`).join(' ');
    return `${linePart} L ${last.x} ${baselineY} L ${first.x} ${baselineY} Z`;
});

const yTicks = computed(() => {
    const steps = 4;
    const usableHeight = chartHeight - chartPadding.top - chartPadding.bottom;
    return Array.from({ length: steps + 1 }).map((_, idx) => {
        const ratio = idx / steps;
        return {
            y: chartPadding.top + ratio * usableHeight,
            value: Math.round((1 - ratio) * maxChartValue.value),
        };
    });
});

const xTickLabels = computed(() => {
    if (!currentLabels.value.length) return [];
    const sampleCount = 6;
    const step = Math.max(1, Math.floor(currentLabels.value.length / sampleCount));
    return currentLabels.value.filter((_, idx) => idx % step === 0).slice(0, sampleCount);
});

const maxTopLinkClicks = computed(() => Math.max(...props.topLinks.map((row) => row.clicks), 1));
const maxBrowserTotal = computed(() => Math.max(...props.breakdowns.browsers.map((row) => row.total), 1));

const deviceTotal = computed(() => props.breakdowns.devices.reduce((sum, row) => sum + Number(row.total || 0), 0));
const donutCircumference = 2 * Math.PI * 42;
const donutPalette = ['#6DBE45', '#111111', '#22c55e', '#64748b', '#94a3b8'];

const donutSegments = computed(() => {
    let cumulative = 0;

    return props.breakdowns.devices.slice(0, 5).map((row, index) => {
        const value = Number(row.total || 0);
        const ratio = deviceTotal.value > 0 ? value / deviceTotal.value : 0;
        const length = ratio * donutCircumference;
        const offset = -cumulative;
        cumulative += length;

        return {
            key: `${row.label}-${index}`,
            label: row.label,
            value,
            length,
            offset,
            color: donutPalette[index % donutPalette.length],
        };
    });
});
</script>
