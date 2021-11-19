import { createApp } from 'vue';
import App from './App.vue';
import Title from './Title.vue';
import './assets/scss/main.scss';
import ApiService from "./services/api.service";
import { TokenService } from "./services/storage.service";
import store from './store';
import router from './router';
import { createI18n } from 'vue-i18n';
import { messages, defaultLocale } from '@/i18n';


// Set the base URL of the API
ApiService.init(process.env.VUE_APP_API_URL as string);

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

createApp(App)
    .use(store)
    .use(router)
    .use(i18n)
    .mount('#app');

createApp(Title)
    .use(i18n)
    .mount('#title');

