import { createApp } from 'vue';
import App from './App.vue';
import './assets/scss/main.scss';
import ApiService from "./services/api.service";
import { TokenService } from "./services/storage.service";
import store from './store';
import router from './router';

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

app.mount('#app');
