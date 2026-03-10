<template>
    <div class="flex h-screen flex-col bg-slate-50">
        <Navbar :user="user" :sidebar-open="sidebarOpen" @toggle-sidebar="toggleSidebar" />

        <div class="flex h-[calc(100vh-4rem)] overflow-hidden">
            <Sidebar :is-open="sidebarOpen" @close="sidebarOpen = false" />

            <main ref="contentRef" class="min-w-0 flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import Navbar from './Navbar.vue';
import Sidebar from './Sidebar.vue';

const page = usePage();
const contentRef = ref(null);
const sidebarOpen = ref(false);

const user = computed(() => page.props.auth?.user ?? null);

const handleViewport = () => {
    const isMobile = window.innerWidth < 1024;
    
    if (isMobile) {
        // Siempre cerrar en móvil
        sidebarOpen.value = false;
        hasUserToggled.value = false;
        return;
    }

    if (sidebarOpen.value === false && !hasUserToggled.value) {
        sidebarOpen.value = true;
    }
};

const hasUserToggled = ref(false);

const toggleSidebar = () => {
    hasUserToggled.value = true;
    sidebarOpen.value = !sidebarOpen.value;
};

onMounted(() => {
    // Solo establecer el estado inicial si no ha sido definido
    if (sidebarOpen.value === false && window.innerWidth >= 1024) {
        sidebarOpen.value = true;
    }
    window.addEventListener('resize', handleViewport, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('resize', handleViewport);
});

watch(
    () => page.url,
    () => {
        if (window.innerWidth < 1024) {
            sidebarOpen.value = false;
        }
    }
);
</script>
