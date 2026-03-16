<template>
    <div>
        <div
            v-if="isOpen"
            class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden"
            @click="$emit('close')"
        />

        <aside
            class="sidebar-scroll fixed left-0 top-16 z-50 h-[calc(100vh-4rem)] w-72 overflow-y-auto border-r border-black/20 bg-[linear-gradient(180deg,#111111_0%,#111111_62%,#1a2a12_82%,#6DBE45_100%)] p-4 shadow-2xl transition-transform duration-300 lg:hidden"
            :class="isOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-full flex-col">
                <div class="mb-6 px-2">
                    <h2 class="text-xs font-semibold uppercase tracking-[0.18em] text-white/60">{{ copy.navigation }}</h2>
                </div>

                <nav class="space-y-1">
                    <Link
                        v-for="item in menuItems"
                        :key="item.name"
                        :href="item.href"
                        class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition"
                        :class="isActive(item.href)
                            ? 'bg-[#6DBE45] text-[#111111] shadow-md'
                            : 'text-white/85 hover:bg-white/10 hover:text-white'"
                        @click="$emit('close')"
                    >
                        <component :is="item.icon" class="h-5 w-5" />
                        <span>{{ item.name }}</span>
                    </Link>
                </nav>

                <a
                    href="https://xper.team"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mt-4 inline-flex text-xs font-medium text-white/80 transition hover:text-white"
                >
                    {{ copy.productBy }}
                </a>

                <div class="mt-auto rounded-xl border border-white/15 bg-white/10 p-4 text-white">
                    <p class="text-sm font-semibold">{{ copy.proExperience }}</p>
                    <p class="mt-1 text-xs text-white/75">{{ copy.proExperienceDesc }}</p>
                </div>
            </div>
        </aside>

        <aside
            class="hidden h-full shrink-0 border-r border-black/20 bg-[linear-gradient(180deg,#111111_0%,#111111_62%,#1a2a12_82%,#6DBE45_100%)] shadow-xl transition-all duration-300 lg:block"
            :class="isOpen ? 'w-72 p-4' : 'w-0 p-0 overflow-hidden border-r-0'"
        >
            <div class="sidebar-scroll h-full overflow-y-auto" :class="isOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'">
                <div class="flex h-full flex-col">
                    <div class="mb-6 px-2">
                        <h2 class="text-xs font-semibold uppercase tracking-[0.18em] text-white/60">{{ copy.navigation }}</h2>
                    </div>

                    <nav class="space-y-1">
                        <Link
                            v-for="item in menuItems"
                            :key="item.name"
                            :href="item.href"
                            class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition"
                            :class="isActive(item.href)
                                ? 'bg-[#6DBE45] text-[#111111] shadow-md'
                                : 'text-white/85 hover:bg-white/10 hover:text-white'"
                        >
                            <component :is="item.icon" class="h-5 w-5" />
                            <span>{{ item.name }}</span>
                        </Link>
                    </nav>

                    <a
                        href="https://xper.team"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-4 inline-flex text-xs font-medium text-white/80 transition hover:text-white"
                    >
                        {{ copy.productBy }}
                    </a>

                    <div class="mt-auto rounded-xl border border-white/15 bg-white/10 p-4 text-white">
                        <p class="text-sm font-semibold">{{ copy.proExperience }}</p>
                        <p class="mt-1 text-xs text-white/75">{{ copy.proExperienceDesc }}</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</template>

<script setup>
import {
    ChartBarSquareIcon,
    CreditCardIcon,
    EnvelopeIcon,
    HomeIcon,
    UsersIcon,
    UserCircleIcon,
    CalendarDaysIcon,
    IdentificationIcon,
    ShoppingBagIcon,
} from '@heroicons/vue/24/outline';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useLocale } from '@/composables/useLocale';

defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['close']);

const page = usePage();
const { t } = useLocale();
const copy = computed(() => t({
    en: {
        navigation: 'Navigation',
        dashboard: 'Dashboard',
        cards: 'Cards',
        products: 'Products',
        appointments: 'Appointments',
        updateProfile: 'Update Profile',
        users: 'Users',
        analytics: 'Analytics',
        stripe: 'Stripe Settings',
        mail: 'Mail Settings',
        productBy: 'A product of Xperteam LLC',
        proExperience: 'ReferyApp Pro Experience',
        proExperienceDesc: 'Fast navigation, premium branding and modular architecture.',
    },
    es: {
        navigation: 'Navegacion',
        dashboard: 'Dashboard',
        cards: 'Tarjetas',
        products: 'Productos',
        appointments: 'Citas',
        updateProfile: 'Actualizar Perfil',
        users: 'Usuarios',
        analytics: 'Analiticas',
        stripe: 'Stripe Config',
        mail: 'Config Mail',
        productBy: 'Un producto de Xperteam LLC',
        proExperience: 'Experiencia Pro de ReferyApp',
        proExperienceDesc: 'Navegacion rapida, branding premium y arquitectura modular.',
    },
}));

const adminItems = computed(() => [
    { name: copy.value.dashboard, href: '/dashboard', icon: HomeIcon },
    { name: copy.value.users, href: '/users', icon: UsersIcon },
    { name: copy.value.stripe, href: '/stripe-settings', icon: CreditCardIcon },
    { name: copy.value.mail, href: '/mail-settings', icon: EnvelopeIcon },
    { name: copy.value.analytics, href: '/analytics', icon: ChartBarSquareIcon },
]);

const businessItems = computed(() => [
    { name: copy.value.dashboard, href: '/dashboard', icon: HomeIcon },
    { name: copy.value.cards, href: '/cards', icon: IdentificationIcon },
    { name: copy.value.products, href: '/products', icon: ShoppingBagIcon },
    { name: copy.value.appointments, href: '/appointments', icon: CalendarDaysIcon },
    { name: copy.value.updateProfile, href: '/profile', icon: UserCircleIcon },
    { name: copy.value.analytics, href: '/analytics', icon: ChartBarSquareIcon },
]);

const menuItems = computed(() => {
    const role = page.props.auth?.user?.role ?? 'business';
    return role === 'admin' ? adminItems.value : businessItems.value;
});

const isActive = (href) =>
    page.url === href ||
    page.url.startsWith(`${href}/`) ||
    page.url.startsWith(`${href}?`);
</script>

<style scoped>
.sidebar-scroll::-webkit-scrollbar {
    width: 6px;
}

.sidebar-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-scroll::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.22);
    border-radius: 3px;
    transition: background 0.2s ease;
}

.sidebar-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.35);
}

.sidebar-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgba(255, 255, 255, 0.22) transparent;
}
</style>
