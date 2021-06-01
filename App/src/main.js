import { createApp } from 'vue';
import App from './App.vue';
import './assets/scss/main.scss';
import ApiService from "./services/api.service";
import { TokenService } from "./services/storage.service";
import store from './store';
import router from './router';
import VueTippy from 'vue-tippy';


// Set the base URL of the API
ApiService.init(process.env.VUE_APP_API_URL);

// If token exists set header
if (TokenService.getToken()) {
    ApiService.setHeader();
}

// If error, act accordingly (401 refreshes token, others raise error)
ApiService.mountErrorInterceptor();


const app = createApp(App);

app.use(store);
app.use(router);
app.use(
    VueTippy,
    // optional
    {
        directive: 'tippy', // => v-tippy
        component: 'tippy', // => <tippy/>
        componentSingleton: 'tippy-singleton', // => <tippy-singleton/>

        defaultProps: {
            allowHTML: true,
            theme: 'mush',
            maxWidth: 280,
            followCursor: true,
            inlinePositioning: true,
            placement: 'bottom-start',
            duration: [null, 50],
        },
    }
);

app.mount('#app');
