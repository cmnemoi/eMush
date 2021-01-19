import { rooms } from "@/traductions";

const state =  {
    userLanguage: "fr"
};

const getters = {
    roomsTrad(state) {
        return rooms[state.userLanguage] || rooms["en"];
    }
};

const actions = {
    setUserLanguage({ commit }, language) {
        commit('setUserLanguage', language);
    }
};

const mutations = {
    setUserLanguage(state, language) {
        state.userLanguage = language;
    }
};

export const traduction = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
