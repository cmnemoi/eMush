import PlayerService from "@/services/player.service";
import { ActionTree, GetterTree, MutationTree } from "vuex";


const state =  {
    loading: false,
    player: null
};

const getters: GetterTree<any, any> = {
    isLoading: (state) => {
        return state.loading;
    },
    player: (state) => {
        return state.player;
    }
};

const actions: ActionTree<any, any> = {
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
    setLoading({ commit }, { loading }) {
        commit('setLoading', loading);
    }
};

const mutations : MutationTree<any> = {
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
