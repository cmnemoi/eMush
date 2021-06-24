import { ActionTree, MutationTree } from "vuex";

const state =  {
    error: null
};

const actions: ActionTree<any, any> = {
    setError({ commit }, error) {
        commit('setError', error);
    },
    clearError({ commit }) {
        commit('resetError');
    }
};

const mutations: MutationTree<any> = {
    setError(state, error) {
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
                details: error.response?.data?.detail,
                class: error.response?.data?.class
            }
        };
    },
    resetError(state) {
        state.error = null;
    }
};

export const error = {
    namespaced: true,
    state,
    getters: {},
    actions,
    mutations
};
