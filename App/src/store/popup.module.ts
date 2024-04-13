import { ActionTree, GetterTree, MutationTree } from "vuex";

type BasicPopUp = {
    isOpen: boolean;
}

const state = {
    reportPopup: {
        isOpen: false
    },
    newRulesPopUp: {
        isOpen: false
    },
};

const getters: GetterTree<any, any> = {
    reportPopup: (state: any): BasicPopUp => {
        return state.reportPopup;
    },
    newRulesPopUp: (state: any): BasicPopUp => {
        return state.newRulesPopUp;
    }
};

const mutations: MutationTree<any> = {
    openReportPopup(state) {
        state.reportPopup.isOpen = true;
    },
    closeReportPopup(state) {
        state.reportPopup.isOpen = false;
    },
    openNewRulesPopUp(state) {
        state.newRulesPopUp.isOpen = true;
    },
    closeNewRulesPopUp(state) {
        state.newRulesPopUp.isOpen = false;
    }

};

const actions: ActionTree<any, any> = {
    openReportPopup({ commit }) {
        commit('openReportPopup');
    },
    closeReportPopup({ commit }) {
        commit('closeReportPopup');
    },
    openNewRulesPopUp({ commit }) {
        commit('openNewRulesPopUp');
    },
    closeNewRulesPopUp({ commit }) {
        commit('closeNewRulesPopUp');
    }
};

export const popup = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
