import PlayerService from "@/services/player.service";


const state =  {
    loading: false,
    player: null,
    target: null
};

const getters = {
    getPlayer: (state) => {
        return state.player;
    },
    loading: (state) => {
        return state.loading;
    },
    getTarget: (state) => {
        return state.target;
    }
};

const actions = {
    selectTarget({ commit }, { target }) {
        commit('setTarget', target);
    },
    storePlayer({ commit }, { player }) {
        commit('updatePlayer', player);
    },
    async loadPlayer({ commit }, { playerId }) {
        commit('setLoading', true);
        try {
            const player = await PlayerService.loadPlayer(playerId);
            commit('updatePlayer', player);

            return true;
        } catch (e) {
            return false;
        }
    },
    async reloadPlayer({ state, dispatch }) {
        return dispatch("loadPlayer", { playerId: state.player.id });
    },
    setLoading({ commit }) {
        commit('setLoading', true);
    }
};

const mutations = {
    setTarget(state, target) {
        state.target = target;
    },
    setLoading(state, newValue) {
        state.loading = newValue;
    },
    updatePlayer(state, player) {
        state.target = player;
        state.player = player;
        state.loading = false;
    }
};

export const player = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
