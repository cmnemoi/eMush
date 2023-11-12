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
    getSpaceWeaponAndActions: (state) => {
        const room = (<Room> state.room);

        if (!(state.selectedTarget instanceof Hunter)) {
            return [];
        }

        const actions: {action: Action, target: Equipment}[] = [];
        const weapon = room.equipments.filter((equipment:Equipment) => (
            equipment.key?.substring(0, 11) === 'patrol_ship'
            || equipment.key === 'turret_command')
        )[0];

        for (let i = 0; i < weapon.actions.length; i++) {
            const action: Action = weapon.actions[i];

            // if a hunter is selected and the action is the patrol ship shoot random, do not return the patrol ship shoot
            if (action.key !== 'shoot_hunter_patrol_ship' && action.key !== 'shoot_hunter') {
                actions.push({ action: action, target: weapon });
            }
        }

        return actions;
    },
    getSpaceShip: (state) => {
        const room = (<Room> state.room);

        if (room.type !== 'patrol_ship') {
            return null;
        }

        return room.equipments.filter((equipment:Equipment) => (
            equipment.key?.substring(0, 11) === 'patrol_ship'
            || equipment.key?.substring(0, 8) === 'pasiphae')
        )[0];
    },
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
        if (state.room?.key !== room?.key) {
            state.selectedTarget = null;
        }
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

            // if the hunter has been destroyed the spaceWeapon is selected
            return state.selectedTarget = room.equipments.filter((equipment:Equipment) => (
                equipment.key?.substring(0, 11) === 'patrol_ship'
                || equipment.key === 'turret_command')
            )[0];
        }

        if (room?.type === 'patrol_ship') {
            return state.selectedTarget = room.equipments.filter((equipment:Equipment) => (
                equipment.key?.substring(0, 11) === 'patrol_ship'
                || equipment.key?.substring(0, 8) === 'pasiphae')
            )[0];
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
