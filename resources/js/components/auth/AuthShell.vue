<template>
    <div class="relative min-h-screen overflow-hidden bg-slate-50">
        <div class="pointer-events-none absolute inset-0" />

        <div class="relative mx-auto grid min-h-screen max-w-7xl grid-cols-1 gap-6 px-4 py-8 lg:grid-cols-2 lg:gap-8 lg:px-8 lg:py-10">
            <section ref="promoRef" class="relative order-2 overflow-hidden rounded-3xl bg-[#111111] p-6 text-white shadow-2xl sm:p-8 lg:order-1 lg:flex lg:flex-col lg:justify-between">
                <div class="absolute inset-0 bg-[#111111]" />

                <div class="relative">
                    <p class="inline-flex items-center rounded-full border border-white/20 bg-white/5 px-3 py-1 text-xs font-semibold tracking-[0.12em] text-[#8EDB63]">
                        REFERY.APP
                    </p>
                    <h1 class="mt-6 max-w-md text-4xl font-semibold leading-tight tracking-tight">
                        {{ activeHero.title }}
                    </h1>
                    <p class="mt-4 max-w-md text-sm leading-relaxed text-white/80">
                        {{ activeHero.body }}
                    </p>
                </div>

                <div v-if="sliderImages.length" class="relative mt-6 overflow-hidden bg-black">
                    <div class="relative w-full aspect-[4/5] bg-black">
                        <img
                            v-for="(image, index) in sliderImages"
                            :key="`${image}-${index}`"
                            :src="image"
                            :alt="`promo-${index + 1}`"
                            class="absolute inset-0 h-full w-full object-contain transition-opacity duration-700"
                            :class="index === currentSlide ? 'opacity-100' : 'opacity-0'"
                        >
                    </div>
                    <div class="absolute bottom-2 left-1/2 flex -translate-x-1/2 items-center gap-1.5 rounded-full bg-black/45 px-2 py-1">
                        <button
                            v-for="(image, index) in sliderImages"
                            :key="`dot-${image}-${index}`"
                            type="button"
                            class="h-1.5 w-4 rounded-full transition"
                            :class="index === currentSlide ? 'bg-[#6DBE45]' : 'bg-white/45'"
                            @click="goToSlide(index)"
                        />
                    </div>
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

            <section ref="formRef" class="order-1 flex items-center justify-center lg:order-2">
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
import { usePage } from '@inertiajs/vue3';
import gsap from 'gsap';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const promoRef = ref(null);
const formRef = ref(null);
const currentSlide = ref(0);
let sliderTimer = null;

const { isEnglish, t, toggleLocale } = useLocale();
const page = usePage();

const sliderImages = computed(() => {
    const value = page.props.promoImages;
    if (!Array.isArray(value)) {
        return [];
    }

    return value.filter((item) => typeof item === 'string' && item !== '');
});

const copy = computed(() =>
    t({
        en: {
            plan: 'Plan',
            upgrade: 'Upgrade',
            focus: 'Focus',
            impact: 'High impact',
            product: 'A product of Xperteam LLC -',
            slides: [
                {
                    title: 'Stylists who turn every profile into a premium first impression.',
                    body: 'Show your services, bookings, and contact options with a modern card your clients can trust.',
                },
                {
                    title: 'Craft professionals who build reputation one detail at a time.',
                    body: 'Share your work, receive leads, and keep all your service links in one polished profile.',
                },
                {
                    title: 'Healthcare professionals with a clear, confident digital presence.',
                    body: 'Help patients find your information fast, book consultations, and connect through one smart card.',
                },
                {
                    title: 'Engineers and technical experts with a profile that looks serious.',
                    body: 'Present projects, services, and appointments in a format designed to convert visitors into clients.',
                },
                {
                    title: 'Dental professionals ready to share trust at first glance.',
                    body: 'Highlight your practice, simplify contact, and centralize every key link in one place.',
                },
                {
                    title: 'Builders and contractors with a card built for real business.',
                    body: 'Show what you do, receive direct inquiries, and keep your digital presence professional everywhere.',
                },
            ],
        },
        es: {
            plan: 'Plan',
            upgrade: 'Upgrade',
            focus: 'Enfoque',
            impact: 'Alto impacto',
            product: 'Un producto de Xperteam LLC -',
            slides: [
                {
                    title: 'Estilistas que convierten cada perfil en una primera impresion premium.',
                    body: 'Muestra tus servicios, citas y formas de contacto con una tarjeta moderna en la que tus clientes confian.',
                },
                {
                    title: 'Profesionales del oficio que construyen reputacion en cada detalle.',
                    body: 'Comparte tu trabajo, recibe prospectos y organiza todos tus enlaces en un perfil elegante.',
                },
                {
                    title: 'Profesionales de la salud con una presencia digital clara y confiable.',
                    body: 'Permite que tus pacientes encuentren tu informacion rapido, agenden consultas y se conecten en una sola tarjeta.',
                },
                {
                    title: 'Ingenieros y expertos tecnicos con un perfil serio y profesional.',
                    body: 'Presenta proyectos, servicios y citas en un formato pensado para convertir visitas en clientes.',
                },
                {
                    title: 'Profesionales odontologicos listos para transmitir confianza al instante.',
                    body: 'Destaca tu consulta, facilita el contacto y centraliza cada enlace clave en un solo lugar.',
                },
                {
                    title: 'Constructores y contratistas con una tarjeta hecha para negocio real.',
                    body: 'Muestra lo que haces, recibe solicitudes directas y manten una presencia profesional en todo momento.',
                },
            ],
        },
    })
);

const activeHero = computed(() => {
    const slides = Array.isArray(copy.value.slides) ? copy.value.slides : [];
    if (!slides.length) {
        return {
            title: 'ReferyApp',
            body: '',
        };
    }

    const index = currentSlide.value % slides.length;
    return slides[index] ?? slides[0];
});

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

    if (sliderImages.value.length > 1) {
        sliderTimer = window.setInterval(() => {
            currentSlide.value = (currentSlide.value + 1) % sliderImages.value.length;
        }, 5200);
    }
});

onUnmounted(() => {
    if (sliderTimer) {
        clearInterval(sliderTimer);
        sliderTimer = null;
    }
});

const goToSlide = (index) => {
    currentSlide.value = index;
};
</script>
