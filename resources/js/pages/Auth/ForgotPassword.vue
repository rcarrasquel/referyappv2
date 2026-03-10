<template>
    <Head :title="copy.title" />

    <AuthShell>
        <template #heading>
            <h2 class="mt-2 text-3xl font-semibold tracking-tight text-[#111111]">{{ copy.heading }}</h2>
            <p class="mt-2 text-sm text-slate-600">{{ copy.subheading }}</p>
        </template>

        <p v-if="status" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
            {{ status }}
        </p>

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

            <Button type="submit" class="w-full" :disabled="form.processing">
                <span class="inline-flex items-center gap-2">
                    <PaperAirplaneIcon class="h-4 w-4" />
                    {{ copy.cta }}
                </span>
            </Button>
        </form>

        <p class="mt-5 text-sm text-slate-600">
            <Link href="/login" class="font-semibold text-[#111111] underline decoration-[#6DBE45] decoration-2 underline-offset-4 hover:text-black">
                {{ copy.backToLogin }}
            </Link>
        </p>
    </AuthShell>
</template>

<script setup>
import AuthShell from '@/components/auth/AuthShell.vue';
import Button from '@/components/ui/Button.vue';
import { PaperAirplaneIcon } from '@heroicons/vue/24/outline';
import { useLocale } from '@/composables/useLocale';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const { t } = useLocale();
const page = usePage();
const status = computed(() => page.props.flash?.status ?? '');

const copy = computed(() =>
    t({
        en: {
            title: 'Recover Password',
            heading: 'Recover your password',
            subheading: 'Enter your email and we will send a reset link.',
            email: 'Email',
            cta: 'Send reset link',
            backToLogin: 'Back to login',
        },
        es: {
            title: 'Recuperar clave',
            heading: 'Recupera tu clave',
            subheading: 'Ingresa tu email y te enviaremos un enlace para restablecerla.',
            email: 'Email',
            cta: 'Enviar enlace de recuperacion',
            backToLogin: 'Volver al login',
        },
    })
);

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/forgot-password');
};
</script>
