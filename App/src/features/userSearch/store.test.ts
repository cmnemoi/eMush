import { describe, expect, it } from "vitest";
import { createStore } from "vuex";
import { createUserSearchModule } from "./store";
import { UserSearchResult } from "./models";

describe("User search store", () => {
    it("initializes with default state", () => {
        const store = createStore({
            modules: {
                userSearch: createUserSearchModule(
                    () => Promise.resolve([])
                )
            }
        });

        expect(store.state.userSearch.results).toEqual([]);
        expect(store.state.userSearch.query).toBe("");
    });

    it("should search for users", async () => {
        const mockResults: UserSearchResult[] = [
            { userId: "1", username: "alice", similarityScore: 0.9 },
            { userId: "2", username: "alex", similarityScore: 0.8 }
        ];

        const store = createStore({
            modules: {
                userSearch: createUserSearchModule(
                    (username: string, _limit: number) => {
                        if (username === "al") {
                            return Promise.resolve(mockResults);
                        }
                        return Promise.resolve([]);
                    }
                )
            }
        });

        await store.dispatch("userSearch/search", { username: "al", limit: 3 });

        expect(store.state.userSearch.query).toBe("al");
        expect(store.state.userSearch.results).toEqual(mockResults);
        expect(store.getters["userSearch/results"]).toEqual(mockResults);
    });

    it("should clear results when searching with empty query", async () => {
        const store = createStore({
            modules: {
                userSearch: createUserSearchModule(
                    () => Promise.resolve([{ userId: "1", username: "test", similarityScore: 1 }])
                )
            }
        });

        await store.dispatch("userSearch/search", { username: "test", limit: 3 });
        expect(store.state.userSearch.results).toHaveLength(1);

        await store.dispatch("userSearch/search", { username: "   ", limit: 3 });

        expect(store.state.userSearch.results).toEqual([]);
        expect(store.state.userSearch.query).toBe("");
    });

    it("should clear results with clear action", async () => {
        const store = createStore({
            modules: {
                userSearch: createUserSearchModule(
                    () => Promise.resolve([{ userId: "1", username: "test", similarityScore: 1 }])
                )
            }
        });

        await store.dispatch("userSearch/search", { username: "test", limit: 3 });
        expect(store.state.userSearch.results).toHaveLength(1);

        store.dispatch("userSearch/clear");

        expect(store.state.userSearch.results).toEqual([]);
        expect(store.state.userSearch.query).toBe("");
    });

    it("should handle search errors gracefully", async () => {
        const store = createStore({
            modules: {
                userSearch: createUserSearchModule(
                    () => Promise.reject(new Error("Network error"))
                )
            }
        });

        await expect(
            store.dispatch("userSearch/search", { username: "test", limit: 3 })
        ).rejects.toThrow("Network error");

        expect(store.state.userSearch.results).toEqual([]);
    });

    it("should return results via getter", async () => {
        const mockResults: UserSearchResult[] = [
            { userId: "1", username: "bob", similarityScore: 0.95 }
        ];

        const store = createStore({
            modules: {
                userSearch: createUserSearchModule(
                    () => Promise.resolve(mockResults)
                )
            }
        });

        await store.dispatch("userSearch/search", { username: "bob", limit: 3 });

        expect(store.getters["userSearch/results"]).toEqual(mockResults);
        expect(store.getters["userSearch/query"]).toBe("bob");
    });
});
