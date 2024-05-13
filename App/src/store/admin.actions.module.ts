import AdminActionsService from "@/services/admin.actions.service";
import { ActionTree, Dispatch, GetterTree, MutationTree } from "vuex";
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
        await dispatch('admin/displayLoading', null, { root: true });
        try {
            const response: SuccessReponse = await AdminActionsService.createProjectsForOnGoingDaedaluses();
            await dispatch('toast/openSuccessToast', response.data.detail, { root: true });
        } catch (error) {
            await displayToastError(error, dispatch);
        }
        await dispatch('admin/endLoading', null, { root: true });
    },
    async createEquipmentForOnGoingDaedaluses({ dispatch }, { equipmentName, quantity, place }): Promise<void> {
        await dispatch('admin/displayLoading', null, { root: true });
        try {
            const response: SuccessReponse = await AdminActionsService.createEquipmentForOnGoingDaedaluses(equipmentName, quantity, place);
            await dispatch('toast/openSuccessToast', response.data.detail, { root: true });
        } catch (error) {
            await displayToastError(error, dispatch);
        }
        await dispatch('admin/endLoading', null, { root: true });
    },
    async createPlayersAllInitStatusesForOnGoingDaedaluses({ dispatch }): Promise<void> {
        await dispatch('admin/displayLoading', null, { root: true });
        try {
            const response: SuccessReponse = await AdminActionsService.createPlayersAllInitStatusesForOnGoingDaedaluses();
            await dispatch('toast/openSuccessToast', response.data.detail, { root: true });
        } catch (error) {
            await displayToastError(error, dispatch);
        }
        await dispatch('admin/endLoading', null, { root: true });
    },
    async deleteAllStatusesByName({ dispatch }, { statusName }): Promise<void> {
        await dispatch('admin/displayLoading', null, { root: true });
        try {
            const response: SuccessReponse = await AdminActionsService.deleteAllStatusesByName(statusName);
            await dispatch('toast/openSuccessToast', response.data.detail, { root: true });
        } catch (error) {
            await displayToastError(error, dispatch);
        }
        await dispatch('admin/endLoading', null, { root: true });
    },
    async makeNonFinishedNeronProjectsAvailableForOnGoingDaedaluses({ dispatch }): Promise<void> {
        await dispatch('admin/displayLoading', null, { root: true });
        try {
            const response: SuccessReponse = await AdminActionsService.makeNonFinishedNeronProjectsAvailableForOnGoingDaedaluses();
            await dispatch('toast/openSuccessToast', response.data.detail, { root: true });
        } catch (error) {
            await displayToastError(error, dispatch);
        }
        await dispatch('admin/endLoading', null, { root: true });
    },
    async proposeNewNeronProjectsForOnGoingDaedaluses({ dispatch }): Promise<void> {
        await dispatch('admin/displayLoading', null, { root: true });
        try {
            const response: SuccessReponse = await AdminActionsService.proposeNewNeronProjectsForOnGoingDaedaluses();
            await dispatch('toast/openSuccessToast', response.data.detail, { root: true });
        } catch (error) {
            await displayToastError(error, dispatch);
        }
        await dispatch('admin/endLoading', null, { root: true });
    },
    async unproposeAllNeronProjectsForOnGoingDaedaluses({ dispatch }): Promise<void> {
        await dispatch('admin/displayLoading', null, { root: true });
        try {
            const response: SuccessReponse = await AdminActionsService.unproposeAllNeronProjectsForOnGoingDaedaluses();
            await dispatch('toast/openSuccessToast', response.data.detail, { root: true });
        } catch (error) {
            await displayToastError(error, dispatch);
        }
        await dispatch('admin/endLoading', null, { root: true });
    }
};

const mutations: MutationTree<any> = {
    setLoading(state: any, newValue: boolean): void {
        state.isLoading = newValue;
    }
};

async function displayToastError(error: unknown, dispatch: Dispatch): Promise<void> {
    console.error(error);
    await dispatch('error/setError', error, { root: true });
    await dispatch('toast/openErrorToast', store.getters['error/getError'].response.details, { root: true });
}

export const adminActions = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};


