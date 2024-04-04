import { ActionTree, GetterTree, MutationTree } from "vuex";

interface ReportPopup {
    isOpen: boolean;
}

const state = {
    reportPopup: {
        isOpen: false
    } as ReportPopup
};

const getters: GetterTree<any, any> = {
    reportPopup: (state: any): ReportPopup => {
        return state.reportPopup;
    }
};

const mutations: MutationTree<any> = {
    openReportPopup(state) {
        state.reportPopup.isOpen = true;
    },
    closeReportPopup(state) {
        state.reportPopup.isOpen = false;
    }

};

const actions: ActionTree<any, any> = {
    openReportPopup({ commit }) {
        commit('openReportPopup');
    },
    closeReportPopup({ commit }) {
        commit('closeReportPopup');
    }
};

export const popup = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
