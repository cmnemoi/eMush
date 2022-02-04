import DaedalusService from "@/services/daedalus.service";
import { ActionTree, GetterTree, MutationTree } from "vuex";
import { Daedalus } from "@/entities/Daedalus";
import { Alert } from "@/entities/Alerts";


const state =  {
    daedalus: null,
    alerts: [],
    loadingAlerts: false
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
    }
};

export const daedalus = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
