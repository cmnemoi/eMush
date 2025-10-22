import { Module } from "vuex";
import { RankingDaedalus } from "./models";

export type DaedalusRankingState = {
    loading: boolean,
    ranking: RankingDaedalus
};

export function createDaedalusRankingModule(
    loadDaedalusRanking: (language: string, page: number, itemsPerPage: number) => Promise<RankingDaedalus>
): Module<DaedalusRankingState, Record<string, any>> {
    return {
        namespaced: true,
        state: (): DaedalusRankingState => ({
            loading: false,
            ranking: { data: [], totalItems: 0 }
        }),
        mutations: {
            setLoading(state: DaedalusRankingState, loading: boolean) {
                state.loading = loading;
            },
            setRanking(state: DaedalusRankingState, ranking: RankingDaedalus) {
                state.ranking = ranking;
            }
        },
        getters: {
            loading: (state: DaedalusRankingState): boolean => state.loading,
            ranking: (state: DaedalusRankingState): RankingDaedalus => state.ranking
        },
        actions: {
            async loadRanking({ commit }, { language, page, itemsPerPage }: { language: string, page: number, itemsPerPage: number }) {
                commit('setLoading', true);
                try {
                    const ranking = await loadDaedalusRanking(language, page, itemsPerPage);
                    commit('setRanking', ranking);
                } catch (error) {
                    console.error(error);
                    throw error;
                } finally {
                    commit('setLoading', false);
                }
            }
        }
    };
}
