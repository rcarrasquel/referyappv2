<template>
    <Head title="Update Profile" />

    <div class="space-y-5">
        <div class="mb-6 rounded-2xl bg-[linear-gradient(130deg,#111111_0%,#173010_58%,#6DBE45_115%)] p-5 text-white shadow-[0_22px_45px_rgba(8,12,8,0.42)]">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#c7f4b2]">{{ copy.accountSettings }}</p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-white">{{ copy.updateProfile }}</h1>
                    <p class="mt-1 text-sm text-white/75">{{ copy.updateBasicInfo }}</p>
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
                            {{ copy.name }}
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
                            {{ copy.email }}
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
                            {{ copy.language }}
                        </label>
                        <select
                            id="language"
                            v-model="form.language"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2.5 focus:ring focus:ring-[#6DBE45]"
                        >
                            <option value="en">{{ copy.english }}</option>
                            <option value="es">{{ copy.spanish }}</option>
                        </select>
                        <p v-if="form.errors.language" class="mt-1 text-xs text-rose-600">{{ form.errors.language }}</p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-800">
                            <KeyIcon class="h-4 w-4" />
                            {{ copy.changePassword }}
                        </p>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-sm font-medium text-slate-700" for="current_password">{{ copy.currentPassword }}</label>
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
                                <label class="mb-1 block text-sm font-medium text-slate-700" for="password">{{ copy.newPassword }}</label>
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
                                <label class="mb-1 block text-sm font-medium text-slate-700" for="password_confirmation">{{ copy.confirmPassword }}</label>
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
                            <p class="text-xs uppercase tracking-wide text-slate-400">{{ copy.plan }}</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ user.plan }}</p>
                            <p
                                v-if="isBusinessUser && user.plan !== 'business'"
                                class="mt-2 text-xs text-slate-500"
                            >
                                {{ copy.upgradeHint }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">{{ copy.memberSince }}</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ memberSince }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-1">
                        <Button
                            v-if="isBusinessUser && user.plan !== 'business'"
                            type="button"
                            class="px-6"
                            :disabled="billingForm.processing"
                            @click="goBusiness"
                        >
                            <span class="inline-flex items-center gap-2">
                                <CreditCardIcon class="h-4 w-4" />
                                {{ copy.upgradeBusiness }}
                            </span>
                        </Button>

                        <Button
                            v-if="isBusinessUser && user.plan === 'business'"
                            type="button"
                            variant="danger"
                            class="px-6"
                            :disabled="cancelForm.processing"
                            @click="openCancelModal"
                        >
                            <span class="inline-flex items-center gap-2">
                                <NoSymbolIcon class="h-4 w-4" />
                                {{ copy.cancelSubscription }}
                            </span>
                        </Button>

                        <Button type="submit" class="px-6" :disabled="form.processing">
                            <span class="inline-flex items-center gap-2">
                                <CheckIcon class="h-4 w-4" />
                                {{ copy.saveChanges }}
                            </span>
                        </Button>
                    </div>
                </form>
            </Card>
        </div>

        <Card class="mt-5">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-semibold text-slate-800">{{ copy.paymentHistory }}</p>
                <span class="text-xs text-slate-500">{{ transactions.length }}</span>
            </div>

            <div v-if="transactions.length" class="table-scroll mt-3 max-h-[420px] overflow-x-auto overflow-y-auto rounded-xl border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-2 py-2">{{ copy.date }}</th>
                            <th class="px-2 py-2">{{ copy.description }}</th>
                            <th class="px-2 py-2">{{ copy.amount }}</th>
                            <th class="px-2 py-2">{{ copy.statusLabel }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in transactions"
                            :key="item.id"
                            class="border-b border-slate-100 text-slate-700"
                        >
                            <td class="px-2 py-2">{{ formatDate(item.paid_at || item.created_at) }}</td>
                            <td class="px-2 py-2">{{ item.description || 'ReferyApp Business Plan' }}</td>
                            <td class="px-2 py-2 font-semibold">{{ formatMoney(item.amount_cents, item.currency) }}</td>
                            <td class="px-2 py-2">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                                    :class="item.status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700'"
                                >
                                    {{ item.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p v-else class="mt-3 text-sm text-slate-500">
                {{ copy.noPayments }}
            </p>
        </Card>

        <Modal :show="showCancelModal" :title="copy.cancelSubscription" max-width-class="max-w-xl" @close="closeCancelModal">
            <div class="space-y-3">
                <p class="text-sm text-slate-700">
                    {{ copy.cancelWarningTitle }}
                </p>
                <p class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700">
                    {{ copy.cancelWarningBody }}
                </p>
                <p class="text-xs text-slate-500">
                    {{ copy.cancelWarningExtra }}
                </p>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <Button type="button" variant="secondary" class="min-w-[210px] px-6" @click="closeCancelModal">
                        <span class="inline-flex items-center gap-2">
                            <XMarkIcon class="h-4 w-4" />
                            {{ copy.keepBusiness }}
                        </span>
                    </Button>
                    <Button type="button" variant="danger" class="min-w-[210px] px-6" :disabled="cancelForm.processing" @click="confirmCancelSubscription">
                        <span class="inline-flex items-center gap-2">
                            <ExclamationTriangleIcon class="h-4 w-4" />
                            {{ copy.confirmCancel }}
                        </span>
                    </Button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Modal from '@/components/ui/Modal.vue';
import { useLocale } from '@/composables/useLocale';
import { CheckIcon, CreditCardIcon, EnvelopeIcon, ExclamationTriangleIcon, KeyIcon, LanguageIcon, NoSymbolIcon, UserCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    transactions: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const { t } = useLocale();

const isAdmin = computed(() => props.user.role === 'admin');
const isBusinessUser = computed(() => props.user.role === 'business');
const flashStatus = computed(() => page.props.flash?.status ?? '');
const copy = computed(() => t({
    en: {
        accountSettings: 'Account Settings',
        updateProfile: 'Update Profile',
        updateBasicInfo: 'Update your basic account information.',
        name: 'Name',
        email: 'Email',
        language: 'Language',
        english: 'English',
        spanish: 'Spanish',
        changePassword: 'Change Password',
        currentPassword: 'Current Password',
        newPassword: 'New Password',
        confirmPassword: 'Confirm Password',
        plan: 'Plan',
        memberSince: 'Member Since',
        upgradeBusiness: 'Upgrade to Business ($9/month)',
        cancelSubscription: 'Cancel subscription',
        cancelWarningTitle: 'If you cancel recurring payments your account will be moved to Free plan immediately.',
        cancelWarningBody: 'When you cancel, only 2 products and 1 card will remain.',
        cancelWarningExtra: 'The remaining content will be removed permanently from database and storage.',
        keepBusiness: 'Keep my Business plan',
        confirmCancel: 'Cancel and downgrade',
        upgradeHint: 'Unlock unlimited cards, products and advanced tools.',
        paymentHistory: 'Payment History',
        date: 'Date',
        description: 'Description',
        amount: 'Amount',
        statusLabel: 'Status',
        noPayments: 'No transactions yet.',
        saveChanges: 'Save Changes',
    },
    es: {
        accountSettings: 'Configuracion de Cuenta',
        updateProfile: 'Actualizar Perfil',
        updateBasicInfo: 'Actualiza la informacion basica de tu cuenta.',
        name: 'Nombre',
        email: 'Correo',
        language: 'Idioma',
        english: 'Ingles',
        spanish: 'Espanol',
        changePassword: 'Cambiar Contrasena',
        currentPassword: 'Contrasena Actual',
        newPassword: 'Nueva Contrasena',
        confirmPassword: 'Confirmar Contrasena',
        plan: 'Plan',
        memberSince: 'Miembro Desde',
        upgradeBusiness: 'Actualizar a Business ($9/mes)',
        cancelSubscription: 'Cancelar suscripcion',
        cancelWarningTitle: 'Si cancelas los pagos recurrentes tu cuenta pasara a Free inmediatamente.',
        cancelWarningBody: 'Al cancelar la suscripcion solo quedaran 2 productos y una sola tarjeta.',
        cancelWarningExtra: 'El resto del contenido se eliminara de forma permanente en base de datos y storage.',
        keepBusiness: 'Mantener mi plan Business',
        confirmCancel: 'Cancelar y degradar',
        upgradeHint: 'Desbloquea tarjetas, productos y funciones avanzadas ilimitadas.',
        paymentHistory: 'Historial de Pagos',
        date: 'Fecha',
        description: 'Descripcion',
        amount: 'Monto',
        statusLabel: 'Estado',
        noPayments: 'Aun no hay transacciones.',
        saveChanges: 'Guardar Cambios',
    },
}));

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

const billingForm = useForm({});
const billingSyncForm = useForm({
    session_id: '',
});
const cancelForm = useForm({});
const showCancelModal = ref(false);

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

const goBusiness = () => {
    billingForm.post('/billing/checkout', {
        preserveScroll: true,
    });
};

const openCancelModal = () => {
    showCancelModal.value = true;
};

const closeCancelModal = () => {
    if (!cancelForm.processing) {
        showCancelModal.value = false;
    }
};

const confirmCancelSubscription = () => {
    cancelForm.post('/billing/cancel-subscription', {
        preserveScroll: true,
        onSuccess: () => {
            showCancelModal.value = false;
        },
    });
};

const formatDate = (value) => {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleDateString();
};

const formatMoney = (amountCents, currency = 'usd') => {
    const normalized = (currency || 'usd').toUpperCase();
    const amount = Number(amountCents || 0) / 100;
    return new Intl.NumberFormat(undefined, {
        style: 'currency',
        currency: normalized,
    }).format(amount);
};

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const billing = params.get('billing');
    const sessionId = params.get('session_id');

    if (billing === 'success' && sessionId) {
        billingSyncForm.session_id = sessionId;
        billingSyncForm.post('/billing/sync-session', {
            preserveScroll: true,
            onSuccess: () => {
                const cleanUrl = `${window.location.origin}${window.location.pathname}`;
                window.history.replaceState({}, '', cleanUrl);
            },
        });
    }
});
</script>

<style scoped>
.table-scroll::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.table-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.table-scroll::-webkit-scrollbar-thumb {
    background: rgba(15, 23, 42, 0.18);
    border-radius: 9999px;
}

.table-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgba(15, 23, 42, 0.18) transparent;
}
</style>
