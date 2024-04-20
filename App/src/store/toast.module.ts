import { ActionTree, GetterTree, MutationTree } from "vuex";

type ToastType = 'success' | 'error' | 'info' | 'warning';

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
    openSuccessToast(state, title: string) {
        state.toast.isOpen = true;
        state.toast.type = 'success';
        state.toast.title = title;
    },
    closeToast(state) {
        state.toast.isOpen = false;
    }
};

const actions: ActionTree<any, any> = {
    openSuccessToast({ commit }, title: string) {
        commit('openSuccessToast', title);
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
