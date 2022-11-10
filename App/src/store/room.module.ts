import { ActionTree, GetterTree, MutationTree } from "vuex";
import { Room } from "@/entities/Room";
import { Player } from "@/entities/Player";
import { Equipment } from "@/entities/Equipment";
import { Item } from "@/entities/Item";

const state =  {
    loading: false,
    room: null,
    inventoryOpen: false,
    selectedTarget: null
};

const getters: GetterTree<any, any> = {
    isInventoryOpen: (state) => {
        return state.inventoryOpen;
    },
    selectedTarget: (state) => {
        return state.selectedTarget;
    }
};

const actions: ActionTree<any, any> = {
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
        return dispatch("loadPlayer", { playerId: state.player.id });
    },
    setLoading({ commit }, { loading }) {
        commit('setLoading', loading);
    },
    selectTarget({ commit }, { target }) {
        commit('setSelectedTarget', target);
    },
    updateSelectedItemPile({ commit }) {
        commit('updateSelectedItemPile');
    }
};

const mutations : MutationTree<any> = {
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
    setRoom(state, room: Room | null) {
        state.room = room;
    },
    setSelectedTarget(state, target: Player | Equipment | null) {
        state.selectedTarget = target;

        if (!(target instanceof Item)) {
            state.inventoryOpen = false;
        }
    },
    updateSelectedItemPile(state) {
        const oldTarget = state.selectedTarget;
        if (oldTarget instanceof Item && state.inventoryOpen) {
            const targetList = (<Room> state.room).items;
            for (let i = 0; i < targetList.length; i++) {
                const target = targetList[i];
                if ((oldTarget.key === target.key && oldTarget.number > 0) ||
                    oldTarget.id === target.id
                ) {
                    return state.selectedTarget = target;
                }
            }
        } else if (oldTarget instanceof Player) {
            const targetList = (<Room> state.room).players;
            for (let i = 0; i < targetList.length; i++) {
                const target = targetList[i];
                if (oldTarget.id === target.id) {
                    return state.selectedTarget = target;
                }
            }
        } else if (oldTarget instanceof Equipment) {
            const targetList = (<Room> state.room).equipments;
            for (let i = 0; i < targetList.length; i++) {
                const target = targetList[i];
                if (oldTarget.id === target.id) {
                    return state.selectedTarget = target;
                }
            }
        }
        state.selectedTarget = null;
    }
};

export const room = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
