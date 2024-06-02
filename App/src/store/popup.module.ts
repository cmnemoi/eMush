import { ActionTree, GetterTree, MutationTree } from "vuex";

type BasicPopUp = {
    isOpen: boolean;
}

const state = {
    newRulesPopUp: {
        isOpen: false
    }
};

const getters: GetterTree<any, any> = {
    newRulesPopUp: (state: any): BasicPopUp => {
        return state.newRulesPopUp;
    }
};

const mutations: MutationTree<any> = {
    openNewRulesPopUp(state) {
        state.newRulesPopUp.isOpen = true;
    },
    closeNewRulesPopUp(state) {
        state.newRulesPopUp.isOpen = false;
    }

};

const actions: ActionTree<any, any> = {
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
