import DaedalusService from "@/services/daedalus.service";
import { ActionTree, GetterTree, MutationTree } from "vuex";
import { Daedalus } from "@/entities/Daedalus";
import { Alert } from "@/entities/Alerts";
import { Minimap } from "@/entities/Minimap";
import store from ".";

interface DaedalusModuleState {
    daedalus: Daedalus | null;
    alerts: Alert[];
    loadingAlerts: boolean;
    minimap: Minimap[];
    isMinimapAvailable: boolean;
    loadingMinimap: boolean;
}

const state: DaedalusModuleState =  {
    daedalus: null,
    alerts: [],
    loadingAlerts: false,
    minimap: [],
    isMinimapAvailable: false,
    loadingMinimap: false
};

const getters: GetterTree<DaedalusModuleState, DaedalusModuleState> = {
    daedalus: (state): Daedalus|null => {
        return state.daedalus;
    },
    alerts: (state): Alert[] => {
        return state.alerts;
    },
    loadingAlerts: (state): boolean => {
        return state.loadingAlerts;
    },
    minimap: (state): Minimap[] => {
        return state.minimap;
    },
    isMinimapAvailable: (state): boolean => {
        return state.isMinimapAvailable;
    },
    loadingMinimap: (state): boolean => {
        return state.loadingMinimap;
    }
};

const actions: ActionTree<DaedalusModuleState, DaedalusModuleState> = {
    async loadAlerts({ commit, state }, { daedalus }) {
        commit('updateDaedalus', daedalus);
        commit('setLoadingAlerts', true);
        try {
            const alerts = await DaedalusService.loadAlerts(state.daedalus);
            commit('updateAlerts', alerts);

            return true;
        } catch (e) {
            console.error(e);
            return false;
        }
    },
    async loadMinimap({ commit, state }) {
        const player = store.getters['player/player'];
        if (!player.isInARoom()) {
            return false;
        }

        commit('updateDaedalus', player.daedalus);
        commit('setLoadingMinimap', true);
        try {
            const minimap = await DaedalusService.loadMinimap(state.daedalus);
            commit('updateMinimap', minimap);

            return true;
        } catch (e) {
            console.error(e);
            return false;
        }
    },
    async clearDaedalus({ commit }) {
        commit("clearDaedalus");
    }
};

const mutations: MutationTree<DaedalusModuleState> = {
    updateDaedalus(state, daedalus: Daedalus): void {
        state.daedalus = daedalus;
    },
    updateAlerts(state, alerts: Alert[]): void {
        state.alerts = alerts;
        state.loadingAlerts = false;
    },
    setLoadingAlerts(state, newValue: boolean): void {
        state.loadingAlerts = newValue;
    },
    updateMinimap(state, minimap: Minimap[]): void
    {
        state.minimap = minimap;
        if (minimap.length > 0) {
            state.isMinimapAvailable = true;
        } else {
            state.isMinimapAvailable = false;
        }
        state.loadingMinimap = false;
    },
    setIsMinimapAvailable(state, newValue: boolean): void
    {
        state.isMinimapAvailable = newValue;
    },
    setLoadingMinimap(state, newValue: boolean): void {
        state.loadingMinimap = newValue;
    },
    clearDaedalus(state): void {
        state.alerts = [];
        state.minimap = [];
        state.isMinimapAvailable = false;
        state.daedalus = null;
    }
};

export const daedalus = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
