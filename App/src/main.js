import { createApp } from 'vue'
import App from './App.vue'
import './assets/scss/main.scss';
import ApiService from "./services/api.service";
import {TokenService} from "./services/storage.service";
import store from './store'

// Set the base URL of the API
ApiService.init(process.env.VUE_APP_BASE_URL);

// If token exists set header
if (TokenService.getToken()) {
    ApiService.setHeader()
}
// If 401 try to refresh token
ApiService.mount401Interceptor();


const app = createApp(App)

app.use(store)

app.mount('#app');
