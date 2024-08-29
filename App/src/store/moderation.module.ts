import { ModerationSanction } from "@/entities/ModerationSanction";
import { ActionTree, GetterTree, MutationTree } from "vuex";
import ModerationService from "@/services/moderation.service";
import { Player } from "@/entities/Player";
import { SuccessReponse } from "@/services/api.service";
import store from ".";

const state = {
    userSanctions: [] as ModerationSanction[],
    reportablePlayers : [] as Player[]
};

const getters: GetterTree<any, any> = {
    userSanctions: (state: any): ModerationSanction[] => {
        return state.userSanctions;
    },
    getReportablePlayers: (state: any): Player[] => {
        return state.reportablePlayers;
    }
};

const actions: ActionTree<any, any> = {
    async loadReportablePlayers({ commit }) {
        await commit('player/setLoading', true, { root: true });
        const reportablePlayers = await ModerationService.loadReportablePlayers();
        await commit('player/setLoading', false, { root: true });
        await commit('setReportablePlayers', { reportablePlayers: reportablePlayers });
    },
    async loadUserSanctions({ commit, dispatch }, userId: integer): Promise<boolean> {
        try {
            const userSanctions = await ModerationService.getUserActiveBansAndWarnings(userId)
                .then((response: ModerationSanction[]) => {
                    return response;
                });
            commit('setUserSanctions', userSanctions);
            return true;
        } catch (error) {
            console.error(error);
            await dispatch('error/setError', error, { root: true });
            await dispatch('toast/openErrorToast', store.getters['error/getError'].response.details, { root: true });
            return false;
        }
    },
    async reportClosedPlayer({ dispatch }, { closedPlayerId, params }) {
        try {
            const response: SuccessReponse = await ModerationService.reportClosedPlayer(closedPlayerId, params);
            await dispatch('toast/openInfoToast', response.data.detail, { root: true });
        } catch (error) {
            console.error(error);
            await dispatch('error/setError', error, { root: true });
            await dispatch('toast/openErrorToast', store.getters['error/getError'].response.details, { root: true });
        }
    },
    async reportMessage({ dispatch }, { messageId, params }) {
        try {
            const response: SuccessReponse = await ModerationService.reportMessage(messageId, params);
            await dispatch('toast/openInfoToast', response.data.detail, { root: true });
        } catch (error) {
            console.error(error);
            await dispatch('error/setError', error, { root: true });
            await dispatch('toast/openErrorToast', store.getters['error/getError'].response.details, { root: true });
        }
    },
    async reportRoomLog({ dispatch }, { roomLogId, params }) {
        try {
            const response: SuccessReponse = await ModerationService.reportLog(roomLogId, params);
            await dispatch('toast/openInfoToast', response.data.detail, { root: true });
        } catch (error) {
            console.error(error);
            await dispatch('error/setError', error, { root: true });
            await dispatch('toast/openErrorToast', store.getters['error/getError'].response.details, { root: true });
        }
    },
    async reportCommanderMission({ dispatch }, { missionId, params }) {
        try {
            const response: SuccessReponse = await ModerationService.reportCommanderMission(missionId, params);
            await dispatch('toast/openInfoToast', response.data.detail, { root: true });
        } catch (error) {
            console.error(error);
            await dispatch('error/setError', error, { root: true });
            await dispatch('toast/openErrorToast', store.getters['error/getError'].response.details, { root: true });
        }
    }
};

const mutations: MutationTree<any> = {
    setUserSanctions(state: any, sanctions: ModerationSanction[]): void {
        state.userSanctions = sanctions;
    },
    setReportablePlayers(state: any, { reportablePlayers }): void {
        state.reportablePlayers = reportablePlayers;
    }
};

export const moderation = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};

