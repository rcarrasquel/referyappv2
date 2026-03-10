import '../css/app.css';
import './bootstrap';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { MotionPlugin } from '@vueuse/motion';

createInertiaApp({
    title: () => 'ReferyApp',
    resolve: async (name) => {
        const page = await resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue'));
        
        // Configurar layout persistente solo para páginas privadas
        if (page.default.layout === undefined && !name.startsWith('Auth/') && !name.startsWith('Public/')) {
            const AppLayout = (await import('./components/layout/AppLayout.vue')).default;
            page.default.layout = AppLayout;
        }
        
        return page;
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin);
        app.use(MotionPlugin);
        app.mount(el);
    },
    progress: {
        color: '#4f46e5',
        showSpinner: false,
    },
});
