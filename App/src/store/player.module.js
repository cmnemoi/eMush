import PlayerService from "@/services/player.service";


const state =  {
    loading: false,
    player: null
};

const getters = {
    isLoading: (state) => {
        return state.loading;
    },
    player: (state) => {
        return state.player;
    }
};

const actions = {
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
            commit('errorUpdatePlayer');
            return false;
        }
    },
    async reloadPlayer({ state, dispatch }) {
        return dispatch("loadPlayer", { playerId: state.player.id });
    },
    setLoading({ commit }, {loading}) {
        commit('setLoading', loading);
    }
};

const mutations = {
    setLoading(state, newValue) {
        state.loading = newValue;
    },
    updatePlayer(state, player) {
        state.player = player;
        state.loading = false;
    },
    errorUpdatePlayer(state) {
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
