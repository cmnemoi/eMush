import AdminService from "@/services/admin.service";
import { ActionTree, GetterTree, MutationTree } from "vuex";

const state = {
    gameInMaintenance: false
};

const getters: GetterTree<any, any> = {
    gameInMaintenance: (state: any): boolean => {
        return state.gameInMaintenance;
    }
};

const actions: ActionTree<any, any> = {
    async loadGameMaintenanceStatus({ commit }): Promise<boolean> {
        try {
            const gameInMaintenance: boolean = await AdminService.getMaintenanceStatus().then((response: any) => {
                return response.data?.gameInMaintenance;
            });
            commit('setMaintenanceStatus', gameInMaintenance);
            return true;
        } catch (e) {
            return false;
        }
    }
};

const mutations: MutationTree<any> = {
    setMaintenanceStatus(state: any, status: boolean): void {
        state.gameInMaintenance = status;
    }
};

export const admin = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
