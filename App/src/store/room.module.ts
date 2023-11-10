import { ActionTree, GetterTree, MutationTree } from "vuex";
import { Room } from "@/entities/Room";
import { Player } from "@/entities/Player";
import { Equipment } from "@/entities/Equipment";
import { Item } from "@/entities/Item";
import { Hunter } from "@/entities/Hunter";
import { SpaceBattle } from "@/entities/SpaceBattle";
import { Action } from "@/entities/Action";

const state =  {
    loading: false,
    room: null,
    inventoryOpen: false,
    selectedTarget: null,
    spaceBattle: null
};

const getters: GetterTree<any, any> = {
    isInventoryOpen: (state) => {
        return state.inventoryOpen;
    },
    selectedTarget: (state) => {
        return state.selectedTarget;
    },
    patrolShipActions: (state) => {
        const room = (<Room> state.room);

        if (room.type !== 'patrol_ship') {
            return [];
        }

        const actions: {action: Action, target: Equipment}[] = [];
        const patrolShip = room.equipments[0];
        for (let i = 0; i < patrolShip.actions.length; i++) {
            const action: Action = patrolShip.actions[i];

            // if a hunter is selected and the action is the patrol ship shoot random, do not return the patrol ship shoot
            if (!(state.selectedTarget  !== null && action.key === 'shoot_hunter_patrol_ship')) {
                actions.push({ action: action, target: patrolShip });
            }
        }

        return actions;
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
    loadSpaceBattle({ commit }, { spaceBattle }) {
        commit('setSpaceBattle', spaceBattle);
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
    setSpaceBattle(state, spaceBattle: SpaceBattle | null) {
        state.spaceBattle = spaceBattle;
    },
    setSelectedTarget(state, target: Player | Equipment | Hunter | null) {
        state.selectedTarget = target;

        if (!(target instanceof Item)) {
            state.inventoryOpen = false;
        }
    },
    updateSelectedItemPile(state) {
        const oldTarget = state.selectedTarget;
        const room = (<Room> state.room);
        if (oldTarget instanceof Item && state.inventoryOpen) {
            const targetList = room.items;
            for (let i = 0; i < targetList.length; i++) {
                const target = targetList[i];
                if ((oldTarget.key === target.key && oldTarget.number > 0) ||
                    oldTarget.id === target.id
                ) {
                    return state.selectedTarget = target;
                }
            }
        } else if (oldTarget instanceof Player) {
            const targetList = room.players;
            for (let i = 0; i < targetList.length; i++) {
                const target = targetList[i];
                if (oldTarget.id === target.id) {
                    return state.selectedTarget = target;
                }
            }
        } else if (oldTarget instanceof Equipment) {
            const targetList = room.equipments;
            for (let i = 0; i < targetList.length; i++) {
                const target = targetList[i];
                if (oldTarget.id === target.id) {
                    return state.selectedTarget = target;
                }
            }
        } else if (oldTarget instanceof Hunter) {
            const targetList = (<SpaceBattle> state.spaceBattle).hunters;
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
