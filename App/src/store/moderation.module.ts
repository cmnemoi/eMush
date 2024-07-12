import { ModerationSanction } from "@/entities/ModerationSanction";
import { ActionTree, GetterTree, MutationTree } from "vuex";
import ModerationService from "@/services/moderation.service";
import { Player } from "@/entities/Player";

const state = {
    userSanctions: [] as ModerationSanction[],
    reportablePlayers : [] as Player
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
    async loadUserSanctions({ commit }, userId: integer): Promise<boolean> {
        try {
            const userSanctions = await ModerationService.getUserActiveBansAndWarnings(userId)
                .then((response: ModerationSanction[]) => {
                    return response;
                });
            commit('setUserSanctions', userSanctions);
            return true;
        } catch (e) {
            console.error(e);
            return false;
        }
    },
    async getReportablePlayers({ commit }, message) {
        commit("player/setLoading", true, { root: true });
        const reportablePlayers = await ModerationService.loadReportablePlayers(message);
        commit("player/setLoading", false, { root: true });
        commit('setReportablePlayers', { reportablePlayers: reportablePlayers });
    },
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
