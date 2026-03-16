<template>
    <Head title="User Detail" />

    <div class="space-y-5">
        <Card class="overflow-hidden border border-[#264318] bg-[linear-gradient(145deg,#111111_0%,#173010_65%,#6DBE45_140%)] text-white shadow-[0_18px_38px_rgba(9,14,8,0.42)]">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#c7f4b2]">Admin User Detail</p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight">{{ userItem.name }}</h1>
                    <p class="mt-1 text-sm text-white/80">{{ userItem.email }}</p>
                    <p class="mt-1 text-xs text-white/70">{{ userItem.role }} · {{ userItem.plan }} · {{ userItem.language }}</p>
                </div>
                <Link
                    href="/users"
                    class="inline-flex items-center rounded-lg border border-white/30 bg-white/10 px-3 py-2 text-xs font-semibold text-white transition hover:bg-white/15"
                >
                    Back to users
                </Link>
            </div>
        </Card>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <Card><p class="text-xs text-slate-500">Cards</p><p class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.cards_total }}</p></Card>
            <Card><p class="text-xs text-slate-500">Products</p><p class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.products_total }}</p></Card>
            <Card><p class="text-xs text-slate-500">Appointments</p><p class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.appointments_total }}</p></Card>
            <Card><p class="text-xs text-slate-500">Leads</p><p class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.leads_total }}</p></Card>
            <Card><p class="text-xs text-slate-500">Card Views</p><p class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.visits_total }}</p></Card>
            <Card><p class="text-xs text-slate-500">Link Clicks</p><p class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.clicks_total }}</p></Card>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <Card><p class="text-xs text-slate-500">Paid Transactions</p><p class="mt-1 text-2xl font-semibold text-slate-900">{{ summary.payments_count || 0 }}</p></Card>
            <Card><p class="text-xs text-slate-500">Total Paid</p><p class="mt-1 text-2xl font-semibold text-slate-900">{{ formatMoney(summary.payments_total_cents || 0) }}</p></Card>
            <Card><p class="text-xs text-slate-500">Last Payment</p><p class="mt-1 text-2xl font-semibold text-slate-900">{{ formatDate(summary.payments_last_at) }}</p></Card>
        </div>

        <Card>
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold tracking-tight text-slate-900">Cards Metrics</h2>
                    <p class="mt-1 text-sm text-slate-500">Performance by card for this user.</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Card</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Username</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Views (Total)</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Clicks (Total)</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Views (30d)</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Clicks (30d)</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">CTR (30d)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="item in cardsMetrics" :key="item.id">
                            <td class="px-4 py-3 text-slate-800">{{ item.name || 'Untitled card' }}</td>
                            <td class="px-4 py-3 text-slate-600">@{{ item.username }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ item.visits_total }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ item.clicks_total }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ item.visits_30d }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ item.clicks_30d }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ item.ctr_30d }}%</td>
                        </tr>
                        <tr v-if="!cardsMetrics.length">
                            <td colspan="7" class="px-4 py-6 text-center text-slate-500">No cards available for this user.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <Card>
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold tracking-tight text-slate-900">Payment History</h2>
                    <p class="mt-1 text-sm text-slate-500">Latest successful payments for this user.</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Date</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Description</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Amount</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="payment in payments" :key="`payment-${payment.id}`">
                            <td class="px-4 py-3 text-slate-600">{{ formatDate(payment.paid_at || payment.created_at) }}</td>
                            <td class="px-4 py-3 text-slate-800">{{ payment.description || 'ReferyApp Business Plan' }}</td>
                            <td class="px-4 py-3 text-slate-800">{{ formatMoney(payment.amount_cents, payment.currency) }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                    {{ payment.status }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="!payments.length">
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">No payments found for this user.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </div>
</template>

<script setup>
import Card from '@/components/ui/Card.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    userItem: {
        type: Object,
        required: true,
    },
    summary: {
        type: Object,
        required: true,
    },
    cardsMetrics: {
        type: Array,
        default: () => [],
    },
    payments: {
        type: Array,
        default: () => [],
    },
});

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
</script>
