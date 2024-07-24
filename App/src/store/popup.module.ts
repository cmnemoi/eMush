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
    skillSelectionPopUp: {
        isOpen: false
    }
};

const getters: GetterTree<any, any> = {
    reportPopup: (state: any): BasicPopUp => {
        return state.reportPopup;
    },
    newRulesPopUp: (state: any): BasicPopUp => {
        return state.newRulesPopUp;
    },
    skillSelectionPopUp: (state: any): BasicPopUp => {
        return state.skillSelectionPopUp;
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
    },
    openSkillSelectionPopUp(state) {
        state.skillSelectionPopUp.isOpen = true;
    },
    closeSkillSelectionPopUp(state) {
        state.skillSelectionPopUp.isOpen = false;
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
    },
    openSkillSelectionPopUp({ commit }) {
        commit('openSkillSelectionPopUp');
    },
    closeSkillSelectionPopUp({ commit }) {
        commit('closeSkillSelectionPopUp');
    }
};

export const popup = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
