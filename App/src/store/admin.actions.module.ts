import AdminActionsService from "@/services/admin.actions.service";
import { ActionTree, GetterTree, MutationTree } from "vuex";
import store from ".";
import { SuccessReponse } from "@/services/api.service";

const state = {
    isLoading: false
};

const getters: GetterTree<any, any> = {
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
    async createProjectsForOnGoingDaedaluses({ dispatch }): Promise<void> {
        try {
            const response: SuccessReponse = await AdminActionsService.createProjectsForOnGoingDaedaluses();
            await dispatch('toast/openSuccessToast', response.data.detail, { root: true });
        } catch (error) {
            await dispatch('error/setError', error, { root: true });
            await dispatch('toast/openErrorToast', store.getters['error/getError'].response.details, { root: true });
        }
    }
};

const mutations: MutationTree<any> = {
    setLoading(state: any, newValue: boolean): void {
        state.isLoading = newValue;
    }
};

export const adminActions = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
