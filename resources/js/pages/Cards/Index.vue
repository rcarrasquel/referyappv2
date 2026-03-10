<template>
    <Head title="Cards" />

    <div class="space-y-5">
        <div class="mb-6 rounded-2xl bg-[linear-gradient(130deg,#111111_0%,#173010_58%,#6DBE45_115%)] p-5 text-white shadow-[0_22px_45px_rgba(8,12,8,0.42)]">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#c7f4b2]">Digital Cards Hub</p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-white">Cards</h1>
                    <p class="mt-1 text-sm text-white/75">Manage your professional digital cards.</p>
                </div>
                <Badge tone="positive">Plan: {{ plan }}</Badge>
            </div>
        </div>

        <p
            v-if="isFreeLimitReached"
            class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800"
        >
            Free plan allows only one card. Upgrade to monthly or lifetime to create more.
        </p>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <Link
                v-for="card in cards"
                :key="card.id"
                :href="`/cards/${card.id}`"
                class="block rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <p class="text-lg font-semibold text-slate-900">{{ card.name }}</p>
                <p class="mt-1 text-sm text-slate-500">@{{ card.username }}</p>
                <p class="mt-3 line-clamp-2 text-sm text-slate-600">{{ card.description || 'No description' }}</p>
            </Link>

            <button
                type="button"
                class="flex min-h-44 items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-white text-slate-500 transition hover:border-[#6DBE45] hover:text-[#111111] disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="!canCreate"
                @click="openModal"
            >
                <PlusIcon class="h-10 w-10" />
            </button>
        </div>
    </div>

    <Modal :show="showModal" title="Create Card" @close="closeModal">
        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Card Name</label>
                <input v-model="form.name" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 focus:ring focus:ring-[#6DBE45]" required>
                <p v-if="form.errors.name" class="mt-1 text-xs text-rose-600">{{ form.errors.name }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Username</label>
                <input v-model="form.username" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 focus:ring focus:ring-[#6DBE45]" required>
                <p v-if="form.errors.username" class="mt-1 text-xs text-rose-600">{{ form.errors.username }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Description</label>
                <input v-model="form.description" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 focus:ring focus:ring-[#6DBE45]">
                <p v-if="form.errors.description" class="mt-1 text-xs text-rose-600">{{ form.errors.description }}</p>
            </div>

            <p v-if="form.errors.limit" class="text-xs text-rose-600">{{ form.errors.limit }}</p>

            <div class="flex items-center justify-end gap-2">
                <Button type="button" variant="secondary" @click="closeModal">
                    <span class="inline-flex items-center gap-2">
                        <XMarkIcon class="h-4 w-4" />
                        Cancel
                    </span>
                </Button>
                <Button type="submit" :disabled="form.processing">
                    <span class="inline-flex items-center gap-2">
                        <CheckIcon class="h-4 w-4" />
                        Create
                    </span>
                </Button>
            </div>
        </form>
    </Modal>
</template>

<script setup>
import Badge from '@/components/ui/Badge.vue';
import Button from '@/components/ui/Button.vue';
import Modal from '@/components/ui/Modal.vue';
import { CheckIcon, PlusIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    cards: {
        type: Array,
        default: () => [],
    },
    canCreate: {
        type: Boolean,
        default: false,
    },
    plan: {
        type: String,
        default: 'free',
    },
});

const showModal = ref(false);

const form = useForm({
    name: '',
    username: '',
    description: '',
});

const isFreeLimitReached = computed(() => props.plan === 'free' && props.cards.length >= 1);

const openModal = () => {
    if (!props.canCreate) {
        return;
    }

    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    form.post('/cards', {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
    });
};
</script>
