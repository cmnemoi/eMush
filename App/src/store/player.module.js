import PlayerService from "@/services/player.service";
import { AuthenticationError } from "@/services/user.service";


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
    async selectTarget({ commit }, { target }) {
        commit('setTarget', target);
    },
    async storePlayer({ commit }, { player }) {
        commit('updatePlayer', player);
    },
    async loadPlayer({ commit }, { playerId }) {
        commit('setLoading', true);
        try {
            const player = await PlayerService.loadPlayer(playerId);
            commit('updatePlayer', player);

            return true;
        } catch (e) {
            if (e instanceof AuthenticationError) {
                commit('setError', { errorCode: e.errorCode, errorMessage: e.message });
            }

            return false;
        }
    },
    async reloadPlayer({ state, dispatch }) {
        return dispatch("loadPlayer", { playerId: state.player.id });
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
    },
    setError(state, { errorCode, errorMessage }) {
        state.playerErrorCode = errorCode;
        state.playerError = errorMessage;
    }
};

export const player = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
