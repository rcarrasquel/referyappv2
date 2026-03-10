<template>
    <Transition
        enter-active-class="transition duration-250 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show" class="fixed inset-0 z-50 bg-black/50" />
    </Transition>

    <Transition
        enter-active-class="transition duration-250 ease-out"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2"
    >
        <div v-if="show" class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-16 sm:pt-20">
            <div class="w-full rounded-2xl bg-white p-6 shadow-2xl max-h-[84vh] overflow-y-auto" :class="maxWidthClass">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">{{ title }}</h2>
                    <button
                        type="button"
                        class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100"
                        @click="$emit('close')"
                    >
                        <XMarkIcon class="h-5 w-5" />
                    </button>
                </div>

                <slot />
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { XMarkIcon } from '@heroicons/vue/24/outline';

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: '',
    },
    maxWidthClass: {
        type: String,
        default: 'max-w-md',
    },
});

defineEmits(['close']);
</script>
