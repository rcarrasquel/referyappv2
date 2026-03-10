<template>
    <Head title="Users" />

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
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Language</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    <tr v-for="item in users.data" :key="item.id">
                        <td class="px-4 py-3 text-slate-800">{{ item.name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ item.email }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ item.role }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ item.plan }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ item.language }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ formatDate(item.created_at) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </Card>
</template>

<script setup>
import Card from '@/components/ui/Card.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    users: {
        type: Object,
        required: true,
    },
});

const formatDate = (value) => {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleDateString();
};
</script>
