import axios, { AxiosPromise, AxiosResponse, AxiosRequestConfig } from 'axios';
import { uuid } from 'vue3-uuid';
import store from '../store';

export type SuccessReponse = AxiosResponse<{ detail: string }>;

const ApiService = {
    _errorInterceptor: 0,

    init(baseURL: string): void {
        axios.defaults.baseURL = baseURL;
        axios.defaults.withCredentials = true;
    },

    get(resource: string, config?: AxiosRequestConfig): Promise<AxiosResponse> {
        this.addCorrelationId();
        return axios.get(resource, config);
    },

    post<T>(resource: string, data?: T, options?: AxiosRequestConfig): Promise<AxiosResponse> {
        this.addCorrelationId();
        return axios.post(resource, data, options);
    },

    put<T>(resource: string, data?: T): Promise<AxiosResponse> {
        this.addCorrelationId();
        return axios.put(resource, data);
    },

    patch<T>(resource: string, data?: T, _config?: AxiosRequestConfig): Promise<AxiosResponse> {
        this.addCorrelationId();
        return axios.patch(resource, data, { headers: { 'Content-Type' : 'application/merge-patch+json' } });
    },

    delete(resource: string): Promise<AxiosResponse> {
        this.addCorrelationId();
        return axios.delete(resource);
    },

    mountErrorInterceptor(): void {
        this._errorInterceptor = axios.interceptors.response.use(
            (response) => {
                return response;
            },
            async (error) => {
                if (error.request.status === 401) {
                    await store.dispatch('auth/logout');
                } else if (error.request.status === 503) {
                    // Set global maintenance flag; App.vue will render MaintenancePage
                    store.commit('admin/setMaintenanceStatus', true);
                    throw error;
                } else {
                    // If error was not 401, inform user with a pop-up before rejecting
                    await store.dispatch('error/setError', error);
                    throw error;
                }
            }
        );
    },

    unmountErrorInterceptor(): void {
        // Eject the interceptor
        axios.interceptors.response.eject(this._errorInterceptor);
    },

    /**
     * Perform a custom Axios request.
     *
     * data is an object containing the following properties:
     *  - method
     *  - url
     *  - data ... request payload
     *  - auth (optional)
     *    - username
     *    - password
     **/
    customRequest<T>(data: AxiosRequestConfig): AxiosPromise<T> {
        return axios(data);
    },

    addCorrelationId(){
        axios.defaults.headers.common["X-Request-Id"] = `${uuid.v4()}`;
    }
};

export default ApiService;
