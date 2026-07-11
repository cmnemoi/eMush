import AdminService from "@/services/admin.service";
import { ActionTree, GetterTree, MutationTree } from "vuex";
import { AxiosResponse } from "axios";

interface AdminState {
    gameInMaintenance: boolean;
    isLoading: boolean;
}

const state: AdminState = {
    gameInMaintenance: false,
    isLoading: false
};

const getters: GetterTree<AdminState, AdminState> = {
    gameInMaintenance: (state): boolean => {
        return state.gameInMaintenance;
    },
    isLoading: (state): boolean => {
        return state.isLoading;
    }
};

const actions: ActionTree<AdminState, AdminState> = {
    displayLoading({ commit }): void {
        commit('setLoading', true);
    },
    endLoading({ commit }): void {
        commit('setLoading', false);
    },
    async loadGameMaintenanceStatus({ commit }): Promise<boolean> {
        try {
            const gameInMaintenance: boolean = await AdminService.getMaintenanceStatus().then((response: AxiosResponse<{ gameInMaintenance: boolean }>) => {
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

const mutations: MutationTree<AdminState> = {
    setMaintenanceStatus(state, status: boolean): void {
        state.gameInMaintenance = status;
    },
    setLoading(state, newValue: boolean): void {
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
