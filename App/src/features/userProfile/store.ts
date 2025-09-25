import { Module } from "vuex";
import { ShipHistory, User } from "./models";

export type UserProfileState = {
    loading: boolean;
    user: User,
    shipsHistory: ShipHistory[],
};

export function createUserProfileModule(
    loadShipsHistory: (userId: number, page: number, itemsPerPage: number, language: string) => Promise<ShipHistory[]>,
    loadUserById: (userId: string) => Promise<User>
): Module<UserProfileState, Record<string, any>> {
    return {
        namespaced: true,
        state: (): UserProfileState => ({
            loading: false,
            shipsHistory: [],
            user: {
                userId: '',
                username: ''
            }
        }),
        mutations: {
            setLoading(state: UserProfileState, loading: boolean) {
                state.loading = loading;
            },
            setShipsHistory(state: UserProfileState, shipsHistory: ShipHistory[]) {
                state.shipsHistory = shipsHistory;
            },
            setUser(state: UserProfileState, user: User) {
                state.user = user;
            }
        },
        getters: {
            loading: (state: UserProfileState): boolean => state.loading,
            shipsHistory: (state: UserProfileState): ShipHistory[] => state.shipsHistory,
            user: (state: UserProfileState): User => state.user
        },
        actions: {
            async loadShipsHistory({ commit }, { userId, page, itemsPerPage, language }: { userId: number; page: number; itemsPerPage: number; language: string }) {
                commit('setLoading', true);
                try {
                    const shipsHistory = await loadShipsHistory(userId, page, itemsPerPage, language);
                    commit('setShipsHistory', shipsHistory);
                } catch (error) {
                    console.error(error);
                    throw error;
                } finally {
                    commit('setLoading', false);
                }
            },
            async loadUser({ commit }, userId: string) {
                try {
                    const user = await loadUserById(userId);
                    commit('setUser', user);
                } catch (error) {
                    console.error(error);
                    throw error;
                }
            }
        }
    };
}
