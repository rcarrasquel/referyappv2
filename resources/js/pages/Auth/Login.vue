<template>
    <Head :title="copy.title" />

    <AuthShell>
        <template #heading>
            <h2 class="mt-2 text-3xl font-semibold tracking-tight text-[#111111]">{{ copy.heading }}</h2>
            <p class="mt-2 text-sm text-slate-600">{{ copy.subheading }}</p>
        </template>

        <p v-if="flashStatus" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
            {{ flashStatus }}
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

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="password">{{ copy.password }}</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    autocomplete="current-password"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-slate-900 focus:ring focus:ring-[#6DBE45]"
                    required
                >
                <p v-if="form.errors.password" class="mt-1 text-xs text-rose-600">{{ form.errors.password }}</p>
            </div>

            <div class="flex items-center justify-between gap-3">
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input v-model="form.remember" type="checkbox" class="rounded border-slate-300 text-[#6DBE45] focus:ring-[#6DBE45]">
                    {{ copy.remember }}
                </label>
                <Link href="/forgot-password" class="text-xs font-medium text-[#111111] underline decoration-[#6DBE45] decoration-2 underline-offset-4">
                    {{ copy.forgotPassword }}
                </Link>
            </div>

            <Button type="submit" class="w-full" :disabled="form.processing">
                <span class="inline-flex items-center gap-2">
                    <ArrowRightOnRectangleIcon class="h-4 w-4" />
                    {{ copy.cta }}
                </span>
            </Button>
        </form>

        <div class="mt-6 rounded-xl border border-[#6DBE45]/30 bg-[#6DBE45]/10 p-3 text-xs text-slate-700">
            {{ copy.freeProNotice }}
        </div>

        <p class="mt-5 text-sm text-slate-600">
            {{ copy.noAccount }}
            <Link href="/register" class="font-semibold text-[#111111] underline decoration-[#6DBE45] decoration-2 underline-offset-4 hover:text-black">
                {{ copy.register }}
            </Link>
        </p>
    </AuthShell>
</template>

<script setup>
import AuthShell from '@/components/auth/AuthShell.vue';
import Button from '@/components/ui/Button.vue';
import { ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline';
import { useLocale } from '@/composables/useLocale';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const { t } = useLocale();
const page = usePage();

const flashStatus = computed(() => page.props.flash?.status ?? '');

const copy = computed(() =>
    t({
        en: {
            title: 'Login',
            heading: 'Sign in to ReferyApp',
            subheading: 'Access your digital card and manage your presence from one place.',
            email: 'Email',
            password: 'Password',
            remember: 'Remember me',
            forgotPassword: 'Forgot password?',
            cta: 'Sign in',
            freeProNotice: 'Start free. Upgrade later to monthly Pro when you need advanced features.',
            noAccount: "Don't have an account?",
            register: 'Create free account',
        },
        es: {
            title: 'Iniciar sesion',
            heading: 'Inicia sesion en ReferyApp',
            subheading: 'Accede a tu tarjeta digital y administra tu presencia desde un solo lugar.',
            email: 'Email',
            password: 'Password',
            remember: 'Recordarme',
            forgotPassword: 'Olvidaste tu clave?',
            cta: 'Entrar',
            freeProNotice: 'Empieza gratis. Luego activa Pro mensual cuando necesites funciones avanzadas.',
            noAccount: 'Aun no tienes cuenta?',
            register: 'Crear cuenta gratis',
        },
    })
);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>
