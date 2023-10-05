import { createApp } from 'vue';
import App from './App.vue';
import './assets/scss/main.scss';
import ApiService from "./services/api.service";
import { TokenService } from "./services/storage.service";
import store from './store';
import router from './router';
import { createI18n } from 'vue-i18n';
import { messages, defaultLocale } from '@/i18n';
import { mixin } from './mixin/mixin';
import { plugin as VueTippy } from 'vue-tippy';
import UUID from "vue3-uuid";

// Set the base URL of the API
ApiService.init(process.env.VUE_APP_API_URL!);

// If token exists set header
if (TokenService.getToken()) {
    ApiService.setHeader();
}

// If error, act accordingly (401 refreshes token, others raise error)
ApiService.mountErrorInterceptor();

// Translation with i18n
const i18n = createI18n({
    messages,
    locale: navigator.language,
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
        delay: [400, 0],
        duration: [150, 100],
        hideOnClick: false,
        theme:'mush'
    }
};

createApp(App)
    .use(store)
    .use(router)
    .use(i18n)
    .mixin(mixin)
    .use(VueTippy, vueTippyProps)
    .use(UUID)
    .mount('#app');


