<template>
    <Head title="Clients" />

    <div class="space-y-6">
        <section class="rounded-2xl bg-[#111111] border border-[#264318] p-5 text-white shadow-[0_22px_45px_rgba(8,12,8,0.42)]">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#c7f4b2]">Clients Hub</p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight">{{ copy.title }}</h1>
                    <p class="mt-1 text-sm text-white/75">{{ copy.subtitle }}</p>
                </div>
                <Button type="button" @click="openCreateModal">
                    <span class="inline-flex items-center gap-2">
                        <PlusIcon class="h-4 w-4" />
                        {{ copy.newClient }}
                    </span>
                </Button>
            </div>
        </section>

        <Card>
            <div class="mb-4 grid gap-3 md:grid-cols-4">
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ copy.search }}</label>
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="copy.searchPlaceholder"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"
                        @input="onSearch"
                    >
                </div>
            </div>

            <div class="day-agenda-scroll max-h-[520px] overflow-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-2">{{ copy.name }}</th>
                            <th class="px-3 py-2">{{ copy.email }}</th>
                            <th class="px-3 py-2">{{ copy.phone }}</th>
                            <th class="px-3 py-2">{{ copy.status }}</th>
                            <th class="px-3 py-2">{{ copy.services }}</th>
                            <th class="px-3 py-2">{{ copy.cards }}</th>
                            <th class="px-3 py-2">{{ copy.appointments }}</th>
                            <th class="px-3 py-2">{{ copy.leads }}</th>
                            <th class="px-3 py-2">{{ copy.lastInteraction }}</th>
                            <th class="px-3 py-2 text-right">{{ copy.actions }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="item in clients" :key="item.identity">
                            <td class="px-3 py-2 text-sm font-semibold text-slate-800">{{ item.full_name || '-' }}</td>
                            <td class="px-3 py-2 text-sm text-slate-600">{{ item.email || '-' }}</td>
                            <td class="px-3 py-2 text-sm text-slate-600">{{ item.phone || '-' }}</td>
                            <td class="px-3 py-2 text-sm text-slate-600">{{ item.status || 'new' }}</td>
                            <td class="px-3 py-2 text-sm text-slate-600">{{ (item.service_names || []).join(', ') || '-' }}</td>
                            <td class="px-3 py-2 text-sm text-slate-600">{{ (item.cards || []).join(', ') || '-' }}</td>
                            <td class="px-3 py-2 text-sm text-slate-600">{{ item.appointments_count }}</td>
                            <td class="px-3 py-2 text-sm text-slate-600">{{ item.leads_count }}</td>
                            <td class="px-3 py-2 text-sm text-slate-600">{{ formatDate(item.last_interaction_at) }}</td>
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
                        <tr v-if="!clients.length">
                            <td colspan="10" class="px-4 py-6 text-center text-slate-500">{{ copy.empty }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </div>

    <Modal :show="showCreateModal" :title="copy.newClient" max-width-class="max-w-2xl" @close="closeCreateModal">
        <form class="grid gap-4 sm:grid-cols-2" @submit.prevent="createClient">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.firstName }}</label>
                <input v-model="createForm.first_name" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.lastName }}</label>
                <input v-model="createForm.last_name" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.email }}</label>
                <input v-model="createForm.email" type="email" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.phone }}</label>
                <input v-model="createForm.phone" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.address1 }}</label>
                <input v-model="createForm.address_1" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.address2 }}</label>
                <input v-model="createForm.address_2" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.city }}</label>
                <input v-model="createForm.city" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.country }}</label>
                <input v-model="createForm.country" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.zip }}</label>
                <input v-model="createForm.zip" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.state }}</label>
                <input v-model="createForm.state" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.status }}</label>
                <select v-model="createForm.status" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                    <option v-for="status in statusOptions" :key="`c-status-${status}`" :value="status">{{ status }}</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.services }}</label>
                <select v-model="createForm.service_ids" multiple class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm min-h-[120px]">
                    <option v-for="service in serviceOptions" :key="`c-service-${service.id}`" :value="service.id">{{ service.name }}</option>
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
        <form class="grid gap-4 sm:grid-cols-2" @submit.prevent="updateClient">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.firstName }}</label>
                <input v-model="editForm.first_name" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.lastName }}</label>
                <input v-model="editForm.last_name" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.email }}</label>
                <input v-model="editForm.email" type="email" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.phone }}</label>
                <input v-model="editForm.phone" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.address1 }}</label>
                <input v-model="editForm.address_1" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.address2 }}</label>
                <input v-model="editForm.address_2" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.city }}</label>
                <input v-model="editForm.city" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.country }}</label>
                <input v-model="editForm.country" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.zip }}</label>
                <input v-model="editForm.zip" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.state }}</label>
                <input v-model="editForm.state" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.status }}</label>
                <select v-model="editForm.status" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                    <option v-for="status in statusOptions" :key="`e-status-${status}`" :value="status">{{ status }}</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.services }}</label>
                <select v-model="editForm.service_ids" multiple class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm min-h-[120px]">
                    <option v-for="service in serviceOptions" :key="`e-service-${service.id}`" :value="service.id">{{ service.name }}</option>
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

    <Modal :show="showDeleteModal" :title="copy.delete" max-width-class="max-w-xl" @close="closeDeleteModal">
        <div class="space-y-5">
            <p class="text-sm text-slate-600">{{ copy.confirmDelete }}</p>
            <div class="flex justify-end gap-2">
                <Button type="button" variant="secondary" @click="closeDeleteModal">
                    <span class="inline-flex items-center gap-2">
                        <XMarkIcon class="h-4 w-4" />
                        {{ copy.cancel }}
                    </span>
                </Button>
                <Button type="button" variant="danger" :disabled="deleteForm.processing" @click="deleteClient">
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
import Card from '@/components/ui/Card.vue';
import Button from '@/components/ui/Button.vue';
import Modal from '@/components/ui/Modal.vue';
import { useLocale } from '@/composables/useLocale';
import { CheckIcon, PencilSquareIcon, PlusIcon, TrashIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    clients: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    serviceOptions: {
        type: Array,
        default: () => [],
    },
    statusOptions: {
        type: Array,
        default: () => [],
    },
});

const { t } = useLocale();
const copy = computed(() => t({
    en: {
        title: 'Clients',
        subtitle: 'Auto-grouped from appointments and leads by email or phone.',
        newClient: 'New Client',
        search: 'Search',
        searchPlaceholder: 'Search by name, email or phone',
        firstName: 'First Name',
        lastName: 'Last Name',
        name: 'Name',
        email: 'Email',
        phone: 'Phone',
        address1: 'Address 1',
        address2: 'Address 2',
        city: 'City',
        country: 'Country',
        zip: 'Zip',
        state: 'State',
        cards: 'Cards',
        appointments: 'Appointments',
        leads: 'Leads',
        lastInteraction: 'Last interaction',
        status: 'Status',
        services: 'Services',
        actions: 'Actions',
        interest: 'Interest',
        notes: 'Notes',
        save: 'Save',
        cancel: 'Cancel',
        edit: 'Edit',
        delete: 'Delete',
        confirmDelete: 'Are you sure you want to delete this client?',
        empty: 'No clients found yet.',
    },
    es: {
        title: 'Clientes',
        subtitle: 'Agrupados automaticamente desde citas y leads por correo o telefono.',
        newClient: 'Nuevo Cliente',
        search: 'Buscar',
        searchPlaceholder: 'Buscar por nombre, correo o telefono',
        firstName: 'Nombre',
        lastName: 'Apellido',
        name: 'Nombre',
        email: 'Correo',
        phone: 'Telefono',
        address1: 'Direccion 1',
        address2: 'Direccion 2',
        city: 'Ciudad',
        country: 'Pais',
        zip: 'Zip',
        state: 'Estado',
        cards: 'Tarjetas',
        appointments: 'Citas',
        leads: 'Leads',
        lastInteraction: 'Ultima interaccion',
        status: 'Estado',
        services: 'Servicios',
        actions: 'Acciones',
        interest: 'Interes',
        notes: 'Notas',
        save: 'Guardar',
        cancel: 'Cancelar',
        edit: 'Editar',
        delete: 'Eliminar',
        confirmDelete: 'Seguro que deseas eliminar este cliente?',
        empty: 'No hay clientes todavia.',
    },
}));

const search = ref(props.filters.search ?? '');
let timer = null;
const onSearch = () => {
    if (timer) clearTimeout(timer);
    timer = setTimeout(() => {
        router.get('/clients', { search: search.value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
    }, 250);
};

const formatDate = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleString();
};

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedClientId = ref('');

const createForm = useForm({
    first_name: '',
    last_name: '',
    full_name: '',
    email: '',
    phone: '',
    address_1: '',
    address_2: '',
    city: '',
    country: '',
    zip: '',
    state: '',
    status: 'new',
    service_ids: [],
    interest: '',
    notes: '',
});

const editForm = useForm({
    first_name: '',
    last_name: '',
    full_name: '',
    email: '',
    phone: '',
    address_1: '',
    address_2: '',
    city: '',
    country: '',
    zip: '',
    state: '',
    status: 'new',
    service_ids: [],
    interest: '',
    notes: '',
});

const deleteForm = useForm({});

const openCreateModal = () => {
    createForm.reset();
    createForm.clearErrors();
    showCreateModal.value = true;
};
const closeCreateModal = () => { showCreateModal.value = false; };

const createClient = () => {
    createForm.post('/clients', {
        preserveScroll: true,
        onSuccess: () => closeCreateModal(),
    });
};

const openEditModal = (item) => {
    selectedClientId.value = item.id;
    editForm.first_name = item.first_name || '';
    editForm.last_name = item.last_name || '';
    editForm.full_name = item.full_name || '';
    editForm.email = item.email || '';
    editForm.phone = item.phone || '';
    editForm.address_1 = item.address_1 || '';
    editForm.address_2 = item.address_2 || '';
    editForm.city = item.city || '';
    editForm.country = item.country || '';
    editForm.zip = item.zip || '';
    editForm.state = item.state || '';
    editForm.status = item.status || 'new';
    editForm.service_ids = Array.isArray(item.service_ids) ? [...item.service_ids] : [];
    editForm.interest = item.interest || '';
    editForm.notes = item.notes || '';
    editForm.clearErrors();
    showEditModal.value = true;
};
const closeEditModal = () => { showEditModal.value = false; };

const updateClient = () => {
    editForm.put(`/clients/${selectedClientId.value}`, {
        preserveScroll: true,
        onSuccess: () => closeEditModal(),
    });
};

const openDeleteModal = (item) => {
    selectedClientId.value = item.id;
    showDeleteModal.value = true;
};
const closeDeleteModal = () => { showDeleteModal.value = false; };

const deleteClient = () => {
    deleteForm.delete(`/clients/${selectedClientId.value}`, {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
    });
};
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
