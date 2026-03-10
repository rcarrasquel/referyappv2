<template>
    <Head title="Update Profile" />

    <div class="space-y-5">
        <div class="mb-6 rounded-2xl bg-[linear-gradient(130deg,#111111_0%,#173010_58%,#6DBE45_115%)] p-5 text-white shadow-[0_22px_45px_rgba(8,12,8,0.42)]">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#c7f4b2]">Account Settings</p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-white">Update Profile</h1>
                    <p class="mt-1 text-sm text-white/75">Update your basic account information.</p>
                </div>
            </div>
        </div>

        <div class="max-w-3xl">
            <Card>

            <p
                v-if="flashStatus"
                class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700"
            >
                {{ flashStatus }}
            </p>

            <form class="mt-6 space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1 flex items-center gap-2 text-sm font-medium text-slate-700" for="name">
                        <UserCircleIcon class="h-4 w-4" />
                        Name
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5 focus:ring focus:ring-[#6DBE45]"
                        required
                    >
                    <p v-if="form.errors.name" class="mt-1 text-xs text-rose-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="mb-1 flex items-center gap-2 text-sm font-medium text-slate-700" for="email">
                        <EnvelopeIcon class="h-4 w-4" />
                        Email
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        :disabled="!isAdmin"
                        :readonly="!isAdmin"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5 focus:ring focus:ring-[#6DBE45] disabled:cursor-not-allowed disabled:bg-slate-100"
                        required
                    >
                    <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="mb-1 flex items-center gap-2 text-sm font-medium text-slate-700" for="language">
                        <LanguageIcon class="h-4 w-4" />
                        Language
                    </label>
                    <select
                        id="language"
                        v-model="form.language"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5 focus:ring focus:ring-[#6DBE45]"
                    >
                        <option value="en">English</option>
                        <option value="es">Spanish</option>
                    </select>
                    <p v-if="form.errors.language" class="mt-1 text-xs text-rose-600">{{ form.errors.language }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-800">
                        <KeyIcon class="h-4 w-4" />
                        Change Password
                    </p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="current_password">Current Password</label>
                            <input
                                id="current_password"
                                v-model="form.current_password"
                                type="password"
                                autocomplete="current-password"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2.5 focus:ring focus:ring-[#6DBE45]"
                            >
                            <p v-if="form.errors.current_password" class="mt-1 text-xs text-rose-600">{{ form.errors.current_password }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="password">New Password</label>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                autocomplete="new-password"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2.5 focus:ring focus:ring-[#6DBE45]"
                            >
                            <p v-if="form.errors.password" class="mt-1 text-xs text-rose-600">{{ form.errors.password }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700" for="password_confirmation">Confirm Password</label>
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2.5 focus:ring focus:ring-[#6DBE45]"
                            >
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Plan</p>
                        <p class="mt-1 text-sm font-medium text-slate-800">{{ user.plan }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Member Since</p>
                        <p class="mt-1 text-sm font-medium text-slate-800">{{ memberSince }}</p>
                    </div>
                </div>

                <Button type="submit" :disabled="form.processing">
                    <span class="inline-flex items-center gap-2">
                        <CheckIcon class="h-4 w-4" />
                        Save Changes
                    </span>
                </Button>
            </form>
        </Card>
        </div>
    </div>
</template>

<script setup>
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import { CheckIcon, EnvelopeIcon, KeyIcon, LanguageIcon, UserCircleIcon } from '@heroicons/vue/24/outline';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

const page = usePage();

const isAdmin = computed(() => props.user.role === 'admin');
const flashStatus = computed(() => page.props.flash?.status ?? '');

const memberSince = computed(() => {
    if (!props.user.created_at) {
        return '-';
    }

    return new Date(props.user.created_at).toLocaleDateString();
});

const form = useForm({
    name: props.user.name ?? '',
    email: props.user.email ?? '',
    language: props.user.language ?? 'en',
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form
        .transform((data) => {
            const payload = { ...data };

            if (!isAdmin.value) {
                delete payload.email;
            }

            return payload;
        })
        .put('/profile', {
            preserveScroll: true,
            onSuccess: () => form.reset('current_password', 'password', 'password_confirmation'),
        });
};
</script>
