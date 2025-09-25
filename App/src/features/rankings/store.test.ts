import { describe, expect, it } from "vitest";
import { createStore } from "vuex";
import { createDaedalusRankingModule } from "./store";

describe("Daedalus ranking store", () => {
    it("initializes with default state", () => {
        const store = createStore({
            modules: {
                daedalusRanking: createDaedalusRankingModule(() => Promise.resolve([]))
            }
        });

        expect(store.state.daedalusRanking.loading).toBe(false);
        expect(store.state.daedalusRanking.ranking).toEqual([]);
    });

    it("should load ranking", async () => {
        const store = createStore({
            modules: {
                daedalusRanking: createDaedalusRankingModule(() => Promise.resolve([
                    {
                        id: 2,
                        endCause: "sol_return",
                        daysSurvived: 2,
                        cyclesSurvived: 17
                    },
                    {
                        id: 1,
                        endCause: "sol_return",
                        daysSurvived: 1,
                        cyclesSurvived: 10
                    }
                ]))
            }
        });
        await store.dispatch("daedalusRanking/loadRanking", { language: "en", page: 1, itemsPerPage: 10 });
        expect(store.state.daedalusRanking.loading).toBe(false);
        expect(store.state.daedalusRanking.ranking).toEqual([
            {
                id: 2,
                endCause: "sol_return",
                daysSurvived: 2,
                cyclesSurvived: 17
            },
            {
                id: 1,
                endCause: "sol_return",
                daysSurvived: 1,
                cyclesSurvived: 10
            }
        ]);
    });
});
