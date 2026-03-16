<template>
    <Head title="Products" />

    <div class="space-y-5">
        <div class="mb-6 rounded-2xl bg-[linear-gradient(130deg,#111111_0%,#173010_58%,#6DBE45_115%)] p-5 text-white shadow-[0_22px_45px_rgba(8,12,8,0.42)]">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#c7f4b2]">Service Management</p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-white">{{ copy.title }}</h1>
                    <p class="mt-1 text-sm text-white/75">{{ copy.subtitle }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <Badge tone="positive">{{ copy.plan }}: {{ normalizedPlan }}</Badge>
                    <Button type="button" :disabled="!canCreate" @click="openCreateModal">
                        <span class="inline-flex items-center gap-2">
                            <PlusIcon class="h-4 w-4" />
                            {{ copy.newProduct }}
                        </span>
                    </Button>
                </div>
            </div>
        </div>

        <p
            v-if="isFreeLimitReached"
            class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-800"
        >
            {{ copy.freeLimit }}
        </p>

        <p
            v-if="flashStatus"
            class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700"
        >
            {{ flashStatus }}
        </p>

        <Card>
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div class="flex w-full max-w-md items-center gap-2">
                    <div class="relative w-full">
                        <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="searchValue"
                            type="text"
                            class="w-full rounded-lg border border-slate-200 py-2.5 pl-9 pr-3 text-sm"
                            :placeholder="copy.searchPlaceholder"
                        >
                    </div>
                    <Button v-if="searchValue" type="button" variant="secondary" @click="clearSearch">
                        <span class="inline-flex items-center gap-2">
                            <XMarkIcon class="h-4 w-4" />
                            {{ copy.clear }}
                        </span>
                    </Button>
                </div>
            </div>

            <div v-if="productRows.length" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left">
                    <thead class="bg-slate-50">
                        <tr class="text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-3 py-3 font-semibold">{{ copy.product }}</th>
                            <th class="px-3 py-3 font-semibold">{{ copy.description }}</th>
                            <th class="px-3 py-3 font-semibold">{{ copy.price }}</th>
                            <th class="px-3 py-3 font-semibold">{{ copy.duration }}</th>
                            <th class="px-3 py-3 font-semibold">{{ copy.link }}</th>
                            <th class="px-3 py-3 font-semibold text-right">{{ copy.actions }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <tr v-for="product in productRows" :key="product.id" class="align-top">
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-slate-100">
                                        <img
                                            v-if="product.image"
                                            :src="storageUrl(product.image)"
                                            :alt="product.name"
                                            class="h-full w-full object-cover"
                                        >
                                        <div v-else class="flex h-full w-full items-center justify-center text-slate-400">
                                            <PhotoIcon class="h-5 w-5" />
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-900">{{ product.name }}</p>
                                        <p class="text-xs text-slate-500">{{ formatDate(product.created_at) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <p class="line-clamp-2 max-w-sm text-sm text-slate-600">{{ product.description }}</p>
                            </td>
                            <td class="px-3 py-3">
                                <span class="text-sm font-medium text-slate-700">{{ product.price || '-' }}</span>
                            </td>
                            <td class="px-3 py-3">
                                <span class="text-sm font-medium text-slate-700">{{ product.duration_minutes ? `${product.duration_minutes} min` : '-' }}</span>
                            </td>
                            <td class="px-3 py-3">
                                <a
                                    v-if="product.link"
                                    :href="product.link"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 text-sm font-medium text-[#1f6d00] hover:underline"
                                >
                                    <LinkIcon class="h-4 w-4" />
                                    {{ copy.open }}
                                </a>
                                <span v-else class="text-sm text-slate-400">-</span>
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex justify-end gap-2">
                                    <Button type="button" variant="secondary" @click="openEditModal(product)">
                                        <span class="inline-flex items-center gap-2">
                                            <PencilSquareIcon class="h-4 w-4" />
                                            {{ copy.edit }}
                                        </span>
                                    </Button>
                                    <Button type="button" variant="danger" @click="openDeleteModal(product)">
                                        <span class="inline-flex items-center gap-2">
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

            <div v-else class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center">
                <CubeIcon class="mx-auto h-10 w-10 text-slate-400" />
                <p class="mt-3 text-sm font-medium text-slate-700">{{ copy.emptyTitle }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ copy.emptySubtitle }}</p>
                <Button class="mt-4" type="button" :disabled="!canCreate" @click="openCreateModal">
                    <span class="inline-flex items-center gap-2">
                        <PlusIcon class="h-4 w-4" />
                        {{ copy.newProduct }}
                    </span>
                </Button>
            </div>

            <div v-if="productRows.length" class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-medium text-slate-600">
                    {{ copy.showing }} {{ productsMeta.from || 0 }}-{{ productsMeta.to || 0 }} {{ copy.of }} {{ productsMeta.total || 0 }} {{ copy.products }}
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        v-if="prevPageUrl"
                        :href="prevPageUrl"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 transition hover:border-[#6DBE45] hover:text-slate-800"
                        preserve-scroll
                        preserve-state
                    >
                        <ChevronLeftIcon class="h-4 w-4" />
                        {{ copy.previous }}
                    </Link>
                    <span
                        v-else
                        class="inline-flex cursor-not-allowed items-center gap-2 rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-400"
                    >
                        <ChevronLeftIcon class="h-4 w-4" />
                        {{ copy.previous }}
                    </span>

                    <span class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600">
                        {{ currentPage }} / {{ lastPage }}
                    </span>

                    <Link
                        v-if="nextPageUrl"
                        :href="nextPageUrl"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 transition hover:border-[#6DBE45] hover:text-slate-800"
                        preserve-scroll
                        preserve-state
                    >
                        {{ copy.next }}
                        <ChevronRightIcon class="h-4 w-4" />
                    </Link>
                    <span
                        v-else
                        class="inline-flex cursor-not-allowed items-center gap-2 rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-400"
                    >
                        {{ copy.next }}
                        <ChevronRightIcon class="h-4 w-4" />
                    </span>
                </div>
            </div>
        </Card>
    </div>

    <Modal :show="showFormModal" :title="isEditing ? copy.editProduct : copy.newProduct" max-width-class="max-w-4xl" @close="closeFormModal">
        <form class="grid gap-5 lg:grid-cols-12" @submit.prevent="submitForm">
            <div class="space-y-4 lg:col-span-7">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.name }}</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5"
                        required
                    >
                    <p v-if="form.errors.name" class="mt-1 text-xs text-rose-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.description }}</label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        maxlength="255"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5"
                        required
                    ></textarea>
                    <div class="mt-1 flex items-center justify-between">
                        <p v-if="form.errors.description" class="text-xs text-rose-600">{{ form.errors.description }}</p>
                        <p class="ml-auto text-xs text-slate-400">{{ form.description.length }}/255</p>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.price }}</label>
                    <input
                        v-model="form.price"
                        type="text"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5"
                        :placeholder="copy.pricePlaceholder"
                    >
                    <p v-if="form.errors.price" class="mt-1 text-xs text-rose-600">{{ form.errors.price }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.duration }}</label>
                    <input
                        v-model.number="form.duration_minutes"
                        type="number"
                        min="5"
                        max="600"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5"
                        :placeholder="copy.durationPlaceholder"
                    >
                    <p class="mt-1 text-xs text-slate-500">{{ copy.durationHint }}</p>
                    <p v-if="form.errors.duration_minutes" class="mt-1 text-xs text-rose-600">{{ form.errors.duration_minutes }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ copy.link }}</label>
                    <input
                        v-model="form.link"
                        type="text"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2.5"
                        placeholder="https://"
                    >
                    <p v-if="form.errors.link" class="mt-1 text-xs text-rose-600">{{ form.errors.link }}</p>
                </div>

                <p v-if="form.errors.limit" class="text-xs text-rose-600">{{ form.errors.limit }}</p>

                <div class="flex items-center justify-end gap-2">
                    <Button type="button" variant="secondary" @click="closeFormModal">
                        <span class="inline-flex items-center gap-2">
                            <XMarkIcon class="h-4 w-4" />
                            {{ copy.cancel }}
                        </span>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <span class="inline-flex items-center gap-2">
                            <CheckIcon class="h-4 w-4" />
                            {{ isEditing ? copy.saveChanges : copy.create }}
                        </span>
                    </Button>
                </div>
            </div>

            <div class="space-y-3 lg:col-span-5">
                <label class="block text-sm font-medium text-slate-700">{{ copy.image }}</label>
                <button
                    type="button"
                    class="group relative block w-full overflow-hidden rounded-2xl border border-dashed border-slate-300 bg-slate-50 transition hover:border-[#6DBE45] hover:bg-[#6DBE45]/5"
                    @click="triggerImageInput"
                >
                    <div class="mx-auto flex h-[260px] w-full max-w-[320px] items-center justify-center p-3">
                        <img v-if="imagePreview" :src="imagePreview" alt="preview" class="h-full w-full object-contain">
                        <div v-else class="flex h-full w-full flex-col items-center justify-center gap-2 text-slate-500">
                            <PhotoIcon class="h-6 w-6" />
                            <span class="text-sm font-medium">{{ copy.uploadImage }}</span>
                        </div>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 flex items-center justify-center bg-gradient-to-t from-black/45 to-transparent px-3 py-2 text-xs font-medium text-white">
                        <span class="inline-flex items-center gap-1.5">
                            <ArrowUpTrayIcon class="h-4 w-4" />
                            {{ copy.uploadImage }}
                        </span>
                    </div>

                    <button
                        v-if="imagePreview"
                        type="button"
                        class="absolute right-2 top-2 z-10 inline-flex h-8 w-8 items-center justify-center rounded-full bg-rose-600 text-white shadow-lg transition hover:scale-105 hover:bg-rose-700"
                        :title="copy.removeImage"
                        @click.stop="removeImage"
                    >
                        <TrashIcon class="h-4 w-4" />
                    </button>
                </button>

                <input ref="imageInputRef" type="file" accept="image/*" class="hidden" @change="onImageSelected">
                <p v-if="form.errors.image" class="text-xs text-rose-600">{{ form.errors.image }}</p>
            </div>
        </form>
    </Modal>

    <Modal :show="showDeleteModal" :title="copy.deleteProduct" @close="closeDeleteModal">
        <div class="space-y-4">
            <p class="text-sm text-slate-600">
                {{ copy.deleteConfirm }}
            </p>

            <div class="flex items-center justify-end gap-2">
                <Button type="button" variant="secondary" @click="closeDeleteModal">
                    <span class="inline-flex items-center gap-2">
                        <XMarkIcon class="h-4 w-4" />
                        {{ copy.cancel }}
                    </span>
                </Button>
                <Button type="button" variant="danger" :disabled="form.processing" @click="confirmDelete">
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
import { useLocale } from '@/composables/useLocale';
import Badge from '@/components/ui/Badge.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Modal from '@/components/ui/Modal.vue';
import {
    ArrowUpTrayIcon,
    CheckIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    CubeIcon,
    LinkIcon,
    MagnifyingGlassIcon,
    PencilSquareIcon,
    PhotoIcon,
    PlusIcon,
    TrashIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    products: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
            from: 0,
            to: 0,
            total: 0,
            current_page: 1,
            last_page: 1,
            prev_page_url: null,
            next_page_url: null,
        }),
    },
    canCreate: {
        type: Boolean,
        default: false,
    },
    plan: {
        type: String,
        default: 'free',
    },
    limit: {
        type: Number,
        default: null,
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
        }),
    },
});

const { t } = useLocale();
const page = usePage();
const flashStatus = computed(() => page.props.flash?.status || '');
const normalizedPlan = computed(() => (props.plan || 'free').toLowerCase());
const productRows = computed(() => props.products?.data ?? []);
const productsMeta = computed(() => ({
    from: props.products?.from ?? 0,
    to: props.products?.to ?? 0,
    total: props.products?.total ?? 0,
}));
const prevPageUrl = computed(() => props.products?.prev_page_url ?? null);
const nextPageUrl = computed(() => props.products?.next_page_url ?? null);
const currentPage = computed(() => props.products?.current_page ?? 1);
const lastPage = computed(() => props.products?.last_page ?? 1);
const isFreeLimitReached = computed(() => normalizedPlan.value === 'free' && props.limit && (props.products?.total ?? 0) >= props.limit);
const searchValue = ref(props.filters?.search || '');

const copy = computed(() =>
    t({
        en: {
            title: 'Products',
            subtitle: 'Publish your products and optional buy links.',
            plan: 'Plan',
            newProduct: 'New Product',
            freeLimit: 'Free plan allows only 2 products. Upgrade to add unlimited products.',
            product: 'Product',
            name: 'Name',
            description: 'Description',
            price: 'Price',
            duration: 'Service Duration (for booking appointments)',
            durationHint: 'Used to let visitors book appointments from your public card form.',
            link: 'Link',
            image: 'Image',
            actions: 'Actions',
            clear: 'Clear',
            showing: 'Showing',
            of: 'of',
            products: 'products',
            previous: 'Previous',
            next: 'Next',
            searchPlaceholder: 'Search by name, description, price or link',
            open: 'Open',
            edit: 'Edit',
            delete: 'Delete',
            create: 'Create',
            cancel: 'Cancel',
            saveChanges: 'Save Changes',
            save: 'Save',
            editProduct: 'Edit Product',
            deleteProduct: 'Delete Product',
            deleteConfirm: 'This action cannot be undone. Do you want to delete this product?',
            emptyTitle: 'No products yet',
            emptySubtitle: 'Create your first product to start sharing offers.',
            pricePlaceholder: '$25 each / Negotiable',
            durationPlaceholder: '30',
            uploadImage: 'Upload',
            removeImage: 'Remove',
        },
        es: {
            title: 'Productos',
            subtitle: 'Publica tus productos y enlaces de compra opcionales.',
            plan: 'Plan',
            newProduct: 'Nuevo Producto',
            freeLimit: 'El plan free permite solo 2 productos. Actualiza para agregar productos ilimitados.',
            product: 'Producto',
            name: 'Nombre',
            description: 'Descripción',
            price: 'Precio',
            duration: 'Duracion del servicio (para agendar citas)',
            durationHint: 'Se usa para que las personas puedan agendar citas desde el formulario publico de tu tarjeta.',
            link: 'Enlace',
            image: 'Imagen',
            actions: 'Acciones',
            clear: 'Limpiar',
            showing: 'Mostrando',
            of: 'de',
            products: 'productos',
            previous: 'Anterior',
            next: 'Siguiente',
            searchPlaceholder: 'Buscar por nombre, descripción, precio o enlace',
            open: 'Abrir',
            edit: 'Editar',
            delete: 'Eliminar',
            create: 'Crear',
            cancel: 'Cancelar',
            saveChanges: 'Guardar Cambios',
            save: 'Guardar',
            editProduct: 'Editar Producto',
            deleteProduct: 'Eliminar Producto',
            deleteConfirm: 'Esta acción no se puede restaurar. ¿Deseas eliminar este producto?',
            emptyTitle: 'Aún no tienes productos',
            emptySubtitle: 'Crea tu primer producto para comenzar a compartir tus ofertas.',
            pricePlaceholder: '$25 cada uno / A convenir',
            durationPlaceholder: '30',
            uploadImage: 'Subir',
            removeImage: 'Quitar',
        },
    })
);

const showFormModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const activeProductId = ref(null);
const productToDelete = ref(null);
const imageInputRef = ref(null);
const imagePreview = ref('');

const form = useForm({
    name: '',
    description: '',
    price: '',
    duration_minutes: null,
    link: '',
    image: null,
    remove_image: false,
    _method: '',
});

const formatDate = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleDateString();
};

const storageUrl = (path) => (path ? `/storage/${path}` : '');

const applySearch = () => {
    router.get('/products', {
        search: searchValue.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearSearch = () => {
    searchValue.value = '';
};

let searchDebounceTimer = null;

watch(searchValue, () => {
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }

    searchDebounceTimer = setTimeout(() => {
        applySearch();
    }, 320);
});

const resetForm = () => {
    form.reset();
    form.clearErrors();
    form.remove_image = false;
    form._method = '';
    imagePreview.value = '';
    activeProductId.value = null;
    isEditing.value = false;
};

const openCreateModal = () => {
    if (!props.canCreate) return;
    resetForm();
    showFormModal.value = true;
};

const openEditModal = (product) => {
    resetForm();
    isEditing.value = true;
    activeProductId.value = product.id;
    form.name = product.name || '';
    form.description = product.description || '';
    form.price = product.price || '';
    form.duration_minutes = product.duration_minutes || null;
    form.link = product.link || '';
    imagePreview.value = product.image ? storageUrl(product.image) : '';
    showFormModal.value = true;
};

const closeFormModal = () => {
    showFormModal.value = false;
    resetForm();
};

const triggerImageInput = () => imageInputRef.value?.click();

const onImageSelected = (event) => {
    const file = event.target.files?.[0] || null;
    form.image = file;
    form.remove_image = false;
    imagePreview.value = file ? URL.createObjectURL(file) : imagePreview.value;
};

const removeImage = () => {
    form.image = null;
    form.remove_image = true;
    imagePreview.value = '';
    if (imageInputRef.value) {
        imageInputRef.value.value = '';
    }
};

const submitForm = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => closeFormModal(),
    };

    if (isEditing.value && activeProductId.value) {
        form._method = 'put';
        form.post(`/products/${activeProductId.value}`, {
            ...options,
            forceFormData: true,
        });
        return;
    }

    form._method = '';
    form.post('/products', {
        ...options,
        forceFormData: true,
    });
};

const openDeleteModal = (product) => {
    productToDelete.value = product;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    productToDelete.value = null;
};

const confirmDelete = () => {
    if (!productToDelete.value) return;
    form.delete(`/products/${productToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
    });
};
</script>
