const state =  {
    userLanguage: "fr"
};

const getters = {};

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
