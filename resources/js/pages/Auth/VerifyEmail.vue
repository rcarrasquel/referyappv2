<template>
    <Head :title="copy.title" />

    <AuthShell>
        <template #heading>
            <h2 class="mt-2 text-3xl font-semibold tracking-tight text-[#111111]">{{ copy.heading }}</h2>
            <p class="mt-2 text-sm text-slate-600">{{ copy.subheading }}</p>
        </template>

        <p class="mb-4 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700">
            {{ copy.sentTo }} <span class="font-semibold">{{ email || '-' }}</span>
        </p>

        <p v-if="flashStatus" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
            {{ flashStatus }}
        </p>

        <Button type="button" class="w-full" :disabled="form.processing" @click="resend">
            <span class="inline-flex items-center gap-2">
                <EnvelopeIcon class="h-4 w-4" />
                {{ copy.resend }}
            </span>
        </Button>

        <div class="mt-4">
            <Link href="/signout" class="text-xs font-medium text-slate-600 underline decoration-[#6DBE45] decoration-2 underline-offset-4">
                {{ copy.logout }}
            </Link>
        </div>
    </AuthShell>
</template>

<script setup>
import AuthShell from '@/components/auth/AuthShell.vue';
import Button from '@/components/ui/Button.vue';
import { EnvelopeIcon } from '@heroicons/vue/24/outline';
import { useLocale } from '@/composables/useLocale';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    email: {
        type: String,
        default: '',
    },
});

const { t } = useLocale();
const page = usePage();
const flashStatus = computed(() => page.props.flash?.status ?? '');

const copy = computed(() =>
    t({
        en: {
            title: 'Verify Email',
            heading: 'Verify your email',
            subheading: 'Before continuing, verify your account using the link we sent.',
            sentTo: 'Verification email sent to:',
            resend: 'Resend verification link',
            logout: 'Logout',
        },
        es: {
            title: 'Verificar correo',
            heading: 'Verifica tu correo',
            subheading: 'Antes de continuar, verifica tu cuenta usando el enlace enviado.',
            sentTo: 'Correo de verificacion enviado a:',
            resend: 'Reenviar enlace de verificacion',
            logout: 'Cerrar sesion',
        },
    })
);

const form = useForm({});

const resend = () => {
    form.post('/email/verification-notification');
};
</script>

