import { ActionTree, MutationTree } from "vuex";

interface StoreState {
    error: ErrorModule | null
};

interface ErrorModule {
    message: string;
    status: string
    statusText: string;
    request: Record<string, unknown>;
    response: Record<string, unknown>
}
const state =  {
    error: null
} as StoreState;

const actions: ActionTree<StoreState, StoreState> = {
    setError({ commit }, error): void {
        commit('setError', error);
    },
    clearError({ commit }): void {
        commit('resetError');
    }
};

const mutations: MutationTree<StoreState> = {
    setError(state, error): void {
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
    resetError(state): void {
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
