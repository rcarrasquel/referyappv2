<template>
    <header class="relative z-[120] shrink-0 overflow-visible border-b border-black/30 bg-[#111111] text-white">
        <div class="flex h-16 items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    class="rounded-lg border border-white/20 bg-white/5 p-2 text-white transition hover:bg-white/10"
                    @click="$emit('toggle-sidebar')"
                >
                    <component :is="sidebarOpen ? XMarkIcon : Bars3Icon" class="h-5 w-5" />
                </button>
                <p class="text-base font-semibold tracking-tight">ReferyApp</p>
            </div>

            <div class="flex items-center gap-3">
                <div class="relative overflow-visible">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-white/20 bg-white/5 px-3 py-2 text-sm font-medium text-white transition hover:bg-white/10"
                        @click="isMenuOpen = !isMenuOpen"
                    >
                        <UserCircleIcon class="h-4 w-4" />
                        {{ props.user?.name ?? copy.user }}
                    </button>

                    <div
                        v-if="isMenuOpen"
                        class="absolute right-0 z-[140] mt-2 w-44 rounded-xl border border-slate-200 bg-white p-2 shadow-xl"
                    >
                        <Link
                            v-if="!isAdmin"
                            href="/profile"
                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-50"
                            @click="isMenuOpen = false"
                        >
                            <UserCircleIcon class="h-4 w-4" />
                            {{ copy.updateProfile }}
                        </Link>
                        <button
                            type="button"
                            class="mt-1 flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-rose-600 transition hover:bg-rose-50"
                            @click="handleLogout"
                        >
                            <ArrowLeftOnRectangleIcon class="h-4 w-4" />
                            {{ copy.logout }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ArrowLeftOnRectangleIcon, Bars3Icon, UserCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useLocale } from '@/composables/useLocale';

defineEmits(['toggle-sidebar']);

const props = defineProps({
    user: {
        type: Object,
        default: null,
    },
    sidebarOpen: {
        type: Boolean,
        default: false,
    },
});

const isMenuOpen = ref(false);
const { t } = useLocale();
const isAdmin = computed(() => props.user?.role === 'admin');
const copy = computed(() => t({
    en: {
        user: 'User',
        updateProfile: 'Update Profile',
        logout: 'Logout',
    },
    es: {
        user: 'Usuario',
        updateProfile: 'Actualizar Perfil',
        logout: 'Cerrar Sesion',
    },
}));

const handleLogout = () => {
    isMenuOpen.value = false;
    router.post('/logout', {}, {
        onFinish: () => {
            window.location.href = '/login';
        }
    });
};
</script>
