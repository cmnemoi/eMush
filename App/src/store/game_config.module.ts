import { ActionTree, GetterTree, MutationTree } from "vuex";
import { GameConfig } from "@/entities/Config/GameConfig";

interface GameConfigModuleState {
    gameConfig: GameConfig | null;
    loading: boolean;
}

const state: GameConfigModuleState =  {
    gameConfig: null,
    loading: false
};

const getters: GetterTree<GameConfigModuleState, GameConfigModuleState> = {
    gameConfig: (state): GameConfig|null => {
        return state.gameConfig;
    },
    isLoading: (state): boolean => {
        return state.loading;
    }
};

const actions: ActionTree<GameConfigModuleState, GameConfigModuleState> = {
    setLoading({ commit }, { loading }) {
        commit('setLoading', loading);
    }
};

const mutations: MutationTree<GameConfigModuleState> = {
    setLoading(state, newValue: boolean): void {
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
