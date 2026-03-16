<template>
    <Head title="Mail Settings" />

    <div class="space-y-5">
        <div class="rounded-2xl bg-[linear-gradient(130deg,#111111_0%,#173010_58%,#6DBE45_115%)] p-5 text-white shadow-[0_22px_45px_rgba(8,12,8,0.42)]">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#c7f4b2]">Admin Mail</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">Mail Settings</h1>
            <p class="mt-1 text-sm text-white/75">Configure SMTP credentials from database and run a delivery test.</p>
        </div>

        <Card class="max-w-5xl">
            <p v-if="flashStatus" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ flashStatus }}
            </p>

            <form class="grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Host</label>
                    <input v-model="form.host" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                    <p v-if="form.errors.host" class="mt-1 text-xs text-rose-600">{{ form.errors.host }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Port</label>
                    <input v-model.number="form.port" type="number" min="1" max="65535" class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                    <p v-if="form.errors.port" class="mt-1 text-xs text-rose-600">{{ form.errors.port }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Encryption</label>
                    <select v-model="form.encryption" class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                        <option value="ssl">ssl</option>
                        <option value="tls">tls</option>
                        <option value="">none</option>
                    </select>
                    <p v-if="form.errors.encryption" class="mt-1 text-xs text-rose-600">{{ form.errors.encryption }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Timeout</label>
                    <input v-model.number="form.timeout" type="number" min="1" max="120" class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                    <p v-if="form.errors.timeout" class="mt-1 text-xs text-rose-600">{{ form.errors.timeout }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Username</label>
                    <input v-model="form.username" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                    <p v-if="form.errors.username" class="mt-1 text-xs text-rose-600">{{ form.errors.username }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                    <input v-model="form.password" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                    <p v-if="form.errors.password" class="mt-1 text-xs text-rose-600">{{ form.errors.password }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">From Address</label>
                    <input v-model="form.from_address" type="email" class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                    <p v-if="form.errors.from_address" class="mt-1 text-xs text-rose-600">{{ form.errors.from_address }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">From Name</label>
                    <input v-model="form.from_name" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                    <p v-if="form.errors.from_name" class="mt-1 text-xs text-rose-600">{{ form.errors.from_name }}</p>
                </div>

                <div class="sm:col-span-2">
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input v-model="form.is_active" type="checkbox" class="rounded border-slate-300 text-[#6DBE45] focus:ring-[#6DBE45]">
                        Active
                    </label>
                </div>

                <div class="sm:col-span-2 flex flex-wrap gap-2">
                    <Button type="submit" :disabled="form.processing">
                        <span class="inline-flex items-center gap-2">
                            <CheckIcon class="h-4 w-4" />
                            Save Mail Settings
                        </span>
                    </Button>
                </div>
            </form>
        </Card>

        <Card class="max-w-3xl">
            <h2 class="text-base font-semibold text-slate-900">Test SMTP Delivery</h2>
            <p class="mt-1 text-sm text-slate-500">Send a test message using the current database SMTP configuration.</p>

            <form class="mt-4 grid gap-3 sm:grid-cols-[1fr,auto]" @submit.prevent="sendTest">
                <input v-model="testForm.test_email" type="email" placeholder="test@domain.com (optional)" class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                <Button type="submit" :disabled="testForm.processing">
                    <span class="inline-flex items-center gap-2">
                        <PaperAirplaneIcon class="h-4 w-4" />
                        Send Test Email
                    </span>
                </Button>
                <p v-if="testForm.errors.test_email" class="sm:col-span-2 text-xs text-rose-600">{{ testForm.errors.test_email }}</p>
            </form>
        </Card>
    </div>
</template>

<script setup>
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import { CheckIcon, PaperAirplaneIcon } from '@heroicons/vue/24/outline';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const flashStatus = computed(() => page.props.flash?.status ?? '');

const form = useForm({
    host: props.settings.host ?? '',
    port: props.settings.port ?? 465,
    encryption: props.settings.encryption ?? 'ssl',
    username: props.settings.username ?? '',
    password: props.settings.password ?? '',
    timeout: props.settings.timeout ?? 30,
    from_address: props.settings.from_address ?? '',
    from_name: props.settings.from_name ?? '',
    is_active: props.settings.is_active ?? true,
});

const testForm = useForm({
    test_email: '',
});

const submit = () => {
    form.put('/mail-settings', {
        preserveScroll: true,
    });
};

const sendTest = () => {
    testForm.post('/mail-settings/test', {
        preserveScroll: true,
        onSuccess: () => testForm.reset('test_email'),
    });
};
</script>

