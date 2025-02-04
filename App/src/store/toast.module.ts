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
    openErrorToast(state, title: string) {
        state.toast.isOpen = true;
        state.toast.type = 'error';
        state.toast.title = title;
    },
    openInfoToast(state, title: string) {
        state.toast.isOpen = true;
        state.toast.type = 'info';
        state.toast.title = title;
    },
    openSuccessToast(state, title: string) {
        state.toast.isOpen = true;
        state.toast.type = 'success';
        state.toast.title = title;
    },
    openNewsToast(state, title: string) {
        state.toast.isOpen = true;
        state.toast.type = 'news';
        state.toast.title = title;
    },
    closeToast(state) {
        state.toast.isOpen = false;
    }
};

const actions: ActionTree<any, any> = {
    openErrorToast({ commit }, title: string) {
        commit('openErrorToast', title);
    },
    openInfoToast({ commit }, title: string) {
        commit('openInfoToast', title);
    },
    openSuccessToast({ commit }, title: string) {
        commit('openSuccessToast', title);
    },
    openNewsToast({ commit }, title: string) {
        commit('openNewsToast', title);
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
