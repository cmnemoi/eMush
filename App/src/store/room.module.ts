import { ActionTree, GetterTree, MutationTree } from "vuex";
import { Room } from "@/entities/Room";
import { Player } from "@/entities/Player";
import { Equipment } from "@/entities/Equipment";

interface StoreStateRoom {
    player: Player | null;
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
} as StoreStateRoom;

const getters: GetterTree<StoreStateRoom, StoreStateRoom> = {
    isInventoryOpen: (state: StoreStateRoom): boolean => {
        return state.inventoryOpen;
    },
    selectedTarget: (state: StoreStateRoom): Player | Equipment | null => {
        return state.selectedTarget;
    }
};

const actions: ActionTree<StoreStateRoom, StoreStateRoom> = {
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

const mutations : MutationTree<StoreStateRoom>= {
    setLoading: (state: StoreStateRoom, newValue : boolean): void => {
        state.loading = newValue;
    },
    openInventory: (state: StoreStateRoom): void => {
        state.inventoryOpen = true;
        state.selectedTarget = null;
    },
    closeInventory: (state: StoreStateRoom): void => {
        state.inventoryOpen = false;
    },
    setRoom: (state: StoreStateRoom, room: Room): void => {
        state.room = room;
    },
    setSelectedTarget: (state: StoreStateRoom, target: Player | Equipment | null): void => {
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
