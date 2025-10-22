import { describe, expect, it } from "vitest";
import { createStore } from "vuex";
import { createUserProfileModule } from "./store";

describe("User profile store", () => {
    it("initializes with default state", () => {
        const store = createStore({
            modules: {
                userProfile: createUserProfileModule(
                    () => Promise.resolve({ data: [], totalItems: 0 }),
                    (userId: string) => Promise.resolve({
                        id: 0,
                        userId: userId,
                        username: `user_${userId}`
                    })
                )
            }
        });

        expect(store.state.userProfile.loading).toBe(false);
        expect(store.state.userProfile.shipsHistory).toEqual({ data: [], totalItems: 0 });
    });

    it("should load ships history", async () => {
        const store = createStore({
            modules: {
                userProfile: createUserProfileModule(
                    (_userId: number, _page: number, _itemsPerPage: number, _language: string) => {
                        return Promise.resolve({
                            data: [{
                                characterName: "andie",
                                daysSurvived: 1,
                                nbExplorations: 0,
                                nbNeronProjects: 0,
                                nbResearchProjects: 0,
                                nbScannedPlanets: 0,
                                titles: [],
                                triumph: "29 :triumph:",
                                endCause: "super_nova",
                                daedalusId: 1
                            }],
                            totalItems: 1
                        });
                    },
                    (userId: string) => Promise.resolve({
                        id: 0,
                        userId: userId,
                        username: `user_${userId}`
                    })
                )
            }
        });

        await store.dispatch("userProfile/loadShipsHistory", {
            userId: 1,
            page: 1,
            itemsPerPage: 10,
            language: "fr"
        });

        expect(store.state.userProfile.loading).toBe(false);
        expect(store.state.userProfile.shipsHistory).toEqual({
            data: [{
                characterName: "andie",
                daysSurvived: 1,
                nbExplorations: 0,
                nbNeronProjects: 0,
                nbResearchProjects: 0,
                nbScannedPlanets: 0,
                titles: [],
                triumph: "29 :triumph:",
                endCause: "super_nova",
                daedalusId: 1
            }],
            totalItems: 1
        });
    });

    it("should load user", async () => {
        const store = createStore({
            modules: {
                userProfile: createUserProfileModule(
                    () => Promise.resolve({ data: [], totalItems: 0 }),
                    (userId: string) => Promise.resolve({
                        id: 0,
                        userId: userId,
                        username: `user_${userId}`
                    })
                )
            }
        });

        await store.dispatch("userProfile/loadUser", "1");

        expect(store.state.userProfile.user.username).toBe("user_1");
    });
});
