import { ActionTree, GetterTree, MutationTree } from "vuex";

type ToastType = 'success' | 'error' | 'info' | 'warning' | 'news';

type Toast = {
    isOpen: boolean;
    title: string;
    type: ToastType;
}

const state = {
    toast: {
        isOpen: false,
        title: '',
        type: 'info' as ToastType
    }
};

const getters: GetterTree<any, any> = {
    toast(state): Toast {
        return state.toast;
    },
    isOpen(state): boolean {
        return state.toast.isOpen;
    },
    type(state): ToastType {
        return state.toast.type;
    },
    title(state): string {
        return state.toast.title;
    }
};

const mutations: MutationTree<any> = {
    openToast(state, payload: { type: ToastType, title: string }) {
        state.toast.isOpen = true;
        state.toast.type = payload.type;
        state.toast.title = payload.title;
    },
    closeToast(state) {
        state.toast.isOpen = false;
    }
};

const actions: ActionTree<any, any> = {
    openErrorToast({ commit }, title: string) {
        commit('openToast', { type: 'error', title });
    },
    openInfoToast({ commit }, title: string) {
        commit('openToast', { type: 'info', title });
    },
    openSuccessToast({ commit }, title: string) {
        commit('openToast', { type: 'success', title });
    },
    openNewsToast({ commit }, title: string) {
        commit('openToast', { type: 'news', title });
    },
    openWarningToast({ commit }, title: string) {
        commit('openToast', { type: 'warning', title });
    },
    closeToast({ commit }) {
        commit('closeToast');
    }
};

export const toast = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
