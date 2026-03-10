<template>
    <Head title="ReferyApp" />

    <div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(109,190,69,0.2),_transparent_54%),#eef2f7] px-4 py-4 sm:px-6 md:py-8 lg:py-12">
        <div class="mx-auto flex min-h-[calc(100vh-2rem)] w-full max-w-[560px] items-center justify-center md:min-h-[calc(100vh-4rem)] md:max-w-[590px] lg:min-h-[calc(100vh-6rem)] lg:max-w-[580px]">
            <div ref="cardRef" class="relative flex h-[calc(100vh-2rem)] max-h-[960px] w-full flex-col overflow-hidden rounded-3xl border border-white/50 shadow-[0_28px_70px_rgba(15,23,42,0.28)] md:h-[calc(100vh-4rem)] md:max-h-none lg:h-[calc(100vh-6rem)]" :style="previewCardStyle">
                <div ref="headerRef" class="z-0 w-full" :class="[headerShapeClass, headerHeightClass, 'md:min-h-[140px] lg:min-h-[160px]']" :style="headerStyle">
                    <svg
                        v-if="showWaveDecoration"
                        viewBox="0 0 320 80"
                        preserveAspectRatio="none"
                        class="absolute inset-x-0 bottom-0 h-10 w-full"
                        :style="waveDecorationStyle"
                        fill="currentColor"
                    >
                        <path d="M0,32 C50,78 120,8 190,32 C250,52 285,48 320,36 L320,80 L0,80 Z" />
                    </svg>
                </div>

                <div class="relative z-20 flex flex-1 flex-col overflow-hidden" :class="previewBodyContainerClass">
                    <div ref="profileWrapRef" :class="profileWrapClass">
                        <img
                            v-if="state.profileImagePreview"
                            :src="state.profileImagePreview"
                            alt="Profile"
                            :class="[profileImageClass, profilePreviewClass, 'md:h-24 md:w-24 lg:h-28 lg:w-28']"
                        >
                        <div v-else :class="[profileImageClass, profilePreviewClass, 'bg-slate-200 md:h-24 md:w-24 lg:h-28 lg:w-28']" />
                    </div>

                    <div ref="textBlockRef" :class="textBlockClass">
                        <p class="text-xl font-semibold leading-tight md:text-2xl lg:text-3xl" :class="textAlignClass" :style="{ color: state.textColor }">
                            {{ state.name || 'Your Name' }}
                        </p>
                        <p class="mt-1 text-sm leading-relaxed md:mt-2 md:text-base lg:text-lg" :class="textAlignClass" :style="{ color: state.textColor }">
                            {{ state.description || '' }}
                        </p>
                    </div>

                    <div ref="linksWrapRef" class="public-links-scroll mt-5 min-h-0 flex-1 space-y-2.5 overflow-y-auto pb-2 pr-1 md:mt-6 md:space-y-3 lg:mt-7 lg:space-y-3.5">
                        <a
                            v-for="(link, idx) in normalizedLinks"
                            :key="`${idx}-${link.title}`"
                            :href="getPublicLinkHref(link, idx)"
                            class="public-link-item block w-full px-4 py-3 text-sm font-semibold transition md:px-5 md:py-3.5 md:text-base lg:px-6 lg:py-4 lg:text-lg"
                            :class="buttonShapeClass"
                            :style="{ backgroundColor: state.buttonBackgroundColor, color: state.buttonTextColor }"
                        >
                            <span class="flex w-full items-center justify-center gap-2 text-left md:gap-3">
                                <span class="inline-flex h-6 w-6 shrink-0 items-center justify-center md:h-7 md:w-7 lg:h-8 lg:w-8">
                                    <component
                                        :is="getLinkIconOption(link.icon).icon"
                                        v-if="getLinkIconOption(link.icon).type === 'hero'"
                                        class="h-4 w-4 md:h-5 md:w-5 lg:h-6 lg:w-6"
                                    />
                                    <svg v-else viewBox="0 0 24 24" class="h-4 w-4 md:h-5 md:w-5 lg:h-6 lg:w-6" fill="currentColor">
                                        <path :d="getLinkIconOption(link.icon).icon.path" />
                                    </svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate leading-tight">{{ link.title || 'Link' }}</span>
                                    <span v-if="link.description" class="mt-0.5 block truncate text-xs font-normal leading-tight opacity-80 md:text-sm">{{ link.description }}</span>
                                </span>
                            </span>
                        </a>
                    </div>
                </div>

                <!-- Offcanvas -->
                <Transition
                    :css="false"
                    @enter="onOffcanvasEnter"
                    @leave="onOffcanvasLeave"
                >
                    <div
                        v-if="isOffcanvasOpen"
                        ref="offcanvasRef"
                        class="absolute inset-0 z-40 flex flex-col bg-white/80 backdrop-blur-md"
                    >
                        <!-- Offcanvas Header -->
                        <div class="flex items-center justify-between border-b border-slate-200/50 px-5 py-4">
                            <h2 class="text-lg font-semibold text-slate-900">{{ offcanvasTitles[activeOption] }}</h2>
                            <button
                                class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-600 transition hover:bg-slate-100/50"
                                @click="closeOffcanvas"
                            >
                                <XMarkIcon class="h-5 w-5" />
                            </button>
                        </div>

                        <!-- Offcanvas Content -->
                        <div class="flex-1 overflow-y-auto p-5">
                            <div v-if="activeOption === 'products'">
                                <div v-if="sortedServices.length" class="space-y-3">
                                    <article
                                        v-for="service in sortedServices"
                                        :key="service.id"
                                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                                    >
                                        <div class="flex h-40 items-center justify-center bg-slate-50 px-3 py-3">
                                            <img
                                                v-if="service.image"
                                                :src="toStorageUrl(service.image)"
                                                :alt="service.name"
                                                class="max-h-full w-auto max-w-full rounded-lg object-contain"
                                            >
                                            <div v-else class="flex h-full w-full items-center justify-center rounded-lg border border-dashed border-slate-300 text-xs font-medium text-slate-400">
                                                No image
                                            </div>
                                        </div>
                                        <div class="px-4 py-3">
                                            <p class="text-sm font-semibold text-slate-800">{{ service.name }}</p>
                                            <p v-if="service.description" class="mt-1 text-xs leading-relaxed text-slate-600">
                                                {{ service.description }}
                                            </p>
                                            <p class="mt-2 text-xs text-slate-500">
                                                {{ service.price || '-' }} · {{ service.duration_minutes || 30 }} min
                                            </p>
                                        </div>
                                    </article>
                                </div>
                                <p v-else class="text-sm text-slate-600">No services available.</p>
                            </div>
                            <div v-else-if="activeOption === 'reservation'" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="space-y-3">
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ formCopy.requestType }}</label>
                                        <select v-model="bookingForm.request_type" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                                            <option value="contact">{{ formCopy.contactOnly }}</option>
                                            <option value="appointment">{{ formCopy.appointment }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ formCopy.service }}</label>
                                        <select v-model="bookingForm.product_id" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                                            <option value="" disabled>{{ formCopy.selectService }}</option>
                                            <option v-for="service in services" :key="service.id" :value="service.id">
                                                {{ service.name }} ({{ service.duration_minutes || 30 }}m)
                                            </option>
                                        </select>
                                        <p v-if="bookingForm.errors.product_id" class="mt-1 text-xs text-rose-600">{{ bookingForm.errors.product_id }}</p>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ formCopy.fullName }}</label>
                                        <input v-model="bookingForm.full_name" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                                        <p v-if="bookingForm.errors.full_name" class="mt-1 text-xs text-rose-600">{{ bookingForm.errors.full_name }}</p>
                                    </div>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ formCopy.phone }}</label>
                                            <input v-model="bookingForm.phone" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ formCopy.email }}</label>
                                            <input v-model="bookingForm.email" type="email" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                                            <p v-if="bookingForm.errors.email" class="mt-1 text-xs text-rose-600">{{ bookingForm.errors.email }}</p>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ formCopy.interest }}</label>
                                        <input v-model="bookingForm.interest" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                                    </div>
                                    <div v-if="bookingForm.request_type === 'appointment'" class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ formCopy.date }}</label>
                                            <input v-model="bookingForm.appointment_date" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                                            <p v-if="bookingForm.errors.appointment_date" class="mt-1 text-xs text-rose-600">{{ bookingForm.errors.appointment_date }}</p>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ formCopy.time }}</label>
                                            <select v-model="bookingForm.appointment_time" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm">
                                                <option value="">{{ loadingSlots ? formCopy.loading : formCopy.selectTime }}</option>
                                                <option v-for="slot in availableSlots" :key="slot.time" :value="slot.time">
                                                    {{ slot.time }}
                                                </option>
                                            </select>
                                            <p v-if="bookingForm.errors.appointment_time" class="mt-1 text-xs text-rose-600">{{ bookingForm.errors.appointment_time }}</p>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ formCopy.notes }}</label>
                                        <textarea v-model="bookingForm.notes" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></textarea>
                                    </div>
                                    <p v-if="bookingForm.errors.status" class="text-xs text-rose-600">{{ bookingForm.errors.status }}</p>
                                    <p v-if="!services.length" class="text-xs text-amber-700">{{ formCopy.noServices }}</p>
                                    <button
                                        type="button"
                                        class="inline-flex w-full items-center justify-center gap-2 px-4 py-3 text-sm font-semibold transition hover:brightness-95 disabled:cursor-not-allowed disabled:opacity-60"
                                        :class="buttonShapeClass"
                                        :style="{ backgroundColor: state.buttonBackgroundColor, color: state.buttonTextColor }"
                                        :disabled="bookingForm.processing || !services.length"
                                        @click="submitPublicBooking"
                                    >
                                        <span class="inline-flex items-center gap-2">
                                            <CheckIcon class="h-4 w-4" />
                                            {{ bookingForm.request_type === 'appointment' ? formCopy.bookAction : formCopy.contactAction }}
                                        </span>
                                    </button>
                                </div>
                            </div>
                            <div v-else-if="activeOption === 'vcard'">
                                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                    <p class="text-base font-semibold text-slate-900">{{ vcardCopy.title }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ vcardCopy.subtitle }}</p>

                                    <div class="mt-4 space-y-2 rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-700">
                                        <p><span class="font-semibold">Name:</span> {{ state.name || '-' }}</p>
                                        <p v-if="state.phone"><span class="font-semibold">Phone:</span> {{ state.phone }}</p>
                                        <p v-if="state.email"><span class="font-semibold">Email:</span> {{ state.email }}</p>
                                        <p v-if="state.address"><span class="font-semibold">Address:</span> {{ state.address }}</p>
                                        <p class="break-all"><span class="font-semibold">URL:</span> {{ shareUrl }}</p>
                                    </div>

                                    <button
                                        type="button"
                                        class="mt-4 inline-flex w-full items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold transition hover:brightness-95"
                                        :class="buttonShapeClass"
                                        :style="{ backgroundColor: state.buttonBackgroundColor, color: state.buttonTextColor }"
                                        @click="downloadVCard"
                                    >
                                        <ArrowDownTrayIcon class="h-4 w-4" />
                                        {{ vcardCopy.download }}
                                    </button>
                                </div>
                            </div>
                            <div v-else-if="activeOption === 'qr'">
                                <div class="flex min-h-full items-center justify-center">
                                    <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                                        <p class="text-base font-semibold text-slate-900">{{ state.name || 'ReferyApp Card' }}</p>
                                        <p class="mt-1 text-xs font-medium text-slate-500">@{{ state.username }}</p>
                                        <p v-if="state.description" class="mx-auto mt-2 max-w-[18rem] truncate text-xs text-slate-500">
                                            {{ state.description }}
                                        </p>
                                        <div class="mt-4 flex items-center justify-center">
                                            <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                                                <img
                                                    v-if="qrCodeDataUrl"
                                                    :src="qrCodeDataUrl"
                                                    :alt="qrCopy.alt"
                                                    class="mx-auto h-64 w-64 max-w-full"
                                                >
                                                <div v-else class="flex h-64 w-64 items-center justify-center text-xs text-slate-400">
                                                    {{ qrCopy.loading }}
                                                </div>
                                            </div>
                                        </div>
                                        <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ qrCopy.urlLabel }}</p>
                                        <p class="mt-1 break-all text-[11px] text-slate-600">{{ qrPublicUrl }}</p>
                                        <button
                                            type="button"
                                            class="mt-4 inline-flex w-full items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold transition hover:brightness-95 disabled:cursor-not-allowed disabled:opacity-60"
                                            :class="buttonShapeClass"
                                            :style="{ backgroundColor: state.buttonBackgroundColor, color: state.buttonTextColor }"
                                            :disabled="!qrCodeDataUrl"
                                            @click="downloadQrCode"
                                        >
                                            <ArrowDownTrayIcon class="h-4 w-4" />
                                            {{ qrCopy.download }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div v-else-if="activeOption === 'share'">
                                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                    <p class="text-base font-semibold text-slate-900">{{ shareCopy.title }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ shareCopy.subtitle }}</p>

                                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                        <button
                                            type="button"
                                            class="inline-flex w-full items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold transition hover:brightness-95"
                                            :class="buttonShapeClass"
                                            :style="{ backgroundColor: state.buttonBackgroundColor, color: state.buttonTextColor }"
                                            @click="shareNative"
                                        >
                                            <ShareIcon class="h-4 w-4" />
                                            {{ shareCopy.native }}
                                        </button>
                                        <button
                                            type="button"
                                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                            @click="copyShareUrl"
                                        >
                                            <LinkIcon class="h-4 w-4" />
                                            {{ copiedShareUrl ? shareCopy.copied : shareCopy.copy }}
                                        </button>
                                    </div>

                                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                                        <p class="truncate text-xs text-slate-600">{{ shareUrl }}</p>
                                    </div>

                                    <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3">
                                        <button
                                            v-for="action in shareActions"
                                            :key="action.key"
                                            type="button"
                                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                                            @click="openShare(action.key)"
                                        >
                                            <component v-if="action.iconType === 'hero'" :is="action.icon" class="h-4 w-4" />
                                            <svg v-else viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor" aria-hidden="true">
                                                <path :d="action.iconPath" />
                                            </svg>
                                            {{ action.label }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </Transition>

                <!-- Floating Action Button -->
                <div ref="fabRef" class="fixed bottom-5 right-5 z-[70] md:bottom-6 md:right-6" @click.stop>
                    <!-- Menu Options -->
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-2"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100 translate-y-0"
                        leave-to-class="opacity-0 scale-95 translate-y-2"
                    >
                        <div
                            v-if="isMenuOpen"
                            ref="fabMenuRef"
                            class="absolute bottom-16 right-0 mb-2 flex w-48 flex-col gap-1 rounded-2xl border border-slate-200/80 bg-white/95 p-2 shadow-2xl backdrop-blur-sm"
                        >
                            <button
                                v-for="option in menuOptions"
                                :key="option.id"
                                class="fab-menu-item flex items-center gap-3 rounded-xl px-4 py-3 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                @click="handleMenuAction(option.id)"
                            >
                                <component :is="option.icon" class="h-5 w-5 text-slate-600" />
                                <span>{{ option.label }}</span>
                            </button>
                        </div>
                    </Transition>

                    <!-- FAB Button -->
                    <button
                        ref="fabButtonRef"
                        class="flex h-14 w-14 items-center justify-center rounded-full bg-[#6DBE45] text-white shadow-2xl transition hover:bg-[#5da939] hover:shadow-3xl active:scale-95"
                        @click.stop="toggleMenu"
                    >
                        <component :is="isMenuOpen ? XMarkIcon : PlusIcon" class="h-6 w-6 transition-transform" :class="isMenuOpen ? 'rotate-90' : 'rotate-0'" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {
    ArrowDownTrayIcon,
    CalendarIcon,
    CheckIcon,
    ChatBubbleLeftEllipsisIcon,
    EnvelopeIcon,
    IdentificationIcon,
    LinkIcon,
    PaperAirplaneIcon,
    PhoneIcon,
    PlusIcon,
    QrCodeIcon,
    ShareIcon,
    ShoppingBagIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import {
    siDiscord,
    siFacebook,
    siGithub,
    siGmail,
    siGoogle,
    siGooglemessages,
    siInstagram,
    siMessenger,
    siSnapchat,
    siSpotify,
    siStripe,
    siTelegram,
    siTiktok,
    siTwitch,
    siWhatsapp,
    siX,
    siYoutube,
} from 'simple-icons';
import { Head, useForm } from '@inertiajs/vue3';
import gsap from 'gsap';
import QRCode from 'qrcode';
import { computed, nextTick, onMounted, onUnmounted, reactive, ref, watch } from 'vue';

defineOptions({ layout: null });

const isMenuOpen = ref(false);
const isOffcanvasOpen = ref(false);
const activeOption = ref(null);
const fabRef = ref(null);
const fabButtonRef = ref(null);
const fabMenuRef = ref(null);
const offcanvasRef = ref(null);
const availableSlotsRaw = ref([]);
const loadingSlots = ref(false);
const qrPublicUrl = ref('');
const qrCodeDataUrl = ref('');
const copiedShareUrl = ref(false);

const translations = {
    es: {
        products: 'Productos',
        vcard: 'vCard',
        qr: 'Código QR',
        share: 'Compartir',
        reservation: 'Reserva',
        requestType: 'Tipo de solicitud',
        contactOnly: 'Solo contacto',
        appointment: 'Agendar cita',
        service: 'Servicio o producto',
        selectService: 'Selecciona un servicio o producto',
        fullName: 'Nombre completo',
        phone: 'Teléfono',
        email: 'Correo',
        interest: 'Interés',
        date: 'Fecha',
        time: 'Hora',
        loading: 'Cargando...',
        selectTime: 'Selecciona una hora',
        notes: 'Mensaje',
        noServices: 'No hay servicios/productos disponibles para seleccionar.',
        bookAction: 'Solicitar cita',
        contactAction: 'Enviar contacto',
        qrAlt: 'Codigo QR de la tarjeta',
        qrLoading: 'Generando QR...',
        qrUrlLabel: 'Enlace',
        qrDownload: 'Descargar QR',
        shareTitle: 'Comparte esta tarjeta',
        shareSubtitle: 'Enviala en segundos y consigue mas contactos.',
        shareNative: 'Compartir ahora',
        shareCopy: 'Copiar enlace',
        shareCopied: 'Enlace copiado',
        shareWhatsapp: 'WhatsApp',
        shareInstagram: 'Instagram',
        shareX: 'X',
        shareFacebook: 'Facebook',
        shareTelegram: 'Telegram',
        shareEmail: 'Email',
        shareSubject: 'Mira mi tarjeta digital',
        vcardTitle: 'Descargar contacto',
        vcardSubtitle: 'Guarda esta tarjeta en tu libreta de contactos.',
        vcardDownload: 'Descargar vCard',
    },
    en: {
        products: 'Products',
        vcard: 'vCard',
        qr: 'QR Code',
        share: 'Share',
        reservation: 'Booking',
        requestType: 'Request type',
        contactOnly: 'Contact me',
        appointment: 'Book appointment',
        service: 'Service or product',
        selectService: 'Select a service or product',
        fullName: 'Full name',
        phone: 'Phone',
        email: 'Email',
        interest: 'Interest',
        date: 'Date',
        time: 'Time',
        loading: 'Loading...',
        selectTime: 'Select a time',
        notes: 'Message',
        noServices: 'No services/products available to select.',
        bookAction: 'Request appointment',
        contactAction: 'Send contact request',
        qrAlt: 'Card QR code',
        qrLoading: 'Generating QR...',
        qrUrlLabel: 'Link',
        qrDownload: 'Download QR',
        shareTitle: 'Share this card',
        shareSubtitle: 'Send it in seconds and get more contacts.',
        shareNative: 'Share now',
        shareCopy: 'Copy link',
        shareCopied: 'Link copied',
        shareWhatsapp: 'WhatsApp',
        shareInstagram: 'Instagram',
        shareX: 'X',
        shareFacebook: 'Facebook',
        shareTelegram: 'Telegram',
        shareEmail: 'Email',
        shareSubject: 'Check out my digital card',
        vcardTitle: 'Download contact',
        vcardSubtitle: 'Save this card to your contacts.',
        vcardDownload: 'Download vCard',
    },
};

const menuOptions = computed(() => {
    const lang = props.card.language || 'es';
    const labels = translations[lang] || translations.es;
    
    return [
        { id: 'products', label: labels.products, icon: ShoppingBagIcon },
        { id: 'vcard', label: labels.vcard, icon: IdentificationIcon },
        { id: 'qr', label: labels.qr, icon: QrCodeIcon },
        { id: 'reservation', label: labels.reservation, icon: CalendarIcon },
        { id: 'share', label: labels.share, icon: ShareIcon },
    ];
});

const offcanvasTitles = computed(() => {
    const lang = props.card.language || 'es';
    const labels = translations[lang] || translations.es;
    
    return {
        products: labels.products,
        vcard: labels.vcard,
        qr: labels.qr,
        reservation: labels.reservation,
        share: labels.share,
    };
});

const toggleMenu = () => {
    if (fabButtonRef.value) {
        gsap.to(fabButtonRef.value, {
            scale: 0.92,
            duration: 0.08,
            yoyo: true,
            repeat: 1,
            ease: 'power1.inOut',
        });
    }

    isMenuOpen.value = !isMenuOpen.value;
};

const handleMenuAction = (actionId) => {
    isMenuOpen.value = false;
    activeOption.value = actionId;
    if (actionId === 'reservation') {
        bookingForm.request_type = 'contact';
    }
    isOffcanvasOpen.value = true;
};

const closeOffcanvas = () => {
    isOffcanvasOpen.value = false;
    setTimeout(() => {
        activeOption.value = null;
    }, 500); // Espera a que termine la animación de cierre
};

const onOffcanvasEnter = (el, done) => {
    gsap.fromTo(
        el,
        {
            yPercent: 100,
            opacity: 0,
            scale: 0.95,
        },
        {
            yPercent: 0,
            opacity: 1,
            scale: 1,
            duration: 0.5,
            ease: 'back.out(1.2)',
            onComplete: done,
        }
    );
};

const onOffcanvasLeave = (el, done) => {
    gsap.to(el, {
        yPercent: 100,
        opacity: 0,
        scale: 0.95,
        duration: 0.4,
        ease: 'power2.in',
        onComplete: done,
    });
};

const props = defineProps({
    card: {
        type: Object,
        required: true,
    },
});

const services = computed(() => Array.isArray(props.card.services) ? props.card.services : []);
const cardPlan = computed(() => (props.card.plan || 'free').toString().toLowerCase());
const sortedServices = computed(() => [...services.value].sort((a, b) =>
    String(a?.name ?? '').localeCompare(String(b?.name ?? ''), undefined, { sensitivity: 'base' })
));
const formCopy = computed(() => {
    const lang = props.card.language || 'es';
    return translations[lang] || translations.es;
});
const qrCopy = computed(() => {
    const lang = props.card.language || 'es';
    const labels = translations[lang] || translations.es;
    return {
        alt: labels.qrAlt,
        loading: labels.qrLoading,
        urlLabel: labels.qrUrlLabel,
        download: labels.qrDownload,
    };
});
const shareCopy = computed(() => {
    const lang = props.card.language || 'es';
    const labels = translations[lang] || translations.es;
    return {
        title: labels.shareTitle,
        subtitle: labels.shareSubtitle,
        native: labels.shareNative,
        copy: labels.shareCopy,
        copied: labels.shareCopied,
        subject: labels.shareSubject,
    };
});
const vcardCopy = computed(() => {
    const lang = props.card.language || 'es';
    const labels = translations[lang] || translations.es;
    return {
        title: labels.vcardTitle,
        subtitle: labels.vcardSubtitle,
        download: labels.vcardDownload,
    };
});
const shareActions = computed(() => {
    const lang = props.card.language || 'es';
    const labels = translations[lang] || translations.es;
    return [
        { key: 'whatsapp', label: labels.shareWhatsapp, iconType: 'hero', icon: ChatBubbleLeftEllipsisIcon },
        { key: 'instagram', label: labels.shareInstagram, iconType: 'simple', iconPath: siInstagram.path },
        { key: 'x', label: labels.shareX, iconType: 'hero', icon: ShareIcon },
        { key: 'facebook', label: labels.shareFacebook, iconType: 'hero', icon: IdentificationIcon },
        { key: 'telegram', label: labels.shareTelegram, iconType: 'hero', icon: PaperAirplaneIcon },
        { key: 'email', label: labels.shareEmail, iconType: 'hero', icon: EnvelopeIcon },
    ];
});

const bookingForm = useForm({
    request_type: 'contact',
    product_id: '',
    full_name: '',
    phone: '',
    email: '',
    interest: '',
    appointment_date: new Date().toISOString().slice(0, 10),
    appointment_time: '',
    notes: '',
});

const cardRef = ref(null);
const headerRef = ref(null);
const profileWrapRef = ref(null);
const textBlockRef = ref(null);
const linksWrapRef = ref(null);

const toStorageUrl = (path) => (path ? `/storage/${path}` : '');
const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const state = reactive({
    slug: props.card.slug,
    name: props.card.name ?? '',
    username: props.card.username ?? '',
    description: props.card.description ?? '',
    phone: props.card.phone ?? '',
    email: props.card.email ?? '',
    address: props.card.address ?? '',
    googleMapsUrl: props.card.google_maps_url ?? '',
    headerColor: props.card.header_color ?? '#6DBE45',
    textColor: props.card.text_color ?? '#111111',
    buttonBackgroundColor: props.card.button_background_color ?? '#6DBE45',
    buttonTextColor: props.card.button_text_color ?? '#FFFFFF',
    backgroundColor: props.card.background_color ?? '#F5F5F5',
    buttonStyle: props.card.button_style ?? 'rounded',
    templateStyle: props.card.template_style ?? 'classic',
    profileImageStyle: props.card.profile_image_style ?? 'circle',
    profileImagePreview: toStorageUrl(props.card.profile_image),
    headerImagePreview: toStorageUrl(props.card.header_image),
    backgroundImagePreview: toStorageUrl(props.card.background_image),
    links: Array.isArray(props.card.links) ? props.card.links : [],
});

const availableSlots = computed(() => availableSlotsRaw.value.filter((slot) => slot.available));

const linkIconOptions = [
    { value: 'facebook', type: 'simple', icon: siFacebook },
    { value: 'instagram', type: 'simple', icon: siInstagram },
    { value: 'tiktok', type: 'simple', icon: siTiktok },
    { value: 'youtube', type: 'simple', icon: siYoutube },
    { value: 'x', type: 'simple', icon: siX },
    { value: 'spotify', type: 'simple', icon: siSpotify },
    { value: 'google', type: 'simple', icon: siGoogle },
    { value: 'gmail', type: 'simple', icon: siGmail },
    { value: 'whatsapp', type: 'simple', icon: siWhatsapp },
    { value: 'telegram', type: 'simple', icon: siTelegram },
    { value: 'discord', type: 'simple', icon: siDiscord },
    { value: 'messenger', type: 'simple', icon: siMessenger },
    { value: 'snapchat', type: 'simple', icon: siSnapchat },
    { value: 'twitch', type: 'simple', icon: siTwitch },
    { value: 'github', type: 'simple', icon: siGithub },
    { value: 'stripe', type: 'simple', icon: siStripe },
    { value: 'googlemessages', type: 'simple', icon: siGooglemessages },
    { value: 'link', type: 'hero', icon: LinkIcon },
    { value: 'phone', type: 'hero', icon: PhoneIcon },
    { value: 'sms', type: 'hero', icon: ChatBubbleLeftEllipsisIcon },
    { value: 'email', type: 'hero', icon: EnvelopeIcon },
];

const linkIconMap = Object.fromEntries(linkIconOptions.map((option) => [option.value, option]));
const defaultLinkIcon = linkIconMap.link;
const getLinkIconOption = (value) => linkIconMap[value] ?? defaultLinkIcon;

const normalizedLinks = computed(() => state.links
    .map((link) => ({
        icon: (link?.icon ?? 'link').toString(),
        title: (link?.title ?? link?.label ?? '').toString(),
        description: (link?.description ?? '').toString(),
    }))
    .filter((link) => link.title.trim() !== '')
    .slice(0, 24));

const normalizeSpecialLinkUrl = (icon, rawUrl) => {
    const value = (rawUrl ?? '').toString().trim();
    if (!value) {
        return '';
    }

    if (/^(mailto:|tel:|sms:)/i.test(value)) {
        return value;
    }

    if (icon === 'phone') {
        return `tel:${value.replace(/\s+/g, '')}`;
    }

    if (icon === 'email') {
        return `mailto:${value}`;
    }

    if (icon === 'sms') {
        return `sms:${value.replace(/\s+/g, '')}`;
    }

    return '';
};

const getPublicLinkHref = (link, index) => {
    const icon = (link?.icon ?? '').toString().toLowerCase();
    const special = normalizeSpecialLinkUrl(icon, link?.url);
    if (special) {
        return special;
    }

    return `/${state.username}/out/${index}`;
};

const styleTemplates = [
    { value: 'classic', profilePlacement: 'center', headerVariant: 'clean', headerHeight: 'normal', contentDensity: 'normal' },
    { value: 'classic_left', profilePlacement: 'left', headerVariant: 'clean', headerHeight: 'normal', contentDensity: 'normal' },
    { value: 'classic_right', profilePlacement: 'right', headerVariant: 'clean', headerHeight: 'normal', contentDensity: 'normal' },
    { value: 'wave_left', profilePlacement: 'left', headerVariant: 'wave', headerHeight: 'normal', contentDensity: 'normal' },
    { value: 'wave_right', profilePlacement: 'right', headerVariant: 'wave', headerHeight: 'normal', contentDensity: 'normal' },
    { value: 'wave_center', profilePlacement: 'center', headerVariant: 'wave', headerHeight: 'tall', contentDensity: 'normal' },
    { value: 'split_modern', profilePlacement: 'left', headerVariant: 'diagonal', headerHeight: 'normal', contentDensity: 'compact' },
    { value: 'split_right', profilePlacement: 'right', headerVariant: 'diagonal', headerHeight: 'normal', contentDensity: 'compact' },
    { value: 'soft_stack', profilePlacement: 'center', headerVariant: 'layered', headerHeight: 'normal', contentDensity: 'spacious' },
    { value: 'layered_left', profilePlacement: 'left', headerVariant: 'layered', headerHeight: 'normal', contentDensity: 'spacious' },
    { value: 'layered_right', profilePlacement: 'right', headerVariant: 'layered', headerHeight: 'normal', contentDensity: 'spacious' },
    { value: 'minimal_center', profilePlacement: 'center', headerVariant: 'clean', headerHeight: 'compact', contentDensity: 'compact' },
];

const styleMap = Object.fromEntries(styleTemplates.map((template) => [template.value, template]));

const activeTemplate = computed(() => ({
    headerHeight: 'normal',
    contentDensity: 'normal',
    ...(styleMap[state.templateStyle] ?? styleMap.classic),
}));

const isWaveTemplate = computed(() => activeTemplate.value.headerVariant === 'wave');
const showWaveDecoration = computed(() => isWaveTemplate.value && !state.headerImagePreview);
const waveDecorationStyle = computed(() => ({ color: state.headerColor }));

const previewCardStyle = computed(() => {
    if (!state.backgroundImagePreview) {
        return { backgroundColor: state.backgroundColor || '#ffffff' };
    }

    return {
        backgroundImage: `url(${state.backgroundImagePreview})`,
        backgroundSize: 'cover',
        backgroundPosition: 'center',
    };
});

const headerStyle = computed(() => {
    if (state.headerImagePreview) {
        return {
            backgroundImage: `url(${state.headerImagePreview})`,
            backgroundSize: 'cover',
            backgroundPosition: 'center',
        };
    }

    return { backgroundColor: state.headerColor };
});

const headerShapeClass = computed(() => {
    if (activeTemplate.value.headerVariant === 'wave') {
        return 'relative overflow-hidden rounded-b-[2.2rem]';
    }

    if (activeTemplate.value.headerVariant === 'layered') {
        return 'relative overflow-hidden rounded-b-[2rem]';
    }

    if (activeTemplate.value.headerVariant === 'diagonal') {
        return 'relative overflow-hidden [clip-path:polygon(0_0,100%_0,100%_86%,0_100%)]';
    }

    return 'relative';
});

const headerHeightClass = computed(() => {
    if (activeTemplate.value.headerHeight === 'tall') {
        return 'h-[132px]';
    }

    if (activeTemplate.value.headerHeight === 'compact') {
        return 'h-[100px]';
    }

    return 'h-[116px]';
});

const previewBodyContainerClass = computed(() => {
    if (activeTemplate.value.contentDensity === 'spacious') {
        return '-mt-9 px-4 pb-5';
    }

    if (activeTemplate.value.contentDensity === 'compact') {
        return '-mt-7 px-4 pb-3';
    }

    return '-mt-8 px-4 pb-4';
});

const profileWrapClass = computed(() => {
    if (activeTemplate.value.profilePlacement === 'left') {
        return 'flex justify-start';
    }

    if (activeTemplate.value.profilePlacement === 'right') {
        return 'flex justify-end';
    }

    return 'flex justify-center';
});

const profilePreviewClass = computed(() => {
    if (isWaveTemplate.value) {
        return 'rounded-full';
    }

    if (state.profileImageStyle === 'square') {
        return 'rounded-none';
    }

    if (state.profileImageStyle === 'rounded') {
        return 'rounded-xl';
    }

    return 'rounded-full';
});

const profileImageClass = computed(() => 'relative z-30 h-[74px] w-[74px] object-cover shadow-[0_12px_26px_rgba(15,23,42,0.32)]');

const textAlignClass = computed(() => {
    if (activeTemplate.value.profilePlacement === 'left') {
        return 'text-left';
    }

    if (activeTemplate.value.profilePlacement === 'right') {
        return 'text-right';
    }

    return 'text-center';
});

const textBlockClass = computed(() => 'mt-3');

const buttonShapeClass = computed(() => {
    if (state.buttonStyle === 'square') {
        return 'rounded-none';
    }

    if (state.buttonStyle === 'normal') {
        return 'rounded-md';
    }

    return 'rounded-xl';
});

const handleClickOutside = (event) => {
    if (fabRef.value && !fabRef.value.contains(event.target) && isMenuOpen.value) {
        isMenuOpen.value = false;
    }
};

const buildCurrentPublicUrl = () => {
    if (typeof window === 'undefined') {
        return '';
    }

    const { origin, pathname } = window.location;
    return `${origin}${pathname}`;
};

const shareUrl = computed(() => qrPublicUrl.value || buildCurrentPublicUrl());

const escapeVCardValue = (value) => String(value ?? '')
    .replace(/\\/g, '\\\\')
    .replace(/\n/g, '\\n')
    .replace(/,/g, '\\,')
    .replace(/;/g, '\\;')
    .trim();

const regenerateQrCode = async () => {
    const targetUrl = qrPublicUrl.value;
    if (!targetUrl) {
        qrCodeDataUrl.value = '';
        return;
    }

    try {
        const size = 512;
        const canvas = document.createElement('canvas');
        await QRCode.toCanvas(canvas, targetUrl, {
            width: 512,
            margin: 1,
            color: {
                dark: '#111111',
                light: '#FFFFFF',
            },
        });

        const centerLogoPath = cardPlan.value === 'free'
            ? '/icon-qr.png'
            : (state.profileImagePreview || '');

        if (centerLogoPath) {
            const context = canvas.getContext('2d');
            if (context) {
                const logo = await new Promise((resolve) => {
                    const image = new Image();
                    image.onload = () => resolve(image);
                    image.onerror = () => resolve(null);
                    image.src = centerLogoPath;
                });

                if (logo) {
                    const logoSize = Math.round(size * 0.18);
                    const logoX = Math.round((size - logoSize) / 2);
                    const logoY = Math.round((size - logoSize) / 2);
                    const pad = Math.round(logoSize * 0.16);
                    const bgX = logoX - pad;
                    const bgY = logoY - pad;
                    const bgSize = logoSize + pad * 2;
                    const radius = Math.round(bgSize * 0.22);

                    context.save();
                    context.fillStyle = '#FFFFFF';
                    context.beginPath();
                    context.moveTo(bgX + radius, bgY);
                    context.lineTo(bgX + bgSize - radius, bgY);
                    context.quadraticCurveTo(bgX + bgSize, bgY, bgX + bgSize, bgY + radius);
                    context.lineTo(bgX + bgSize, bgY + bgSize - radius);
                    context.quadraticCurveTo(bgX + bgSize, bgY + bgSize, bgX + bgSize - radius, bgY + bgSize);
                    context.lineTo(bgX + radius, bgY + bgSize);
                    context.quadraticCurveTo(bgX, bgY + bgSize, bgX, bgY + bgSize - radius);
                    context.lineTo(bgX, bgY + radius);
                    context.quadraticCurveTo(bgX, bgY, bgX + radius, bgY);
                    context.closePath();
                    context.fill();
                    context.restore();

                    context.drawImage(logo, logoX, logoY, logoSize, logoSize);
                }
            }
        }

        qrCodeDataUrl.value = canvas.toDataURL('image/png');
    } catch (_error) {
        qrCodeDataUrl.value = '';
    }
};

const downloadQrCode = () => {
    if (!qrCodeDataUrl.value) {
        return;
    }

    const anchor = document.createElement('a');
    anchor.href = qrCodeDataUrl.value;
    anchor.download = `${state.username || 'referyapp'}-qr.png`;
    document.body.appendChild(anchor);
    anchor.click();
    document.body.removeChild(anchor);
};

const downloadVCard = () => {
    const lines = [
        'BEGIN:VCARD',
        'VERSION:3.0',
    ];

    const fullName = escapeVCardValue(state.name || state.username || 'ReferyApp');
    lines.push(`FN:${fullName}`);
    lines.push(`N:;${fullName};;;`);

    if (state.description) {
        lines.push(`NOTE:${escapeVCardValue(state.description)}`);
    }
    if (state.phone) {
        lines.push(`TEL;TYPE=CELL:${escapeVCardValue(state.phone)}`);
    }
    if (state.email) {
        lines.push(`EMAIL;TYPE=INTERNET:${escapeVCardValue(state.email)}`);
    }
    if (state.address) {
        lines.push(`ADR;TYPE=WORK:;;${escapeVCardValue(state.address)};;;;`);
    }

    lines.push(`URL:${escapeVCardValue(shareUrl.value)}`);
    if (state.googleMapsUrl) {
        lines.push(`URL;TYPE=MAP:${escapeVCardValue(state.googleMapsUrl)}`);
    }

    lines.push('END:VCARD');

    const blob = new Blob([`${lines.join('\r\n')}\r\n`], { type: 'text/vcard;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const anchor = document.createElement('a');
    anchor.href = url;
    anchor.download = `${state.username || 'referyapp'}.vcf`;
    document.body.appendChild(anchor);
    anchor.click();
    document.body.removeChild(anchor);
    URL.revokeObjectURL(url);
};

const trackShareEvent = async (channel) => {
    if (!channel || !state.username) {
        return;
    }

    try {
        await fetch(`/${state.username}/share-events`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({ channel }),
        });
    } catch (_error) {
        // no-op
    }
};

const copyShareUrl = async (shouldTrack = true) => {
    const target = shareUrl.value;
    if (!target) {
        return;
    }

    try {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(target);
        } else {
            const input = document.createElement('input');
            input.value = target;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
        }
        copiedShareUrl.value = true;
        setTimeout(() => {
            copiedShareUrl.value = false;
        }, 1800);
        if (shouldTrack) {
            trackShareEvent('copy');
        }
    } catch (_error) {
        copiedShareUrl.value = false;
    }
};

const shareNative = async () => {
    const url = shareUrl.value;
    if (!url) {
        return;
    }

    const title = state.name || 'ReferyApp';
    const text = state.description || shareCopy.value.subject;

    if (navigator.share) {
        try {
            await navigator.share({ title, text, url });
            trackShareEvent('native');
            return;
        } catch (_error) {
            // no-op
        }
    }

    copyShareUrl(false);
    trackShareEvent('native');
};

const openShare = (key) => {
    const url = encodeURIComponent(shareUrl.value);
    const text = encodeURIComponent(state.name || 'ReferyApp');
    const body = encodeURIComponent(`${state.name || 'ReferyApp'} - ${shareUrl.value}`);

    if (key === 'instagram') {
        copyShareUrl(false);
        trackShareEvent('instagram');
        window.open('https://www.instagram.com/', '_blank', 'noopener,noreferrer');
        return;
    }

    const map = {
        whatsapp: `https://wa.me/?text=${body}`,
        x: `https://x.com/intent/tweet?text=${text}&url=${url}`,
        facebook: `https://www.facebook.com/sharer/sharer.php?u=${url}`,
        telegram: `https://t.me/share/url?url=${url}&text=${text}`,
        email: `mailto:?subject=${encodeURIComponent(shareCopy.value.subject)}&body=${body}`,
    };

    const target = map[key];
    if (!target) {
        return;
    }

    trackShareEvent(key);
    window.open(target, '_blank', 'noopener,noreferrer');
};

onMounted(() => {
    const linkNodes = linksWrapRef.value
        ? Array.from(linksWrapRef.value.querySelectorAll('.public-link-item'))
        : [];

    const timeline = gsap.timeline({ defaults: { ease: 'power2.out' } });

    timeline
        .from(cardRef.value, { opacity: 0, y: 16, duration: 0.4 })
        .from(headerRef.value, { opacity: 0, y: -10, duration: 0.3 }, '-=0.2')
        .from(profileWrapRef.value, { opacity: 0, scale: 0.94, duration: 0.3 }, '-=0.14')
        .from(textBlockRef.value, { opacity: 0, y: 10, duration: 0.3 }, '-=0.16');

    if (linkNodes.length) {
        timeline.from(linkNodes, {
            opacity: 0,
            y: 8,
            duration: 0.24,
            stagger: 0.04,
            clearProps: 'opacity,transform',
        }, '-=0.14');
    }

    nextTick(() => {
        document.addEventListener('click', handleClickOutside);
    });

    qrPublicUrl.value = buildCurrentPublicUrl();
    regenerateQrCode();
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

watch(isMenuOpen, async (opened) => {
    if (!opened) {
        return;
    }

    await nextTick();

    const items = fabMenuRef.value
        ? Array.from(fabMenuRef.value.querySelectorAll('.fab-menu-item'))
        : [];

    if (!items.length) {
        return;
    }

    gsap.from(items, {
        opacity: 0,
        y: 8,
        duration: 0.22,
        stagger: 0.04,
        ease: 'power2.out',
        clearProps: 'opacity,transform',
    });
});

const fetchAvailability = async () => {
    if (bookingForm.request_type !== 'appointment' || !bookingForm.appointment_date) {
        availableSlotsRaw.value = [];
        return;
    }

    loadingSlots.value = true;
    try {
        const params = new URLSearchParams({
            date: bookingForm.appointment_date,
        });
        if (bookingForm.product_id) {
            params.set('service_id', bookingForm.product_id);
        }

        const response = await fetch(`/${state.username}/appointments/availability?${params.toString()}`, {
            headers: { Accept: 'application/json' },
        });
        const payload = await response.json();
        availableSlotsRaw.value = Array.isArray(payload.slots) ? payload.slots : [];

        if (bookingForm.appointment_time && !availableSlots.value.some((slot) => slot.time === bookingForm.appointment_time)) {
            bookingForm.appointment_time = '';
        }
    } catch (_error) {
        availableSlotsRaw.value = [];
    } finally {
        loadingSlots.value = false;
    }
};

const submitPublicBooking = () => {
    bookingForm.post(`/${state.username}/appointments`, {
        preserveScroll: true,
        onSuccess: () => {
            bookingForm.reset();
            bookingForm.request_type = 'contact';
            bookingForm.appointment_date = new Date().toISOString().slice(0, 10);
            bookingForm.appointment_time = '';
            fetchAvailability();
            closeOffcanvas();
        },
    });
};

watch(
    () => [bookingForm.request_type, bookingForm.appointment_date, bookingForm.product_id],
    () => {
        if (bookingForm.request_type !== 'appointment') {
            bookingForm.appointment_time = '';
            availableSlotsRaw.value = [];
            return;
        }
        fetchAvailability();
    },
    { immediate: true }
);

watch(
    () => [activeOption.value, state.username],
    () => {
        if (activeOption.value !== 'qr') {
            return;
        }

        qrPublicUrl.value = buildCurrentPublicUrl();
        regenerateQrCode();
    }
);
</script>

<style scoped>
.public-links-scroll {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.public-links-scroll::-webkit-scrollbar {
    width: 0;
    height: 0;
    display: none;
}
</style>
