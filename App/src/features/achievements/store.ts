import { Module } from "vuex";
import { Achievement, Statistic } from "./models";
import { AchievementsGateway } from "./gateway";

export type AchievementsState = {
    statistics: Statistic[];
    achievements: Achievement[];
};

export function createAchievementsModule(gateway: AchievementsGateway): Module<AchievementsState, Record<string, any>> {
    return {
        namespaced: true,
        state: (): AchievementsState => ({
            statistics: [],
            achievements: []
        }),
        mutations: {
            setStatistics(state, statistics: Statistic[]) {
                state.statistics = statistics;
            },
            setAchievements(state, achievements: Achievement[]) {
                state.achievements = achievements;
            }
        },
        getters: {
            statistics(state): Statistic[] {
                return state.statistics;
            },
            topNStatistics: (state) => (n: integer) => {
                return [...state.statistics].sort((a, b) => b.count - a.count).slice(0, n);
            },
            achievements(state): Achievement[] {
                return state.achievements;
            },
            points(state): number {
                return state.achievements.reduce((acc, achievement) => acc + achievement.points, 0);
            }
        },
        actions: {
            async fetchStatistics({ commit }, { userId, language }): Promise<void> {
                try {
                    const statistics = await gateway.fetchUserStatistics(userId, language);
                    commit("setStatistics", statistics);
                } catch (error) {
                    console.error(error);
                    throw error;
                }
            },
            async fetchAchievements({ commit }, { userId, language }): Promise<void> {
                try {
                    const achievements = await gateway.fetchUserAchievements(userId, language);
                    commit("setAchievements", achievements);
                } catch (error) {
                    console.error(error);
                    throw error;
                }
            }
        }
    };
}
