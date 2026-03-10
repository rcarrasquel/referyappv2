<template>
    <div class="relative min-h-screen overflow-hidden bg-slate-50">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(109,190,69,0.20),_transparent_40%),radial-gradient(circle_at_20%_80%,_rgba(17,17,17,0.08),_transparent_45%)]" />

        <div class="relative mx-auto grid min-h-screen max-w-7xl grid-cols-1 px-4 py-8 lg:grid-cols-2 lg:gap-8 lg:px-8 lg:py-10">
            <section ref="promoRef" class="relative hidden overflow-hidden rounded-3xl bg-[#111111] p-8 text-white shadow-2xl lg:flex lg:flex-col lg:justify-between">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(142,219,99,0.26),_transparent_55%)]" />

                <div class="relative">
                    <p class="inline-flex items-center rounded-full border border-white/20 bg-white/5 px-3 py-1 text-xs font-semibold tracking-[0.12em] text-[#8EDB63]">
                        REFERY.APP
                    </p>
                    <h1 class="mt-6 max-w-md text-4xl font-semibold leading-tight tracking-tight">
                        {{ copy.heroTitle }}
                    </h1>
                    <p class="mt-4 max-w-md text-sm leading-relaxed text-white/80">
                        {{ copy.heroBody }}
                    </p>
                </div>

                <div class="relative grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs text-white/60">{{ copy.plan }}</p>
                        <p class="mt-1 text-lg font-semibold">Free</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs text-white/60">{{ copy.upgrade }}</p>
                        <p class="mt-1 text-lg font-semibold">Pro Monthly</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs text-white/60">{{ copy.focus }}</p>
                        <p class="mt-1 text-lg font-semibold">{{ copy.impact }}</p>
                    </div>
                </div>
            </section>

            <section ref="formRef" class="flex items-center justify-center">
                <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white/95 p-8 shadow-xl backdrop-blur">
                    <div class="mb-6 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold tracking-wide text-[#6DBE45]">REFERY.APP</p>
                            <slot name="heading" />
                        </div>

                        <button
                            type="button"
                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-[#111111] transition hover:bg-slate-50"
                            @click="toggleLocale"
                        >
                            <LanguageIcon class="h-3.5 w-3.5" />
                            {{ isEnglish ? 'ES' : 'EN' }}
                        </button>
                    </div>

                    <slot />

                    <p class="mt-6 text-center text-xs text-slate-500">
                        {{ copy.product }}
                        <a href="https://xper.team" target="_blank" rel="noopener noreferrer" class="font-semibold text-[#111111] hover:text-black">
                            xper.team
                        </a>
                    </p>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup>
import { useLocale } from '@/composables/useLocale';
import { LanguageIcon } from '@heroicons/vue/24/outline';
import gsap from 'gsap';
import { computed, onMounted, ref } from 'vue';

const promoRef = ref(null);
const formRef = ref(null);

const { isEnglish, t, toggleLocale } = useLocale();

const copy = computed(() =>
    t({
        en: {
            heroTitle: 'Turn your link into a professional digital card.',
            heroBody: 'ReferyApp helps you launch your profile in minutes. Start for free and upgrade to monthly Pro when you need advanced features.',
            plan: 'Plan',
            upgrade: 'Upgrade',
            focus: 'Focus',
            impact: 'High impact',
            product: 'A product of Xperteam LLC -',
        },
        es: {
            heroTitle: 'Convierte tu enlace en una tarjeta digital profesional.',
            heroBody: 'ReferyApp te permite lanzar tu perfil en minutos. Empieza gratis y sube a Pro mensual cuando necesites funciones avanzadas.',
            plan: 'Plan',
            upgrade: 'Upgrade',
            focus: 'Enfoque',
            impact: 'Alto impacto',
            product: 'Un producto de Xperteam LLC -',
        },
    })
);

onMounted(() => {
    if (promoRef.value) {
        gsap.from(promoRef.value, {
            x: -18,
            opacity: 0,
            duration: 0.45,
            ease: 'power2.out',
        });
    }

    if (formRef.value) {
        gsap.from(formRef.value, {
            y: 14,
            opacity: 0,
            duration: 0.4,
            delay: 0.08,
            ease: 'power2.out',
        });
    }
});
</script>
