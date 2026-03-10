<template>
    <Head title="Appointments" />

    <div class="space-y-6">
        <section class="rounded-2xl bg-[linear-gradient(130deg,#111111_0%,#173010_58%,#6DBE45_115%)] p-5 text-white shadow-[0_22px_45px_rgba(8,12,8,0.42)]">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#c7f4b2]">Appointments Hub</p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight">{{ copy.title }}</h1>
                    <p class="mt-1 text-sm text-white/75">{{ copy.subtitle }}</p>
                </div>
                <Button type="button" @click="openCreateModal">
                    <span class="inline-flex items-center gap-2">
                        <PlusIcon class="h-4 w-4" />
                        {{ copy.newAppointment }}
                    </span>
                </Button>
            </div>
        </section>

        <div class="grid gap-4 xl:grid-cols-12">
            <Card class="xl:col-span-5">
                <div class="mb-3 flex items-center justify-between">
                    <Button type="button" variant="secondary" @click="moveMonth(-1)">
                        <span class="inline-flex items-center gap-1">
                            <ChevronLeftIcon class="h-4 w-4" />
                            {{ copy.prev }}
                        </span>
                    </Button>
                    <p class="text-sm font-semibold text-slate-800">{{ monthLabel }}</p>
                    <Button type="button" variant="secondary" @click="moveMonth(1)">
                        <span class="inline-flex items-center gap-1">
                            {{ copy.next }}
                            <ChevronRightIcon class="h-4 w-4" />
                        </span>
                    </Button>
                </div>

                <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    <div v-for="day in weekDays" :key="day">{{ day }}</div>
                </div>

                <div class="mt-2 grid grid-cols-7 gap-1">
                    <button
                        v-for="day in calendarDays"
                        :key="day.key"
                        type="button"
                        class="relative min-h-12 rounded-lg border text-sm transition"
                        :class="dayClasses(day)"
                        @click="selectedDate = day.date"
                    >
                        <span>{{ day.dayNumber }}</span>
                        <span
                            v-if="day.count > 0"
                            class="absolute bottom-1 left-1/2 -translate-x-1/2 rounded-full bg-[#6DBE45] px-1.5 text-[10px] font-semibold text-[#111111]"
                        >
                            {{ day.count }}
                        </span>
                    </button>
                </div>
            </Card>

            <Card class="xl:col-span-7">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-slate-900">{{ copy.dayAgenda }} - {{ selectedDateLabel }}</h2>
                    <Badge tone="positive">{{ selectedDayAppointments.length }} {{ copy.items }}</Badge>
                </div>

                <div v-if="selectedDayAppointments.length" class="day-agenda-scroll max-h-[330px] space-y-2 overflow-y-auto pr-1">
                    <div
                        v-for="item in selectedDayAppointments"
                        :key="item.id"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2"
                    >
                        <div class="space-y-1.5">
                            <div class="flex items-start justify-between gap-2">
                                <p class="min-w-0 truncate pt-1 text-sm font-semibold text-slate-800">{{ item.full_name }}</p>
                                <div class="flex shrink-0 items-center gap-1">
                                    <p class="inline-flex rounded-md bg-[#d8f3c9] px-2 py-0.5 text-xs font-semibold text-[#1f4d12] ring-1 ring-[#9fd67f]">
                                        {{ item.start_time && item.end_time ? `${item.start_time} - ${item.end_time}` : formatRange(item.starts_at, item.ends_at) }}
                                    </p>
                                    <div class="flex items-center gap-1 rounded-lg bg-slate-200/70 p-1">
                                        <button
                                            type="button"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-[#6DBE45] hover:text-[#111111]"
                                            :title="copy.edit"
                                            @click="openEditModal(item)"
                                        >
                                            <PencilSquareIcon class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-rose-200 bg-rose-50 text-rose-600 shadow-sm transition hover:border-rose-300 hover:bg-rose-100"
                                            :title="copy.delete"
                                            @click="openDeleteModal(item)"
                                        >
                                            <TrashIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-xs text-slate-600">{{ item.card_name }} · {{ item.interest || copy.noService }}</p>
                                <p class="truncate text-xs text-slate-500">{{ item.phone || '-' }} · {{ item.email || '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-slate-500">{{ copy.noAppointmentsDay }}</p>
            </Card>
        </div>

        <Card>
            <div class="mb-4 space-y-3">
                <h2 class="text-base font-semibold text-slate-900">{{ copy.allAppointments }}</h2>
                <div class="grid gap-3 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ copy.search }}</label>
                        <div class="relative">
                            <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                            <input v-model="filtersState.search" type="text" class="w-full rounded-lg border border-slate-200 py-2.5 pl-9 pr-3 text-sm" :placeholder="copy.searchPlaceholder">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ copy.card }}</label>
                        <select v-model="filtersState.card_id" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                            <option value="">{{ copy.allCards }}</option>
                            <option v-for="card in cards" :key="card.id" :value="card.id">{{ card.name }} (@{{ card.username }})</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ copy.month }}</label>
                        <input v-model="filtersState.month" type="month" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                    </div>
                </div>
            </div>
            <div class="day-agenda-scroll max-h-[420px] overflow-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2">{{ copy.client }}</th>
                            <th class="px-3 py-2">{{ copy.card }}</th>
                            <th class="px-3 py-2">{{ copy.service }}</th>
                            <th class="px-3 py-2">{{ copy.when }}</th>
                            <th class="px-3 py-2">{{ copy.status }}</th>
                            <th class="px-3 py-2">{{ copy.contact }}</th>
                            <th class="px-3 py-2">{{ copy.source }}</th>
                            <th class="px-3 py-2 text-right">{{ copy.actions }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="item in appointments" :key="item.id">
                            <td class="px-3 py-2 text-sm font-semibold text-slate-800">{{ item.full_name }}</td>
                            <td class="px-3 py-2 text-sm text-slate-600">{{ item.card_name }}</td>
                            <td class="px-3 py-2 text-sm text-slate-600">{{ item.interest || item.product_name || '-' }}</td>
                            <td class="px-3 py-2 text-sm text-slate-600">{{ item.starts_at_label || formatDateTime(item.starts_at) }}</td>
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <Badge :tone="statusTone(item.status)">{{ statusLabel(item.status) }}</Badge>
                                    <select
                                        :value="item.status"
                                        class="rounded-lg border border-slate-200 px-2 py-1 text-xs"
                                        @change="updateStatus(item.id, $event.target.value)"
                                    >
                                        <option v-for="status in normalizedStatusOptions" :key="status" :value="status">{{ statusLabel(status) }}</option>
                                    </select>
                                </div>
                            </td>
                            <td class="px-3 py-2 text-sm text-slate-600">{{ item.phone || '-' }} / {{ item.email || '-' }}</td>
                            <td class="px-3 py-2 text-sm text-slate-600">{{ item.source }}</td>
                            <td class="px-3 py-2">
                                <div class="flex justify-end gap-2">
                                    <Button type="button" variant="secondary" @click="openEditModal(item)">
                                        <span class="inline-flex items-center gap-1.5">
                                            <PencilSquareIcon class="h-4 w-4" />
                                            {{ copy.edit }}
                                        </span>
                                    </Button>
                                    <Button type="button" variant="danger" @click="openDeleteModal(item)">
                                        <span class="inline-flex items-center gap-1.5">
                                            <TrashIcon class="h-4 w-4" />
                                            {{ copy.delete }}
                                        </span>
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </div>

    <Modal :show="showCreateModal" :title="copy.newAppointment" max-width-class="max-w-2xl" @close="closeCreateModal">
        <form class="grid gap-4 sm:grid-cols-2" @submit.prevent="createAppointment">
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.card }}</label>
                <select v-model="createForm.card_id" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" required>
                    <option value="" disabled>{{ copy.selectCard }}</option>
                    <option v-for="card in cards" :key="card.id" :value="card.id">{{ card.name }} (@{{ card.username }})</option>
                </select>
                <p v-if="createForm.errors.card_id" class="mt-1 text-xs text-rose-600">{{ createForm.errors.card_id }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.clientName }}</label>
                <input v-model="createForm.full_name" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" required>
                <p v-if="createForm.errors.full_name" class="mt-1 text-xs text-rose-600">{{ createForm.errors.full_name }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.phone }}</label>
                <input v-model="createForm.phone" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.email }}</label>
                <input v-model="createForm.email" type="email" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                <p v-if="createForm.errors.email" class="mt-1 text-xs text-rose-600">{{ createForm.errors.email }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.service }}</label>
                <select v-model="createForm.product_id" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                    <option value="">{{ copy.noService }}</option>
                    <option v-for="service in filteredServices" :key="service.id" :value="service.id">{{ service.name }}</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.interest }}</label>
                <input v-model="createForm.interest" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.date }}</label>
                <input v-model="createForm.appointment_date" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" required>
                <p v-if="createForm.errors.appointment_date" class="mt-1 text-xs text-rose-600">{{ createForm.errors.appointment_date }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.time }}</label>
                <input v-model="createForm.appointment_time" type="time" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" required>
                <p v-if="createForm.errors.appointment_time" class="mt-1 text-xs text-rose-600">{{ createForm.errors.appointment_time }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.duration }}</label>
                <input v-model.number="createForm.duration_minutes" type="number" min="5" max="600" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.status }}</label>
                <select v-model="createForm.status" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                    <option v-for="status in normalizedStatusOptions" :key="status" :value="status">{{ statusLabel(status) }}</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.notes }}</label>
                <textarea v-model="createForm.notes" rows="3" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></textarea>
            </div>
            <div class="sm:col-span-2 flex justify-end gap-2">
                <Button type="button" variant="secondary" @click="closeCreateModal">
                    <span class="inline-flex items-center gap-2">
                        <XMarkIcon class="h-4 w-4" />
                        {{ copy.cancel }}
                    </span>
                </Button>
                <Button type="submit" :disabled="createForm.processing">
                    <span class="inline-flex items-center gap-2">
                        <CheckIcon class="h-4 w-4" />
                        {{ copy.save }}
                    </span>
                </Button>
            </div>
        </form>
    </Modal>

    <Modal :show="showEditModal" :title="copy.edit" max-width-class="max-w-2xl" @close="closeEditModal">
        <form class="grid gap-4 sm:grid-cols-2" @submit.prevent="submitEdit">
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.card }}</label>
                <select v-model="editForm.card_id" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" required>
                    <option value="" disabled>{{ copy.selectCard }}</option>
                    <option v-for="card in cards" :key="card.id" :value="card.id">{{ card.name }} (@{{ card.username }})</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.clientName }}</label>
                <input v-model="editForm.full_name" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" required>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.phone }}</label>
                <input v-model="editForm.phone" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.email }}</label>
                <input v-model="editForm.email" type="email" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.service }}</label>
                <select v-model="editForm.product_id" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                    <option value="">{{ copy.noService }}</option>
                    <option v-for="service in filteredServices" :key="service.id" :value="service.id">{{ service.name }}</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.interest }}</label>
                <input v-model="editForm.interest" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.date }}</label>
                <input v-model="editForm.appointment_date" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" required>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.time }}</label>
                <input v-model="editForm.appointment_time" type="time" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" required>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.duration }}</label>
                <input v-model.number="editForm.duration_minutes" type="number" min="5" max="600" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.status }}</label>
                <select v-model="editForm.status" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                    <option v-for="status in normalizedStatusOptions" :key="status" :value="status">{{ statusLabel(status) }}</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.notes }}</label>
                <textarea v-model="editForm.notes" rows="3" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></textarea>
            </div>
            <div class="sm:col-span-2 flex justify-end gap-2">
                <Button type="button" variant="secondary" @click="closeEditModal">
                    <span class="inline-flex items-center gap-2">
                        <XMarkIcon class="h-4 w-4" />
                        {{ copy.cancel }}
                    </span>
                </Button>
                <Button type="submit" :disabled="editForm.processing">
                    <span class="inline-flex items-center gap-2">
                        <CheckIcon class="h-4 w-4" />
                        {{ copy.save }}
                    </span>
                </Button>
            </div>
        </form>
    </Modal>

    <Modal :show="showDeleteModal" :title="copy.delete" @close="closeDeleteModal">
        <div class="space-y-4">
            <p class="text-sm text-slate-600">{{ copy.deleteConfirm }}</p>
            <div class="flex justify-end gap-2">
                <Button type="button" variant="secondary" @click="closeDeleteModal">
                    <span class="inline-flex items-center gap-2">
                        <XMarkIcon class="h-4 w-4" />
                        {{ copy.cancel }}
                    </span>
                </Button>
                <Button type="button" variant="danger" :disabled="deleteForm.processing" @click="confirmDelete">
                    <span class="inline-flex items-center gap-2">
                        <TrashIcon class="h-4 w-4" />
                        {{ copy.delete }}
                    </span>
                </Button>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import Badge from '@/components/ui/Badge.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Modal from '@/components/ui/Modal.vue';
import { useLocale } from '@/composables/useLocale';
import {
    CheckIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    MagnifyingGlassIcon,
    PencilSquareIcon,
    PlusIcon,
    TrashIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    appointments: { type: Array, default: () => [] },
    cards: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({ card_id: null, search: '', month: '' }) },
    statusOptions: { type: Array, default: () => [] },
});

const { t } = useLocale();
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedDate = ref(new Date().toISOString().slice(0, 10));
const editingId = ref(null);
const deletingId = ref(null);

const filtersState = ref({
    card_id: props.filters.card_id || '',
    search: props.filters.search || '',
    month: props.filters.month || new Date().toISOString().slice(0, 7),
});

const copy = computed(() => t({
    en: {
        title: 'Appointments',
        subtitle: 'Manage leads and appointments by card in one calendar.',
        newAppointment: 'New Appointment',
        search: 'Search',
        searchPlaceholder: 'Search by client, phone, email or interest',
        card: 'Card',
        allCards: 'All cards',
        month: 'Month',
        prev: 'Prev',
        next: 'Next',
        dayAgenda: 'Day Agenda',
        items: 'items',
        noAppointmentsDay: 'No appointments for this day.',
        allAppointments: 'All Appointments',
        client: 'Client',
        service: 'Service',
        when: 'When',
        contact: 'Contact',
        source: 'Source',
        selectCard: 'Select a card',
        clientName: 'Full name',
        phone: 'Phone',
        email: 'Email',
        interest: 'Interest',
        date: 'Date',
        time: 'Time',
        duration: 'Duration (minutes)',
        status: 'Status',
        scheduled: 'Scheduled',
        confirmed: 'Confirmed',
        attended: 'Attended',
        noShow: 'No-show',
        cancelled: 'Cancelled',
        rescheduled: 'Rescheduled',
        notes: 'Notes',
        save: 'Save',
        cancel: 'Cancel',
        noService: 'No service',
        edit: 'Edit',
        delete: 'Delete',
        actions: 'Actions',
        deleteConfirm: 'This action cannot be undone. Do you want to delete this appointment?',
    },
    es: {
        title: 'Citas',
        subtitle: 'Gestiona leads y citas por tarjeta en un solo calendario.',
        newAppointment: 'Nueva Cita',
        search: 'Buscar',
        searchPlaceholder: 'Buscar por cliente, telefono, correo o interes',
        card: 'Tarjeta',
        allCards: 'Todas las tarjetas',
        month: 'Mes',
        prev: 'Anterior',
        next: 'Siguiente',
        dayAgenda: 'Agenda del dia',
        items: 'elementos',
        noAppointmentsDay: 'No hay citas para este dia.',
        allAppointments: 'Todas las Citas',
        client: 'Cliente',
        service: 'Servicio',
        when: 'Cuando',
        contact: 'Contacto',
        source: 'Origen',
        selectCard: 'Selecciona una tarjeta',
        clientName: 'Nombre completo',
        phone: 'Telefono',
        email: 'Correo',
        interest: 'Interes',
        date: 'Fecha',
        time: 'Hora',
        duration: 'Duracion (minutos)',
        status: 'Estado',
        scheduled: 'Agendada',
        confirmed: 'Confirmada',
        attended: 'Asistio',
        noShow: 'No asistio',
        cancelled: 'Cancelada',
        rescheduled: 'Reagendada',
        notes: 'Notas',
        save: 'Guardar',
        cancel: 'Cancelar',
        noService: 'Sin servicio',
        edit: 'Editar',
        delete: 'Eliminar',
        actions: 'Acciones',
        deleteConfirm: 'Esta accion no se puede restaurar. Deseas eliminar esta cita?',
    },
}));

const weekDays = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];

const createForm = useForm({
    card_id: '',
    product_id: '',
    full_name: '',
    phone: '',
    email: '',
    interest: '',
    appointment_date: new Date().toISOString().slice(0, 10),
    appointment_time: '09:00',
    duration_minutes: 30,
    notes: '',
    status: 'scheduled',
});

const statusUpdateForm = useForm({
    status: '',
});

const editForm = useForm({
    card_id: '',
    product_id: '',
    full_name: '',
    phone: '',
    email: '',
    interest: '',
    appointment_date: new Date().toISOString().slice(0, 10),
    appointment_time: '09:00',
    duration_minutes: 30,
    notes: '',
    status: 'scheduled',
});

const deleteForm = useForm({});

const openCreateModal = () => {
    createForm.clearErrors();
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    createForm.reset();
};

const createAppointment = () => {
    createForm.post('/appointments', {
        preserveScroll: true,
        onSuccess: () => closeCreateModal(),
    });
};

const fillEditForm = (item) => {
    const start = item.starts_at ? new Date(item.starts_at) : new Date();
    editForm.card_id = item.card_id || '';
    editForm.product_id = item.product_id || '';
    editForm.full_name = item.full_name || '';
    editForm.phone = item.phone || '';
    editForm.email = item.email || '';
    editForm.interest = item.interest || '';
    editForm.appointment_date = item.date_key || start.toISOString().slice(0, 10);
    editForm.appointment_time = item.start_time || start.toTimeString().slice(0, 5);
    editForm.duration_minutes = item.duration_minutes || 30;
    editForm.notes = item.notes || '';
    editForm.status = item.status || 'scheduled';
};

const openEditModal = (item) => {
    editingId.value = item.id;
    editForm.clearErrors();
    fillEditForm(item);
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingId.value = null;
    editForm.reset();
};

const submitEdit = () => {
    if (!editingId.value) return;
    editForm.put(`/appointments/${editingId.value}`, {
        preserveScroll: true,
        onSuccess: () => closeEditModal(),
    });
};

const openDeleteModal = (item) => {
    deletingId.value = item.id;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deletingId.value = null;
};

const confirmDelete = () => {
    if (!deletingId.value) return;
    deleteForm.delete(`/appointments/${deletingId.value}`, {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
    });
};

const filteredServices = computed(() => props.services);
const normalizedStatusOptions = computed(() => (
    props.statusOptions?.length
        ? props.statusOptions
        : ['scheduled', 'confirmed', 'attended', 'no_show', 'cancelled', 'rescheduled']
));

const statusLabel = (status) => {
    const labels = {
        scheduled: copy.value.scheduled,
        confirmed: copy.value.confirmed,
        attended: copy.value.attended,
        completed: copy.value.attended,
        no_show: copy.value.noShow,
        cancelled: copy.value.cancelled,
        rescheduled: copy.value.rescheduled,
    };
    return labels[status] || status;
};

const statusTone = (status) => {
    if (status === 'attended' || status === 'completed') return 'positive';
    if (status === 'cancelled' || status === 'no_show') return 'negative';
    return 'neutral';
};

const updateStatus = (appointmentId, status) => {
    if (!appointmentId || !status) return;
    statusUpdateForm.status = status;
    statusUpdateForm.put(`/appointments/${appointmentId}/status`, {
        preserveScroll: true,
        preserveState: true,
    });
};

let filtersTimer = null;
watch(filtersState, () => {
    if (filtersTimer) clearTimeout(filtersTimer);
    filtersTimer = setTimeout(() => {
        router.get('/appointments', {
            card_id: filtersState.value.card_id || undefined,
            search: filtersState.value.search || undefined,
            month: filtersState.value.month || undefined,
        }, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    }, 320);
}, { deep: true });

const appointmentMapByDate = computed(() => {
    const map = new Map();
    props.appointments.forEach((item) => {
        const key = item.date_key || (item.starts_at || '').slice(0, 10);
        if (!key) return;
        map.set(key, (map.get(key) || 0) + 1);
    });
    return map;
});

const calendarDays = computed(() => {
    const month = filtersState.value.month || new Date().toISOString().slice(0, 7);
    const [year, monthNumber] = month.split('-').map(Number);
    const first = new Date(year, monthNumber - 1, 1);
    const firstWeekDay = first.getDay();
    const daysInMonth = new Date(year, monthNumber, 0).getDate();
    const result = [];

    for (let i = 0; i < firstWeekDay; i += 1) {
        result.push({ key: `p-${i}`, dayNumber: '', date: '', count: 0, current: false });
    }

    for (let day = 1; day <= daysInMonth; day += 1) {
        const date = `${month}-${String(day).padStart(2, '0')}`;
        result.push({
            key: date,
            dayNumber: day,
            date,
            count: appointmentMapByDate.value.get(date) || 0,
            current: true,
        });
    }

    return result;
});

const dayClasses = (day) => {
    if (!day.current) return 'border-transparent bg-transparent';
    if (selectedDate.value === day.date) return 'border-[#6DBE45] bg-[#6DBE45]/15 text-[#111111]';
    return 'border-slate-200 bg-white text-slate-700 hover:border-[#6DBE45]';
};

const moveMonth = (direction) => {
    const [year, month] = filtersState.value.month.split('-').map(Number);
    const next = new Date(year, month - 1 + direction, 1);
    filtersState.value.month = `${next.getFullYear()}-${String(next.getMonth() + 1).padStart(2, '0')}`;
};

const selectedDayAppointments = computed(() =>
    props.appointments.filter((item) => (item.date_key || (item.starts_at || '').slice(0, 10)) === selectedDate.value)
        .sort((a, b) => new Date(a.starts_at) - new Date(b.starts_at))
);

const monthLabel = computed(() => {
    const [year, month] = (filtersState.value.month || new Date().toISOString().slice(0, 7)).split('-').map(Number);
    return new Date(year, month - 1, 1).toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
});

const selectedDateLabel = computed(() => {
    if (!selectedDate.value) return '-';
    return new Date(`${selectedDate.value}T00:00:00`).toLocaleDateString();
});

const formatDateTime = (value) => value ? new Date(value).toLocaleString() : '-';
const formatRange = (start, end) => `${new Date(start).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} - ${new Date(end).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
</script>

<style scoped>
.day-agenda-scroll::-webkit-scrollbar {
    width: 6px;
}

.day-agenda-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.day-agenda-scroll::-webkit-scrollbar-thumb {
    background: rgba(15, 23, 42, 0.15);
    border-radius: 999px;
}

.day-agenda-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(15, 23, 42, 0.24);
}

.day-agenda-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgba(15, 23, 42, 0.2) transparent;
}
</style>
