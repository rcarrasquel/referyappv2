<template>
    <Head :title="copy.title" />

    <AuthShell>
        <template #heading>
            <h2 class="mt-2 text-3xl font-semibold tracking-tight text-[#111111]">{{ copy.heading }}</h2>
            <p class="mt-2 text-sm text-slate-600">{{ copy.subheading }}</p>
        </template>

        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="email">{{ copy.email }}</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-slate-900 focus:ring focus:ring-[#6DBE45]"
                    required
                >
                <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600">{{ form.errors.email }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="password">{{ copy.password }}</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-slate-900 focus:ring focus:ring-[#6DBE45]"
                    required
                >
                <p v-if="form.errors.password" class="mt-1 text-xs text-rose-600">{{ form.errors.password }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="password_confirmation">{{ copy.confirmPassword }}</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-slate-900 focus:ring focus:ring-[#6DBE45]"
                    required
                >
            </div>

            <Button type="submit" class="w-full" :disabled="form.processing">
                <span class="inline-flex items-center gap-2">
                    <KeyIcon class="h-4 w-4" />
                    {{ copy.cta }}
                </span>
            </Button>
        </form>
    </AuthShell>
</template>

<script setup>
import AuthShell from '@/components/auth/AuthShell.vue';
import Button from '@/components/ui/Button.vue';
import { KeyIcon } from '@heroicons/vue/24/outline';
import { useLocale } from '@/composables/useLocale';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    email: {
        type: String,
        default: '',
    },
    token: {
        type: String,
        required: true,
    },
});

const { t } = useLocale();

const copy = computed(() =>
    t({
        en: {
            title: 'Reset Password',
            heading: 'Create a new password',
            subheading: 'Set a strong password to secure your account.',
            email: 'Email',
            password: 'Password',
            confirmPassword: 'Confirm password',
            cta: 'Reset password',
        },
        es: {
            title: 'Restablecer clave',
            heading: 'Crea una nueva clave',
            subheading: 'Define una clave segura para proteger tu cuenta.',
            email: 'Email',
            password: 'Password',
            confirmPassword: 'Confirmar password',
            cta: 'Restablecer clave',
        },
    })
);

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/reset-password', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>
