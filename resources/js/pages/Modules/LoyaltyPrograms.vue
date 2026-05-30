<template>
    <Head title="Loyalty Programs" />

    <div class="space-y-6">
        <section class="rounded-2xl border border-[#264318] bg-[#111111] p-5 text-white shadow-[0_22px_45px_rgba(8,12,8,0.42)]">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#c7f4b2]">Loyalty Hub</p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight">{{ copy.title }}</h1>
                    <p class="mt-1 text-sm text-white/75">{{ copy.subtitle }}</p>
                </div>
                <Button type="button" @click="openCreateModal">
                    <span class="inline-flex items-center gap-2">
                        <PlusIcon class="h-4 w-4" />
                        {{ copy.newProgram }}
                    </span>
                </Button>
            </div>
        </section>

        <Card>
            <div class="day-agenda-scroll max-h-[380px] overflow-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2">{{ copy.name }}</th>
                            <th class="px-3 py-2">{{ copy.required }}</th>
                            <th class="px-3 py-2">{{ copy.reward }}</th>
                            <th class="px-3 py-2">{{ copy.status }}</th>
                            <th class="px-3 py-2">{{ copy.enrolled }}</th>
                            <th class="px-3 py-2">{{ copy.completed }}</th>
                            <th class="px-3 py-2 text-right">{{ copy.actions }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="program in programs"
                            :key="program.id"
                            class="cursor-pointer"
                            :class="selectedProgramId === program.id ? 'bg-[#6DBE45]/10' : ''"
                            @click="selectProgram(program.id)"
                        >
                            <td class="px-3 py-2 text-sm font-semibold text-slate-800">{{ program.name }}</td>
                            <td class="px-3 py-2 text-sm text-slate-600">{{ program.stamps_required }}</td>
                            <td class="px-3 py-2 text-sm text-slate-600">{{ program.reward }}</td>
                            <td class="px-3 py-2 text-sm text-slate-600">{{ program.status }}</td>
                            <td class="px-3 py-2 text-sm text-slate-600">{{ program.cards_count }}</td>
                            <td class="px-3 py-2 text-sm text-slate-600">{{ program.completed_cards_count }}</td>
                            <td class="px-3 py-2">
                                <div class="flex justify-end gap-2">
                                    <Button type="button" variant="secondary" @click.stop="openEditModal(program)">
                                        <span class="inline-flex items-center gap-1.5">
                                            <PencilSquareIcon class="h-4 w-4" />
                                            {{ copy.edit }}
                                        </span>
                                    </Button>
                                    <Button type="button" variant="danger" @click.stop="openDeleteModal(program)">
                                        <span class="inline-flex items-center gap-1.5">
                                            <TrashIcon class="h-4 w-4" />
                                            {{ copy.delete }}
                                        </span>
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!programs.length">
                            <td colspan="7" class="px-4 py-6 text-center text-slate-500">{{ copy.emptyPrograms }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <div class="grid gap-4 xl:grid-cols-12">
            <Card class="xl:col-span-6">
                <h2 class="mb-3 text-base font-semibold text-slate-900">{{ copy.enrolledCards }}</h2>
                <div class="day-agenda-scroll max-h-[340px] overflow-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-2">{{ copy.customer }}</th>
                                <th class="px-3 py-2">{{ copy.progress }}</th>
                                <th class="px-3 py-2">{{ copy.status }}</th>
                                <th class="px-3 py-2">{{ copy.rewardReady }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="card in enrolledCards" :key="card.id">
                                <td class="px-3 py-2 text-sm text-slate-700">{{ card.customer_name }}<br><span class="text-xs text-slate-500">{{ card.customer_email }}</span></td>
                                <td class="px-3 py-2 text-sm text-slate-600">{{ card.stamps_current }} / {{ card.stamps_required }}</td>
                                <td class="px-3 py-2 text-sm text-slate-600">{{ card.status }}</td>
                                <td class="px-3 py-2 text-sm text-slate-600">{{ card.status === 'completed' ? copy.yes : copy.no }}</td>
                            </tr>
                            <tr v-if="!enrolledCards.length">
                                <td colspan="4" class="px-4 py-5 text-center text-slate-500">{{ copy.emptyCards }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>

            <Card class="xl:col-span-6">
                <h2 class="mb-3 text-base font-semibold text-slate-900">{{ copy.history }}</h2>
                <div class="day-agenda-scroll max-h-[340px] overflow-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-2">{{ copy.action }}</th>
                                <th class="px-3 py-2">{{ copy.delta }}</th>
                                <th class="px-3 py-2">{{ copy.when }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="tx in transactions" :key="tx.id">
                                <td class="px-3 py-2 text-sm text-slate-700">{{ tx.action }}</td>
                                <td class="px-3 py-2 text-sm text-slate-600">{{ tx.stamp_delta }}</td>
                                <td class="px-3 py-2 text-sm text-slate-600">{{ formatDate(tx.created_at) }}</td>
                            </tr>
                            <tr v-if="!transactions.length">
                                <td colspan="3" class="px-4 py-5 text-center text-slate-500">{{ copy.emptyHistory }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
        </div>
    </div>

    <Modal :show="showCreateModal" :title="copy.newProgram" max-width-class="max-w-2xl" @close="closeCreateModal">
        <ProgramForm :form="createForm" :status-options="statusOptions" :copy="copy" @submit="createProgram" @cancel="closeCreateModal" />
    </Modal>

    <Modal :show="showEditModal" :title="copy.editProgram" max-width-class="max-w-2xl" @close="closeEditModal">
        <ProgramForm :form="editForm" :status-options="statusOptions" :copy="copy" @submit="updateProgram" @cancel="closeEditModal" />
    </Modal>

    <Modal :show="showDeleteModal" :title="copy.deleteProgram" max-width-class="max-w-xl" @close="closeDeleteModal">
        <div class="space-y-5">
            <p class="text-sm text-slate-600">{{ copy.confirmDelete }}</p>
            <div class="flex justify-end gap-2">
                <Button type="button" variant="secondary" @click="closeDeleteModal">
                    <span class="inline-flex items-center gap-2"><XMarkIcon class="h-4 w-4" />{{ copy.cancel }}</span>
                </Button>
                <Button type="button" variant="danger" :disabled="deleteForm.processing" @click="deleteProgram">
                    <span class="inline-flex items-center gap-2"><TrashIcon class="h-4 w-4" />{{ copy.delete }}</span>
                </Button>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Modal from '@/components/ui/Modal.vue';
import { useLocale } from '@/composables/useLocale';
import { CheckIcon, PencilSquareIcon, PlusIcon, TrashIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, defineComponent, h, ref } from 'vue';

const props = defineProps({
    programs: { type: Array, default: () => [] },
    selectedProgramId: { type: String, default: null },
    enrolledCards: { type: Array, default: () => [] },
    transactions: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
});

const { t } = useLocale();
const copy = computed(() => t({
    en: {
        title: 'Loyalty Programs',
        subtitle: 'Create and manage loyalty cards, rewards and visit history.',
        newProgram: 'New Program',
        editProgram: 'Edit Program',
        deleteProgram: 'Delete Program',
        name: 'Name',
        description: 'Description',
        required: 'Stamps Required',
        reward: 'Reward',
        startDate: 'Start Date',
        expiresAt: 'Expiration Date',
        status: 'Status',
        actions: 'Actions',
        enrolled: 'Enrolled',
        completed: 'Completed',
        enrolledCards: 'Enrolled Customers',
        customer: 'Customer',
        progress: 'Progress',
        rewardReady: 'Reward Ready',
        history: 'Transactions',
        action: 'Action',
        delta: 'Delta',
        when: 'When',
        yes: 'Yes',
        no: 'No',
        save: 'Save',
        cancel: 'Cancel',
        edit: 'Edit',
        delete: 'Delete',
        confirmDelete: 'Are you sure you want to delete this loyalty program?',
        emptyPrograms: 'No loyalty programs yet.',
        emptyCards: 'No enrolled cards yet.',
        emptyHistory: 'No transaction history yet.',
    },
    es: {
        title: 'Programas de Fidelidad',
        subtitle: 'Crea y administra tarjetas de fidelidad, recompensas e historial.',
        newProgram: 'Nuevo Programa',
        editProgram: 'Editar Programa',
        deleteProgram: 'Eliminar Programa',
        name: 'Nombre',
        description: 'Descripcion',
        required: 'Stamps Requeridos',
        reward: 'Recompensa',
        startDate: 'Fecha Inicio',
        expiresAt: 'Fecha Expiracion',
        status: 'Estado',
        actions: 'Acciones',
        enrolled: 'Inscritos',
        completed: 'Completadas',
        enrolledCards: 'Clientes Inscritos',
        customer: 'Cliente',
        progress: 'Progreso',
        rewardReady: 'Recompensa Disponible',
        history: 'Transacciones',
        action: 'Accion',
        delta: 'Delta',
        when: 'Fecha',
        yes: 'Si',
        no: 'No',
        save: 'Guardar',
        cancel: 'Cancelar',
        edit: 'Editar',
        delete: 'Eliminar',
        confirmDelete: 'Seguro que deseas eliminar este programa de fidelidad?',
        emptyPrograms: 'No hay programas de fidelidad.',
        emptyCards: 'No hay tarjetas inscritas.',
        emptyHistory: 'No hay historial de transacciones.',
    },
}));

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedId = ref('');

const defaults = () => ({
    name: '',
    description: '',
    stamps_required: 10,
    reward: '',
    start_date: '',
    expires_at: '',
    status: 'draft',
});

const createForm = useForm(defaults());
const editForm = useForm(defaults());
const deleteForm = useForm({});

const openCreateModal = () => { createForm.reset(); showCreateModal.value = true; };
const closeCreateModal = () => { showCreateModal.value = false; };
const createProgram = () => createForm.post('/loyalty-programs', { preserveScroll: true, onSuccess: closeCreateModal });

const openEditModal = (item) => {
    selectedId.value = item.id;
    Object.assign(editForm, {
        name: item.name ?? '',
        description: item.description ?? '',
        stamps_required: item.stamps_required ?? 10,
        reward: item.reward ?? '',
        start_date: item.start_date ?? '',
        expires_at: item.expires_at ?? '',
        status: item.status ?? 'draft',
    });
    showEditModal.value = true;
};
const closeEditModal = () => { showEditModal.value = false; };
const updateProgram = () => editForm.put(`/loyalty-programs/${selectedId.value}`, { preserveScroll: true, onSuccess: closeEditModal });

const openDeleteModal = (item) => { selectedId.value = item.id; showDeleteModal.value = true; };
const closeDeleteModal = () => { showDeleteModal.value = false; };
const deleteProgram = () => deleteForm.delete(`/loyalty-programs/${selectedId.value}`, { preserveScroll: true, onSuccess: closeDeleteModal });

const selectProgram = (id) => router.get('/loyalty-programs', { program_id: id }, { preserveState: true, preserveScroll: true, replace: true });

const formatDate = (value) => (value ? new Date(value).toLocaleString() : '-');

const ProgramForm = defineComponent({
    props: { form: Object, statusOptions: Array, copy: Object },
    emits: ['submit', 'cancel'],
    setup(props, { emit }) {
        return () => h('form', {
            class: 'grid gap-4 sm:grid-cols-2',
            onSubmit: (e) => { e.preventDefault(); emit('submit'); },
        }, [
            h('div', {}, [h('label', { class: 'mb-1 block text-sm font-medium text-slate-700' }, props.copy.name), h('input', { class: 'w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm', value: props.form.name, onInput: (e) => props.form.name = e.target.value, required: true })]),
            h('div', {}, [h('label', { class: 'mb-1 block text-sm font-medium text-slate-700' }, props.copy.required), h('input', { type: 'number', min: 1, max: 200, class: 'w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm', value: props.form.stamps_required, onInput: (e) => props.form.stamps_required = Number(e.target.value), required: true })]),
            h('div', { class: 'sm:col-span-2' }, [h('label', { class: 'mb-1 block text-sm font-medium text-slate-700' }, props.copy.description), h('input', { maxlength: 255, class: 'w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm', value: props.form.description, onInput: (e) => props.form.description = e.target.value })]),
            h('div', { class: 'sm:col-span-2' }, [h('label', { class: 'mb-1 block text-sm font-medium text-slate-700' }, props.copy.reward), h('input', { maxlength: 255, class: 'w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm', value: props.form.reward, onInput: (e) => props.form.reward = e.target.value, required: true })]),
            h('div', {}, [h('label', { class: 'mb-1 block text-sm font-medium text-slate-700' }, props.copy.startDate), h('input', { type: 'date', class: 'w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm', value: props.form.start_date, onInput: (e) => props.form.start_date = e.target.value })]),
            h('div', {}, [h('label', { class: 'mb-1 block text-sm font-medium text-slate-700' }, props.copy.expiresAt), h('input', { type: 'date', class: 'w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm', value: props.form.expires_at, onInput: (e) => props.form.expires_at = e.target.value })]),
            h('div', { class: 'sm:col-span-2' }, [h('label', { class: 'mb-1 block text-sm font-medium text-slate-700' }, props.copy.status), h('select', { class: 'w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm', value: props.form.status, onChange: (e) => props.form.status = e.target.value }, props.statusOptions.map((status) => h('option', { value: status }, status)))]),
            h('div', { class: 'sm:col-span-2 flex justify-end gap-2' }, [
                h(Button, { type: 'button', variant: 'secondary', onClick: () => emit('cancel') }, () => [h('span', { class: 'inline-flex items-center gap-2' }, [h(XMarkIcon, { class: 'h-4 w-4' }), props.copy.cancel])]),
                h(Button, { type: 'submit', disabled: props.form.processing }, () => [h('span', { class: 'inline-flex items-center gap-2' }, [h(CheckIcon, { class: 'h-4 w-4' }), props.copy.save])]),
            ]),
        ]);
    },
});
</script>

<style scoped>
.day-agenda-scroll::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.day-agenda-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.day-agenda-scroll::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.25);
    border-radius: 999px;
}
.day-agenda-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgba(148, 163, 184, 0.35) transparent;
}
</style>

