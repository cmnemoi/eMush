import { ActionTree, MutationTree } from "vuex";

const state =  {
    error: null
};

const actions: ActionTree<any, any> = {
    setError({ commit }, error: any): void {
        commit('setError', error);
    },
    clearError({ commit }): void {
        commit('resetError');
    }
};

const getters = {
    getError: (state: any): any => {
        return state.error;
    }
};

const mutations: MutationTree<any> = {
    setError(state: any, error: any): void {
        const isHydraError = error.response?.data['@type'] === 'hydra:Error';
        
        let errorDetails = '';
        if (isHydraError) {
            errorDetails = error.response?.data['hydra:description'];
        } else {
            errorDetails = error.response?.data?.error ?? error.response?.data?.detail;
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
                class: error.response?.data?.class
            }
        };
    },
    resetError(state: any): void {
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
