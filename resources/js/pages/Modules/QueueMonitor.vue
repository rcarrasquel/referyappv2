<template>
    <Head title="Queue Monitor" />

    <div class="space-y-5">
        <div class="rounded-2xl border border-[#264318] bg-[#111111] p-5 text-white shadow-[0_22px_45px_rgba(8,12,8,0.42)]">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#c7f4b2]">{{ copy.adminOps }}</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">{{ copy.title }}</h1>
            <p class="mt-1 text-sm text-white/75">{{ copy.subtitle }}</p>
        </div>

        <p v-if="flashStatus" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
            {{ flashStatus }}
        </p>

        <Card>
            <div class="mb-3 flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">{{ copy.pending }}</h2>
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">{{ appointments.pending.length }}</span>
            </div>
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Card</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Client</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">When</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Owner</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="item in appointments.pending" :key="item.id">
                            <td class="px-4 py-3 text-slate-800">{{ item.card_name || `@${item.card_username}` }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ item.full_name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ formatDate(item.starts_at) }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ item.owner_name }}</td>
                            <td class="px-4 py-3"><span class="rounded bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">pending</span></td>
                        </tr>
                        <tr v-if="!appointments.pending.length">
                            <td colspan="5" class="px-4 py-5 text-center text-slate-500">{{ copy.empty }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <Card>
            <div class="mb-3 flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">{{ copy.sent }}</h2>
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ appointments.synced.length }}</span>
            </div>
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Card</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Client</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">When</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Synced At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="item in appointments.synced" :key="item.id">
                            <td class="px-4 py-3 text-slate-800">{{ item.card_name || `@${item.card_username}` }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ item.full_name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ formatDate(item.starts_at) }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ formatDate(item.cal_synced_at) }}</td>
                        </tr>
                        <tr v-if="!appointments.synced.length">
                            <td colspan="4" class="px-4 py-5 text-center text-slate-500">{{ copy.empty }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <Card>
            <div class="mb-3 flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">{{ copy.failed }}</h2>
                <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">{{ appointments.failed.length }}</span>
            </div>
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Card</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Client</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Error</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="item in appointments.failed" :key="item.id">
                            <td class="px-4 py-3 text-slate-800">{{ item.card_name || `@${item.card_username}` }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ item.full_name }}</td>
                            <td class="px-4 py-3 text-xs text-rose-700">{{ item.cal_sync_error || '-' }}</td>
                            <td class="px-4 py-3">
                                <Button size="sm" @click="retryAppointment(item.id)">
                                    <span class="inline-flex items-center gap-1">
                                        <ArrowPathIcon class="h-4 w-4" />
                                        {{ copy.retry }}
                                    </span>
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="!appointments.failed.length">
                            <td colspan="4" class="px-4 py-5 text-center text-slate-500">{{ copy.empty }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <Card>
            <div class="mb-3 flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">{{ copy.queueJobs }}</h2>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ jobs.queued.length }}</span>
            </div>
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">ID</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Queue</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Attempts</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Created</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="item in jobs.queued" :key="`queued-${item.id}`">
                            <td class="px-4 py-3 text-slate-700">{{ item.id }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ item.queue }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ item.attempts }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ formatDate(item.created_at) }}</td>
                            <td class="px-4 py-3">
                                <button type="button" class="inline-flex items-center gap-1 rounded-lg bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-rose-700" @click="deleteQueuedJob(item.id)">
                                    <TrashIcon class="h-4 w-4" />
                                    {{ copy.remove }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!jobs.queued.length">
                            <td colspan="5" class="px-4 py-5 text-center text-slate-500">{{ copy.empty }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <Card>
            <div class="mb-3 flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">{{ copy.failedJobs }}</h2>
                <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">{{ jobs.failed.length }}</span>
            </div>
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">ID</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Queue</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Failed At</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="item in jobs.failed" :key="`failed-${item.id}`">
                            <td class="px-4 py-3 text-slate-700">{{ item.id }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ item.queue }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ formatDate(item.failed_at) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <Button size="sm" @click="retryFailed(item.id)">
                                        <span class="inline-flex items-center gap-1">
                                            <ArrowPathIcon class="h-4 w-4" />
                                            {{ copy.retry }}
                                        </span>
                                    </Button>
                                    <button type="button" class="inline-flex items-center gap-1 rounded-lg bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-rose-700" @click="deleteFailed(item.id)">
                                        <TrashIcon class="h-4 w-4" />
                                        {{ copy.remove }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!jobs.failed.length">
                            <td colspan="4" class="px-4 py-5 text-center text-slate-500">{{ copy.empty }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </div>
</template>

<script setup>
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import { useLocale } from '@/composables/useLocale';
import { ArrowPathIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    appointments: { type: Object, required: true },
    jobs: { type: Object, required: true },
});

const { t } = useLocale();
const page = usePage();
const flashStatus = computed(() => page.props.flash?.status ?? '');

const copy = computed(() => t({
    en: {
        adminOps: 'Admin Queue',
        title: 'Queue Monitor',
        subtitle: 'Track pending jobs, synced operations and failed tasks.',
        pending: 'Pending Sync',
        sent: 'Sent / Synced',
        failed: 'Failed Sync',
        queueJobs: 'Queued Jobs',
        failedJobs: 'Failed Jobs',
        retry: 'Retry',
        remove: 'Remove',
        empty: 'No records found.',
    },
    es: {
        adminOps: 'Cola Admin',
        title: 'Monitor de Cola',
        subtitle: 'Rastrea pendientes, sincronizados y trabajos fallidos.',
        pending: 'Pendientes',
        sent: 'Enviados / Sincronizados',
        failed: 'Sincronizacion Fallida',
        queueJobs: 'Trabajos en Cola',
        failedJobs: 'Trabajos Fallidos',
        retry: 'Reintentar',
        remove: 'Eliminar',
        empty: 'Sin registros.',
    },
}));

const formatDate = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleString();
};

const retryAppointment = (id) => {
    router.post(`/queue-monitor/appointments/${id}/retry`, {}, { preserveScroll: true });
};

const deleteQueuedJob = (id) => {
    router.delete(`/queue-monitor/jobs/${id}`, { preserveScroll: true });
};

const retryFailed = (id) => {
    router.post(`/queue-monitor/failed-jobs/${id}/retry`, {}, { preserveScroll: true });
};

const deleteFailed = (id) => {
    router.delete(`/queue-monitor/failed-jobs/${id}`, { preserveScroll: true });
};
</script>

