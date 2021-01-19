import { rooms } from "@/traductions";

const state =  {
    userLanguage: localStorage.getItem('userLanguage') || "fr"
};

const untranslatedValuesHandler = {
    // always return the key if the field isn't translated
    get(traductions, key) {
        switch (typeof traductions[key]) {
        case "undefined":
            return new Proxy({}, { get: () => key });
        case "object":
            return new Proxy(traductions[key], { get: (field, subKey) => field[subKey] || key });
        case "string":
            return traductions[key];
        default:
            return key;
        }
    }
};

const getters = {
    roomsTrad(state) {
        return new Proxy(rooms[state.userLanguage], untranslatedValuesHandler);
    }
};

const actions = {
    setUserLanguage({ commit }, language) {
        localStorage.setItem('userLanguage', language);
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
