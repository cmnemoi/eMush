import { ActionTree, GetterTree, MutationTree } from "vuex";

type BasicPopUp = {
    title?: string;
    description?: string;
    isOpen: boolean;
}

type PlayerHistoryPopUpData = {
    isOpen: boolean;
    playerName: string | null;
    gains: string[] | null;
    highlights: string[] | null;
};

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
    },
    playerHistoryPopUp: {
        isOpen: false,
        playerName: null,
        gains: null,
        highlights: null
    } as PlayerHistoryPopUpData
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
    },
    playerHistoryPopUp: (state: any): PlayerHistoryPopUpData => {
        return state.playerHistoryPopUp;
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
    },
    openPlayerHistoryPopUp(state, payload: { playerName: string, gains: string[], highlights: string[] }) {
        state.playerHistoryPopUp.isOpen = true;
        state.playerHistoryPopUp.playerName = payload.playerName;
        state.playerHistoryPopUp.gains = payload.gains;
        state.playerHistoryPopUp.highlights = payload.highlights;
    },
    closePlayerHistoryPopUp(state) {
        state.playerHistoryPopUp.isOpen = false;
        state.playerHistoryPopUp.playerName = null;
        state.playerHistoryPopUp.gains = null;
        state.playerHistoryPopUp.highlights = null;
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
    },
    openPlayerHistoryPopUp({ commit }, payload: { playerName: string, gains: string[], highlights: string[] }) {
        commit('openPlayerHistoryPopUp', payload);
    },
    closePlayerHistoryPopUp({ commit }) {
        commit('closePlayerHistoryPopUp');
    }
};

export const popup = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
