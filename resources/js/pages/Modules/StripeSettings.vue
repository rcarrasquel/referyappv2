<template>
    <Head title="Stripe Settings" />

    <div class="space-y-5">
        <div class="rounded-2xl bg-[linear-gradient(130deg,#111111_0%,#173010_58%,#6DBE45_115%)] p-5 text-white shadow-[0_22px_45px_rgba(8,12,8,0.42)]">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#c7f4b2]">Admin Billing</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">Stripe Settings</h1>
            <p class="mt-1 text-sm text-white/75">Configure billing keys and monthly subscription price.</p>
        </div>

        <Card class="max-w-4xl">
            <p v-if="flashStatus" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ flashStatus }}
            </p>

            <form class="grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Publishable Key</label>
                    <input v-model="form.publishable_key" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                    <p v-if="form.errors.publishable_key" class="mt-1 text-xs text-rose-600">{{ form.errors.publishable_key }}</p>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Secret Key</label>
                    <input v-model="form.secret_key" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                    <p v-if="form.errors.secret_key" class="mt-1 text-xs text-rose-600">{{ form.errors.secret_key }}</p>
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Webhook Secret</label>
                    <input v-model="form.webhook_secret" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                    <p v-if="form.errors.webhook_secret" class="mt-1 text-xs text-rose-600">{{ form.errors.webhook_secret }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Currency</label>
                    <input v-model="form.currency" type="text" maxlength="3" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 uppercase">
                    <p v-if="form.errors.currency" class="mt-1 text-xs text-rose-600">{{ form.errors.currency }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Monthly Price (cents)</label>
                    <input v-model.number="form.monthly_price_cents" type="number" min="100" class="w-full rounded-lg border border-slate-200 px-3 py-2.5">
                    <p class="mt-1 text-xs text-slate-500">For $9/month use 900.</p>
                    <p v-if="form.errors.monthly_price_cents" class="mt-1 text-xs text-rose-600">{{ form.errors.monthly_price_cents }}</p>
                </div>

                <div class="sm:col-span-2">
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input v-model="form.is_active" type="checkbox" class="rounded border-slate-300 text-[#6DBE45] focus:ring-[#6DBE45]">
                        Active
                    </label>
                    <p class="mt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600">
                        Webhook URL: <span class="font-semibold">{{ webhookUrl }}</span>
                    </p>
                </div>

                <div class="sm:col-span-2">
                    <Button type="submit" :disabled="form.processing">
                        <span class="inline-flex items-center gap-2">
                            <CheckIcon class="h-4 w-4" />
                            Save Stripe Settings
                        </span>
                    </Button>
                </div>
            </form>
        </Card>
    </div>
</template>

<script setup>
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import { CheckIcon } from '@heroicons/vue/24/outline';
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
    publishable_key: props.settings.publishable_key ?? '',
    secret_key: props.settings.secret_key ?? '',
    webhook_secret: props.settings.webhook_secret ?? '',
    currency: props.settings.currency ?? 'usd',
    monthly_price_cents: props.settings.monthly_price_cents ?? 900,
    is_active: !!props.settings.is_active,
});

const webhookUrl = computed(() => `${window.location.origin}/api/v1/stripe/webhook`);

const submit = () => {
    form.put('/stripe-settings', {
        preserveScroll: true,
    });
};
</script>
