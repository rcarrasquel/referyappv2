<template>
    <Head :title="copy.title" />

    <AuthShell>
        <template #heading>
            <h2 class="mt-2 text-3xl font-semibold tracking-tight text-[#111111]">{{ copy.heading }}</h2>
            <p class="mt-2 text-sm text-slate-600">{{ copy.subheading }}</p>
        </template>

        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700" for="name">{{ copy.name }}</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    autocomplete="name"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-slate-900 focus:ring focus:ring-[#6DBE45]"
                    required
                >
                <p v-if="form.errors.name" class="mt-1 text-xs text-rose-600">{{ form.errors.name }}</p>
            </div>

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
                    <UserPlusIcon class="h-4 w-4" />
                    {{ copy.cta }}
                </span>
            </Button>
        </form>

        <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-700">
            {{ copy.freeProNotice }}
        </div>

        <p class="mt-5 text-sm text-slate-600">
            {{ copy.hasAccount }}
            <Link href="/login" class="font-semibold text-[#111111] underline decoration-[#6DBE45] decoration-2 underline-offset-4 hover:text-black">
                {{ copy.login }}
            </Link>
        </p>
    </AuthShell>
</template>

<script setup>
import AuthShell from '@/components/auth/AuthShell.vue';
import Button from '@/components/ui/Button.vue';
import { UserPlusIcon } from '@heroicons/vue/24/outline';
import { useLocale } from '@/composables/useLocale';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const { t } = useLocale();

const copy = computed(() =>
    t({
        en: {
            title: 'Register',
            heading: 'Create your free account',
            subheading: 'Launch your professional link-in-bio profile in minutes.',
            name: 'Name',
            email: 'Email',
            password: 'Password',
            confirmPassword: 'Confirm password',
            cta: 'Create free account',
            freeProNotice: 'Your account starts on Free. Upgrade to monthly Pro only when you want advanced tools.',
            hasAccount: 'Already have an account?',
            login: 'Sign in',
        },
        es: {
            title: 'Registro',
            heading: 'Crea tu cuenta gratis',
            subheading: 'Lanza tu perfil tipo link-in-bio con estilo profesional en minutos.',
            name: 'Nombre',
            email: 'Email',
            password: 'Password',
            confirmPassword: 'Confirmar password',
            cta: 'Crear cuenta gratis',
            freeProNotice: 'Tu cuenta inicia en Free. Activa Pro mensual solo cuando quieras funciones avanzadas.',
            hasAccount: 'Ya tienes cuenta?',
            login: 'Iniciar sesion',
        },
    })
);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>
