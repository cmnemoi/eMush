import PlayerService from "@/services/player.service";
import { ActionTree, GetterTree, MutationTree } from "vuex";
import { Player } from "@/entities/Player";
import { Item } from "@/entities/Item";


const state =  {
    loading: false,
    player: null,
    selectedItem: null
};

const getters: GetterTree<any, any> = {
    isLoading: (state: any): boolean => {
        return state.loading;
    },
    player: (state: any): Player|null => {
        return state.player;
    },
    selectedItem: (state: any): Item|null => {
        return state.selectedItem;
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
            commit('updateSelectedItem');
            this.dispatch("daedalus/loadAlerts", { player: player });
            this.dispatch("daedalus/loadMinimap", { player: player });
            this.dispatch("room/setRoom", { room: player?.room });
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
    },
    selectTarget({ commit }, { target }) {
        commit('setSelectedItem', target);
    },
};

const mutations : MutationTree<any> = {
    setLoading(state: any, newValue: boolean): void {
        state.loading = newValue;
    },
    updatePlayer(state: any, player: Player): void {
        state.player = player;
        state.loading = false;
    },
    errorUpdatePlayer(state: any): void {
        state.loading = false;
    },
    setSelectedItem(state, target: Item | null) {
        state.selectedItem = target;
    },
    updateSelectedItem(state) {
        const oldTarget = state.selectedItem;

        const targetList = (<Player>state.player).items;
        if (oldTarget !== null) {
            for (let i = 0; i < targetList.length; i++) {
                const target = targetList[i];
                if (oldTarget.id === target.id) {
                    return state.selectedItem = target;
                }
            }
            return state.selectedItem = null;
        }
    }
};

export const player = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
