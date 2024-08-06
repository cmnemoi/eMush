import { ActionTree, GetterTree, MutationTree } from "vuex";

type BasicPopUp = {
    isOpen: boolean;
}

const state = {
    newRulesPopUp: {
        isOpen: false
    },
    skillSelectionPopUp: {
        isOpen: false
    },
    learnSkillPopUp: {
        isOpen: false
    }
};

const getters: GetterTree<any, any> = {
    newRulesPopUp: (state: any): BasicPopUp => {
        return state.newRulesPopUp;
    },
    skillSelectionPopUp: (state: any): BasicPopUp => {
        return state.skillSelectionPopUp;
    },
    learnSkillPopUp: (state: any): BasicPopUp => {
        return state.learnSkillPopUp;
    }
};

const mutations: MutationTree<any> = {
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
    },
    openLearnSkillPopUp(state) {
        state.learnSkillPopUp.isOpen = true;
    },
    closeLearnSkillPopUp(state) {
        state.learnSkillPopUp.isOpen = false;
    }
};

const actions: ActionTree<any, any> = {
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
    },
    openLearnSkillPopUp({ commit }) {
        commit('openLearnSkillPopUp');
    },
    closeLearnSkillPopUp({ commit }) {
        commit('closeLearnSkillPopUp');
    }
};

export const popup = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
