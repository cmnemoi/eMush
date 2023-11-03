import DaedalusService from "@/services/daedalus.service";
import { ActionTree, GetterTree, MutationTree } from "vuex";
import { Daedalus } from "@/entities/Daedalus";
import { Alert } from "@/entities/Alerts";
import { Minimap } from "@/entities/Minimap";
import disable = Phaser.Display.Canvas.Smoothing.disable;

const state =  {
    daedalus: null,
    alerts: [],
    loadingAlerts: false,
    minimap: [],
    isMinimapAvailable: false,
    loadingMinimap: false
};

const getters: GetterTree<any, any> = {
    daedalus: (state: any): Daedalus|null => {
        return state.daedalus;
    },
    alerts: (state: any): Alert[] => {
        return state.alerts;
    },
    loadingAlerts: (state: any): boolean => {
        return state.loadingAlerts;
    },
    minimap: (state: any): Minimap[] => {
        return state.minimap;
    },
    isMinimapAvailable: (state: any): boolean => {
        return state.isMinimapAvailable;
    },
    loadingMinimap: (state: any): boolean => {
        return state.loadingMinimap;
    }
};

const actions: ActionTree<any, any> = {
    async loadAlerts({ commit, state }, { player }) {
        commit('updateDaedalus', player.daedalus);
        commit('setLoadingAlerts', true);
        try {
            const alerts = await DaedalusService.loadAlerts(state.daedalus);
            commit('updateAlerts', alerts);

            return true;
        } catch (e) {
            return false;
        }
    },
    async loadMinimap({ commit, state }, { player }) {
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
            return false;
        }
    },
    async clearDaedalus({ commit }) {
        commit("clearDaedalus");
    }
};

const mutations: MutationTree<any> = {
    updateDaedalus(state: any, daedalus: Daedalus): void {
        state.daedalus = daedalus;
    },
    updateAlerts(state: any, alerts: Alert[]): void {
        state.alerts = alerts;
        state.loadingAlerts = false;
    },
    setLoadingAlerts(state: any, newValue: boolean): void {
        state.loadingAlerts = newValue;
    },
    updateMinimap(state: any, minimap: Minimap[]): void
    {
        state.minimap = minimap;
        if (minimap.length > 0) {
            state.isMinimapAvailable = true;
        }
        state.loadingMinimap = false;
    },
    setIsMinimapAvailable(state: any, newValue: boolean): void
    {
        state.isMinimapAvailable = newValue;
    },
    setLoadingMinimap(state: any, newValue: boolean): void {
        state.loadingMinimap = newValue;
    },
    clearDaedalus(state: any): void {
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
