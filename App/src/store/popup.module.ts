import { ActionTree, GetterTree, MutationTree } from "vuex";

type BasicPopUp = {
    title?: string;
    description?: string;
    isOpen: boolean;
}

type PlayerNotificationPopUp = {
    title?: string;
    subTitle?: string;
    description: string;
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
    },
    playerNotificationPopUp: {
        title: '',
        subTitle: '',
        description: '',
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
    },
    playerNotificationPopUp: (state: any): PlayerNotificationPopUp => {
        return state.playerNotificationPopUp;
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
    },
    openPlayerNotificationPopUp(state, player) {
        const notification = player?.notification;
        if (!notification) {
            return;
        }

        state.playerNotificationPopUp.title = notification.title;
        state.playerNotificationPopUp.subTitle = notification.subTitle;
        state.playerNotificationPopUp.description = notification.description;
        state.playerNotificationPopUp.isOpen = true;
    },
    closePlayerNotificationPopUp(state) {
        state.playerNotificationPopUp.isOpen = false;
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
    },
    openPlayerNotificationPopUp({ commit }, { player }) {
        commit('openPlayerNotificationPopUp', player);
    },
    closePlayerNotificationPopUp({ commit }) {
        commit('closePlayerNotificationPopUp');
    }
};

export const popup = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
