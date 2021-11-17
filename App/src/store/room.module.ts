import { ActionTree } from "vuex";
import { Room } from "@/entities/Room";
import { Player } from "@/entities/Player";
import { Equipment } from "@/entities/Equipment";

interface StoreState {
    player?: Player;
	loading: boolean;
	room: Room | null;
	inventoryOpen: boolean;
    selectedTarget: Player | Equipment | null
}

const state =  {
    loading: false,
    room: null,
    inventoryOpen: false,
    selectedTarget: null
} as StoreState;

const getters = {
    isInventoryOpen: (state: StoreState): boolean => {
        return state.inventoryOpen;
    },
    selectedTarget: (state: StoreState): Player | Equipment | null => {
        return state.selectedTarget;
    }
};

const actions: ActionTree<StoreState, StoreState> = {
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

const mutations = {
    setLoading: (state: StoreState, newValue : boolean): boolean => {
        return state.loading = newValue;
    },
    openInventory: (state: StoreState): Player | Equipment | null | boolean => {
        return state.inventoryOpen = true,
        state.selectedTarget = null;
    },
    closeInventory: (state: StoreState): boolean => {
        return state.inventoryOpen = false;
    },
    setRoom: (state: StoreState, room: Room): Room => {
        return state.room = room;
    },
    setSelectedTarget: (state: StoreState, target: Player | Equipment | null): Player | Equipment | null | boolean => {
        return state.selectedTarget = target,
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
