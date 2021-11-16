import { ActionTree, GetterTree, MutationTree } from "vuex";
import { Room } from "@/entities/Room";
import { Player } from "@/entities/Player";
import { Equipment } from "@/entities/Equipment";

class state {
    loading = false;
    room: Room | null = null;
    inventoryOpen = false;
    selectedTarget: Player | Equipment | null = null;
    player: Player | null = null;
};

const getters = <GetterTree<state, state>>  {
    isInventoryOpen: (state) => {
        return state.inventoryOpen;
    },
    selectedTarget: (state) => {
        return state.selectedTarget;
    }
};

const actions = <ActionTree<state, state>> {
    openInventory({ commit } ) {
        commit('openInventory');
    },
    closeInventory({ commit } ) {
        commit('closeInventory');
    },
    loadRoom({ commit }, { room }) {
        commit('setRoom', room);
    },
    async reloadPlayer({ state, dispatch }) {
        return dispatch("loadPlayer", { playerId: state.player?.id });
    },
    setLoading({ commit }, { loading }) {
        commit('setLoading', loading);
    },
    selectTarget({ commit }, { target }) {
        commit('setSelectedTarget', target);
    }
};

const mutations = <MutationTree<state>> {
    setLoading(state, newValue) {
        state.loading = newValue;
    },
    openInventory(state) {
        state.inventoryOpen = true;
        state.selectedTarget = null;
    },
    closeInventory(state) {
        state.inventoryOpen = false;
    },
    setRoom(state, room: Room) {
        state.room = room;
    },
    setSelectedTarget(state, target: Player | Equipment | null) {
        state.selectedTarget = target;
        state.inventoryOpen = false;
    }
};

export const room = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
