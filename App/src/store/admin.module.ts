import AdminService from "@/services/admin.service";
import { ActionTree, GetterTree, MutationTree } from "vuex";

const state = {
    gameInMaintenance: false,
    isLoading: false
};

const getters: GetterTree<any, any> = {
    gameInMaintenance: (state: any): boolean => {
        return state.gameInMaintenance;
    },
    isLoading: (state: any): boolean => {
        return state.isLoading;
    }
};

const actions: ActionTree<any, any> = {
    displayLoading({ commit }): void {
        commit('setLoading', true);
    },
    endLoading({ commit }): void {
        commit('setLoading', false);
    },
    async loadGameMaintenanceStatus({ commit }): Promise<boolean> {
        try {
            const gameInMaintenance: boolean = await AdminService.getMaintenanceStatus().then((response: any) => {
                return response.data?.gameInMaintenance;
            });
            commit('setMaintenanceStatus', gameInMaintenance);
            return true;
        } catch (e) {
            console.error(e);
            return false;
        }
    }
};

const mutations: MutationTree<any> = {
    setMaintenanceStatus(state: any, status: boolean): void {
        state.gameInMaintenance = status;
    },
    setLoading(state: any, newValue: boolean): void {
        state.isLoading = newValue;
    }
};

export const admin = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
