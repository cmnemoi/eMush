import { defaultLocale, messages, normalizeLocale } from '@/i18n';
import { VueHeadMixin, createHead } from '@unhead/vue/client';
import { createApp } from 'vue';
import { createI18n } from 'vue-i18n';
import { plugin as VueTippy } from 'vue-tippy';
import UUID from "vue3-uuid";
import App from './App.vue';
import './assets/scss/main.scss';
import { mixin } from './mixin/mixin';
import router from './router';
import ApiService from "./services/api.service";
import { LocaleService } from './services/locale.service';
import store from './store';
import { createServiceWorkerListener } from './store/plugins/service.worker.listener';

// Set the base URL of the API
ApiService.init(import.meta.env.VITE_APP_API_URL!);

// If error, act accordingly (401 refreshes token, others raise error)
ApiService.mountErrorInterceptor();

// Translation with i18n
export const i18n = createI18n({
    messages,
    locale: LocaleService.getLocale() || normalizeLocale(defaultLocale),
    fallbackLocale: defaultLocale
});

const vueTippyProps = {
    directive: 'tippy',
    component: 'Tippy',
    defaultProps: {
        placement: 'bottom-start',
        arrow: false,
        followCursor: true,
        allowHTML: true,
        inlinePositioning: true,
        animation: 'fade',
        delay: [120, 0],
        duration: [150, 300],
        hideOnClick: true,
        theme:'mush'
    }
};

const app = createApp(App);
const head = createHead();
app.mixin(VueHeadMixin);

app.use(store)
    .use(router)
    .use(i18n)
    .mixin(mixin)
    .use(VueTippy, vueTippyProps)
    .use(UUID)
    .use(head);

if ('serviceWorker' in navigator) {
    app.use(createServiceWorkerListener(store, navigator.serviceWorker));
}

app.mount('#app');



