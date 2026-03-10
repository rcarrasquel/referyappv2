import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export const useLocale = () => {
    const page = usePage();

    const locale = computed(() => (page.props.locale === 'es' ? 'es' : 'en'));
    const isEnglish = computed(() => locale.value === 'en');

    const t = (messages) => messages[locale.value] ?? messages.en;

    const toggleLocale = () => {
        router.post(
            '/locale',
            { locale: isEnglish.value ? 'es' : 'en' },
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            }
        );
    };

    return {
        locale,
        isEnglish,
        t,
        toggleLocale,
    };
};
