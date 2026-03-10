<template>
    <div
        v-motion
        :while-hover="{ y: -2 }"
        class="rounded-xl bg-white p-6 shadow-sm transition hover:shadow-md"
    >
        <slot />
    </div>
</template>
