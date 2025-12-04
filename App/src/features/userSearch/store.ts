import { Module } from "vuex";
import { UserSearchResult } from "./models";

export type UserSearchState = {
    results: UserSearchResult[];
    query: string;
};

export function createUserSearchModule(
    searchUsers: (username: string, limit: number) => Promise<UserSearchResult[]>
): Module<UserSearchState, Record<string, any>> {
    return {
        namespaced: true,
        state: (): UserSearchState => ({
            results: [],
            query: ""
        }),
        mutations: {
            setResults(state: UserSearchState, results: UserSearchResult[]) {
                state.results = results;
            },
            setQuery(state: UserSearchState, query: string) {
                state.query = query;
            },
            clearResults(state: UserSearchState) {
                state.results = [];
                state.query = "";
            }
        },
        getters: {
            results: (state: UserSearchState): UserSearchResult[] => state.results,
            query: (state: UserSearchState): string => state.query
        },
        actions: {
            async search({ commit }, { username, limit }: { username: string; limit: number }) {
                if (!username.trim()) {
                    commit("clearResults");
                    return;
                }

                commit("setQuery", username);
                try {
                    const results = await searchUsers(username, limit);
                    commit("setResults", results);
                } catch (error) {
                    console.error(error);
                    commit("setResults", []);
                    throw error;
                }
            },
            clear({ commit }) {
                commit("clearResults");
            }
        }
    };
}
