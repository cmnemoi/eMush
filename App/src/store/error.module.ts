import { ActionTree, GetterTree, MutationTree } from "vuex";
import { AxiosError } from "axios";

export type Error = {
    message: string;
    status: number;
    statusText: string;
    request: {
        url: string;
        params: unknown;
        method: string;
    };
    response: {
        details: string;
        class: string;
    };
};

interface ErrorResponseData {
    '@type'?: string;
    'hydra:description'?: string;
    error?: string;
    detail?: string;
    class?: string;
}

interface ErrorModuleState {
    error: Error | null;
}

const state: ErrorModuleState =  {
    error: null
};

const actions: ActionTree<ErrorModuleState, ErrorModuleState> = {
    setError({ commit }, error: AxiosError<ErrorResponseData>): void {
        commit('setError', error);
    },
    clearError({ commit }): void {
        commit('resetError');
    }
};

const getters: GetterTree<ErrorModuleState, ErrorModuleState> = {
    getError: (state): Error | null => {
        return state.error;
    }
};

const mutations: MutationTree<ErrorModuleState> = {
    setError(state, error: AxiosError<ErrorResponseData>): void {
        const isHydraError = error.response?.data['@type'] === 'hydra:Error';

        let errorDetails = '';
        if (isHydraError) {
            errorDetails = error.response?.data['hydra:description'] ?? '';
        } else {
            errorDetails = error.response?.data?.error ?? error.response?.data?.detail ?? (error as unknown as string);
        }

        state.error = {
            message: error.message,
            status: error.request?.status,
            statusText: error.request?.statusText,
            request: {
                url: error.config?.url,
                params: error.response?.config?.data,
                method: error.config?.method
            },
            response: {
                details: errorDetails,
                class: error.response?.data?.class ?? ''
            }
        };
    },
    resetError(state): void {
        state.error = null;
    }
};

export const error = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
