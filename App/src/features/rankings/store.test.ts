import { describe, expect, it } from "vitest";
import { createStore } from "vuex";
import { createDaedalusRankingModule } from "./store";

describe("Daedalus ranking store", () => {
    it("initializes with default state", () => {
        const store = createStore({
            modules: {
                daedalusRanking: createDaedalusRankingModule(() => Promise.resolve({ data: [], totalItems: 0 }))
            }
        });

        expect(store.state.daedalusRanking.loading).toBe(false);
        expect(store.state.daedalusRanking.ranking).toEqual({ data: [], totalItems: 0 });
    });

    it("should load ranking", async () => {
        const store = createStore({
            modules: {
                daedalusRanking: createDaedalusRankingModule(() => Promise.resolve({
                    data: [
                        {
                            id: 2,
                            endCause: "sol_return",
                            daysSurvived: 2,
                            cyclesSurvived: 17,
                            humanTriumphSum: "2 :triumph:",
                            mushTriumphSum: "2 :triumph_mush:",
                            highestHumanTriumph: "2 :triumph:",
                            highestMushTriumph: "2 :triumph_mush:"
                        },
                        {
                            id: 1,
                            endCause: "sol_return",
                            daysSurvived: 1,
                            cyclesSurvived: 10,
                            humanTriumphSum: "1 :triumph:",
                            mushTriumphSum: "1 :triumph_mush:",
                            highestHumanTriumph: "1 :triumph:",
                            highestMushTriumph: "1 :triumph_mush:"
                        }
                    ],
                    totalItems: 2 }))
            }
        });
        await store.dispatch("daedalusRanking/loadRanking", { language: "en", page: 1, itemsPerPage: 10 });
        expect(store.state.daedalusRanking.loading).toBe(false);
        expect(store.state.daedalusRanking.ranking).toEqual(
            {
                data: [
                    {
                        id: 2,
                        endCause: "sol_return",
                        daysSurvived: 2,
                        cyclesSurvived: 17,
                        humanTriumphSum: "2 :triumph:",
                        mushTriumphSum: "2 :triumph_mush:",
                        highestHumanTriumph: "2 :triumph:",
                        highestMushTriumph: "2 :triumph_mush:"
                    },
                    {
                        id: 1,
                        endCause: "sol_return",
                        daysSurvived: 1,
                        cyclesSurvived: 10,
                        humanTriumphSum: "1 :triumph:",
                        mushTriumphSum: "1 :triumph_mush:",
                        highestHumanTriumph: "1 :triumph:",
                        highestMushTriumph: "1 :triumph_mush:"
                    }
                ],
                totalItems: 2
            }
        );
    });
});
