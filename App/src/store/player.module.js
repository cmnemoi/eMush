import PlayerService from "@/services/player.service";
import {AuthenticationError} from "@/services/user.service";


const state =  {
    loading: false,
    player: null,
    target: null
};

const getters = {
    getPlayer: (state) => {
        return state.player
    },
    loading: (state) => {
        return state.loading
    },
    getTarget: (state) => {
        return state.target
    }
};

const actions = {
    async selectTarget({ commit }, {target}) {
        commit('selectTarget', target)
    },
    async storePlayer({ commit }, {player}) {
        commit('loadSuccess', player)
    },
    async loadPlayer({ commit }, {playerId}) {
        commit('loadRequest');
        try {
            const player = await PlayerService.loadPlayer(playerId);
            commit('loadSuccess', player)

            return true
        } catch (e) {
            if (e instanceof AuthenticationError) {
                commit('loadError', {errorCode: e.errorCode, errorMessage: e.message})
            }

            return false
        }
    },
    async reloadPlayer({ commit, state }) {
        commit('reloadRequest');
        try {
            const player = await PlayerService.loadPlayer(state.player.id);
            commit('loadSuccess', player)
            return true
        } catch (e) {
            if (e instanceof AuthenticationError) {
                commit('loadError', {errorCode: e.errorCode, errorMessage: e.message})
            }

            return false
        }
    },
};

const mutations = {
    selectTarget(state, target) {
        state.target = target;
    },
    loadRequest(state) {
        state.player = null;
        state.loading = true;
    },

    reloadRequest(state) {
        state.loading = true;
    },

    loadSuccess(state, player) {
        state.target = player;
        state.player = player;
        state.loading = false;
        },

    loadError(state, {errorCode, errorMessage}) {
        state.playerErrorCode = errorCode;
        state.playerError = errorMessage
    },
};

export const player = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};