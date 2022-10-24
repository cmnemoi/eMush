import { ActionTree, GetterTree, MutationTree } from "vuex";
import { GameConfig } from "@/entities/Config/GameConfig";

const state =  {
    gameConfig: null,
    loading: false
};

const getters: GetterTree<any, any> = {
    gameConfig: (state: any): GameConfig|null => {
        return state.gameConfig;
    },
    isLoading: (state: any): boolean => {
        return state.loading;
    }
};

const actions: ActionTree<any, any> = {
    setLoading({ commit }, { loading }) {
        commit('setLoading', loading);
    }
};

const mutations: MutationTree<any> = {
    setLoading(state: any, newValue: boolean): void {
        state.loading = newValue;
    }
};

export const gameConfig = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
