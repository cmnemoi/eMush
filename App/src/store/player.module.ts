import PlayerService from "@/services/player.service";
import { ActionTree, GetterTree, MutationTree } from "vuex";
import { Player } from "@/entities/Player";

interface StoreStatePlayer {
    player: Player | null;
	loading: boolean;
}

const state =  {
    loading: false,
    player: null
} as StoreStatePlayer;

const getters: GetterTree<StoreStatePlayer, StoreStatePlayer> = {
    isLoading: (state: StoreStatePlayer): boolean => {
        return state.loading;
    },
    player: (state: StoreStatePlayer): Player|null => {
        return state.player;
    }
};

const actions: ActionTree<StoreStatePlayer, StoreStatePlayer> = {
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
        return dispatch("loadPlayer", { playerId: state.player?.id });
    },
    setLoading({ commit }, { loading }) {
        commit('setLoading', loading);
    }
};

const mutations : MutationTree<StoreStatePlayer> = {
    setLoading(state: StoreStatePlayer, newValue: boolean): void {
        state.loading = newValue;
    },
    updatePlayer(state: StoreStatePlayer, player: Player): void {
        state.player = player;
        state.loading = false;
    },
    errorUpdatePlayer(state: StoreStatePlayer): void {
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
