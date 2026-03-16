<template>
    <Head title="Users" />
    <div class="space-y-5">
        <Card>
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-slate-900">System Users</h1>
                    <p class="mt-1 text-sm text-slate-500">Admin view with all users in ReferyApp.</p>
                </div>
                <span class="rounded-full bg-[#6DBE45]/15 px-3 py-1 text-xs font-semibold text-[#111111]">
                    {{ users.total }} total
                </span>
            </div>
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Name</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Email</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Role</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Plan</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Paid Tx</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Paid Total</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Last Paid</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="item in users.data" :key="item.id">
                            <td class="px-4 py-3 text-slate-800">{{ item.name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ item.email }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ item.role }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ item.plan }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ item.paid_transactions_count || 0 }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ formatMoney(item.paid_total_cents || 0) }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ formatDate(item.last_paid_at) }}</td>
                            <td class="px-4 py-3">
                                <Link
                                    :href="`/users/${item.id}`"
                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-[#6DBE45] hover:text-[#111111]"
                                >
                                    <EyeIcon class="h-4 w-4" />
                                    Detail
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <Card>
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-semibold tracking-tight text-slate-900">Paying Businesses</h2>
                    <p class="mt-1 text-sm text-slate-500">Businesses with at least one successful payment.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                    {{ payingBusinesses.length }} records
                </span>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Business</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Email</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Transactions</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Revenue</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Last Paid</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="item in payingBusinesses" :key="`payer-${item.id}`">
                            <td class="px-4 py-3 text-slate-800">{{ item.name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ item.email }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ item.paid_transactions_count || 0 }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ formatMoney(item.paid_total_cents || 0) }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ formatDate(item.last_paid_at) }}</td>
                        </tr>
                        <tr v-if="!payingBusinesses.length">
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">No paid business accounts yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </div>
</template>

<script setup>
import Card from '@/components/ui/Card.vue';
import { EyeIcon } from '@heroicons/vue/24/outline';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    users: {
        type: Object,
        required: true,
    },
    payingBusinesses: {
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
