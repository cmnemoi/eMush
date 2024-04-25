import axios, { AxiosPromise, AxiosResponse } from 'axios';
import { TokenService } from './storage.service';
import store from '../store';
import { uuid } from 'vue3-uuid';

export type SuccessReponse = {
    config: any;
    data: {
        detail: string;
    },
    headers: any;
    request: any;
    status: integer;
    statusText: string;
};

export type ErrorResponse = {
    config: any;
    data: {
        class: string|null;
        detail: string;
        status: integer;
        title: string;
        trace: string|null;
    },
    headers: any;
    request: any;
    status: integer;
    statusText: string;
};

const ApiService = {

    _errorInterceptor: 0,

    init(baseURL: string): void {
        axios.defaults.baseURL = baseURL;
    },

    setHeader(): void {
        axios.defaults.headers.common["Authorization"] = `Bearer ${TokenService.getToken()}`;
    },

    removeHeader(): void {
        axios.defaults.headers.common = {};
    },

    get(resource: string, params?: Record<string, unknown>): Promise<AxiosResponse> {
        this.addCorrelationId();
        return axios.get(resource, params);
    },

    post(resource: string, data?: Record<string, unknown>, options?: Record<string, unknown>): Promise<AxiosResponse> {
        this.addCorrelationId();
        return axios.post(resource, data, options);
    },

    put(resource: string, data?: any): Promise<AxiosResponse> {
        this.addCorrelationId();
        return axios.put(resource, data);
    },

    patch(resource: string, data?: any, config?: any): Promise<AxiosResponse> {
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
                    //@TODO: use refresh token when available
                    await store.dispatch('auth/logout');
                    // if (error.config.url.includes('/oauth/v2/token')) {
                    //     // Refresh token has failed. Logout the user
                    //     await store.dispatch('auth/logout');
                    //     throw error
                    // } else {
                    //     // Refresh the access token
                    //     try{
                    //         await store.dispatch('auth/refreshToken');
                    //         // Retry the original request
                    //         return this.customRequest({
                    //             method: error.config.method,
                    //             url: error.config.url,
                    //             data: error.config.data
                    //         })
                    //     } catch (e) {
                    //         // Refresh has failed - reject the original request
                    //         throw error
                    //     }
                    // }
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
    customRequest(data: Record<string, unknown>): AxiosPromise<any> {
        return axios(data);
    },

    addCorrelationId(){
        axios.defaults.headers.common["X-Request-Id"] = `${uuid.v4()}`;
    }
};

export default ApiService;
