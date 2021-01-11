const state =  {
    error: null
};

const actions = {
    setError({ commit }, error) {
        commit('setError', error);
    },
    clearError({ commit }) {
        commit('resetError');
    }
};

const mutations = {
    setError(state, error) {
        state.error = { message: error.message };
    },
    resetError(state) {
        state.error = null;
    }
};

export const error = {
    namespaced: true,
    state,
    getters: {},
    actions,
    mutations
};
