import { beforeEach, describe, expect, it } from "vitest";
import { createStore, Store } from "vuex";
import { createAchievementsModule } from "./store";
import { Achievement, Statistic } from "./models";

describe("Achievements Store", () => {
    let store: Store<Record<string, any>>;
    const fetchUserStatistics: (userId: string, language: string) => Promise<Statistic[]> = async (userId: string, language: string) => {
        return Promise.resolve([{
            key: "test_statistic",
            name: "Test Statistic",
            description: "This is a test statistic",
            count: 1,
            formattedCount: "x1",
            isRare: false
        }]);
    };
    const fetchUserAchievements: (userId: string, language: string) => Promise<Achievement[]> = async (userId: string, language: string) => {
        return Promise.resolve([{
            key: "test_achievement_1",
            name: "Test Achievement 1",
            statisticKey: "test_statistic_1",
            statisticName: "Test Statistic 1",
            statisticDescription: "This is a test statistic 1",
            points: 1,
            formattedPoints: "+1",
            isRare: false
        }]);
    };

    beforeEach(() => {
        store = createStore({
            modules: {
                achievements: createAchievementsModule({ fetchUserStatistics, fetchUserAchievements })
            }
        });
    });

    it("should initialize with default state", () => {
        expect(store.state.achievements).toEqual({
            statistics: [],
            achievements: []
        });
    });

    it("should add fetched statistics to the store", async () => {
        await store.dispatch("achievements/fetchStatistics", { userId: "user1", language: "en" });

        expect(store.state.achievements.statistics).toEqual([{
            key: "test_statistic",
            name: "Test Statistic",
            description: "This is a test statistic",
            count: 1,
            formattedCount: "x1",
            isRare: false
        }]);
    });

    it("should return user statistics", async () => {
        await store.dispatch("achievements/fetchStatistics", { userId: "user1", language: "en" });

        const statistics = await store.getters["achievements/statistics"];
        expect(statistics).toEqual([{
            key: "test_statistic",
            name: "Test Statistic",
            description: "This is a test statistic",
            count: 1,
            formattedCount: "x1",
            isRare: false
        }]);
    });

    it("should return top N statistics by DESC count", async () => {
        const store = createStore({
            modules: {
                achievements: createAchievementsModule({ fetchUserStatistics, fetchUserAchievements })
            }
        });
        store.state.achievements.statistics = [
            {
                key: "test_statistic_1",
                name: "Test Statistic 1",
                description: "This is a test statistic 1",
                count: 1,
                formattedCount: "x1",
                isRare: false
            },
            {
                key: "test_statistic_2",
                name: "Test Statistic 2",
                description: "This is a test statistic 2",
                count: 2,
                formattedCount: "x2",
                isRare: false
            },
            {
                key: "test_statistic_3",
                name: "Test Statistic 3",
                description: "This is a test statistic 3",
                count: 3,
                formattedCount: "x3",
                isRare: false
            },
            {
                key: "test_statistic_4",
                name: "Test Statistic 4",
                description: "This is a test statistic 4",
                count: 4,
                formattedCount: "x4",
                isRare: false
            }
        ];

        const statistics = store.getters["achievements/topNStatistics"](3);
        expect(statistics).toEqual([
            {
                key: "test_statistic_4",
                name: "Test Statistic 4",
                description: "This is a test statistic 4",
                count: 4,
                formattedCount: "x4",
                isRare: false
            },
            {
                key: "test_statistic_3",
                name: "Test Statistic 3",
                description: "This is a test statistic 3",
                count: 3,
                formattedCount: "x3",
                isRare: false
            },
            {
                key: "test_statistic_2",
                name: "Test Statistic 2",
                description: "This is a test statistic 2",
                count: 2,
                formattedCount: "x2",
                isRare: false
            }
        ]);
    });

    it("should return top N statistics with rare stats first, then sorted by DESC count", async () => {
        const store = createStore({
            modules: {
                achievements: createAchievementsModule({ fetchUserStatistics, fetchUserAchievements })
            }
        });
        store.state.achievements.statistics = [
            {
                key: "rare_stat_1",
                name: "Rare Statistic 1",
                description: "Rare stat with low count",
                count: 5,
                formattedCount: "x5",
                isRare: true
            },
            {
                key: "rare_stat_2",
                name: "Rare Statistic 2",
                description: "Rare stat with high count",
                count: 10,
                formattedCount: "x10",
                isRare: true
            },
            {
                key: "common_stat_1",
                name: "Common Statistic 1",
                description: "Common stat with high count",
                count: 20,
                formattedCount: "x20",
                isRare: false
            },
            {
                key: "common_stat_2",
                name: "Common Statistic 2",
                description: "Common stat with low count",
                count: 15,
                formattedCount: "x15",
                isRare: false
            }
        ];

        const statistics = store.getters["achievements/topNStatistics"](10); // Get all
        expect(statistics).toEqual([
            {
                key: "rare_stat_2",
                name: "Rare Statistic 2",
                description: "Rare stat with high count",
                count: 10,
                formattedCount: "x10",
                isRare: true
            },
            {
                key: "rare_stat_1",
                name: "Rare Statistic 1",
                description: "Rare stat with low count",
                count: 5,
                formattedCount: "x5",
                isRare: true
            },
            {
                key: "common_stat_1",
                name: "Common Statistic 1",
                description: "Common stat with high count",
                count: 20,
                formattedCount: "x20",
                isRare: false
            },
            {
                key: "common_stat_2",
                name: "Common Statistic 2",
                description: "Common stat with low count",
                count: 15,
                formattedCount: "x15",
                isRare: false
            }
        ]);
    });

    it("should return number of statistics as sum of achievement points", async () => {
        const store = createStore({
            modules: {
                achievements: createAchievementsModule({ fetchUserStatistics, fetchUserAchievements })
            }
        });
        store.state.achievements.achievements = [
            {
                key: "test_achievement_1",
                name: "Test Achievement 1",
                statisticName: "Test Statistic 1",
                statisticDescription: "This is a test statistic 1",
                points: 1,
                formattedPoints: "+1",
                isRare: false
            },
            {
                key: "test_achievement_2",
                name: "Test Achievement 2",
                statisticName: "Test Statistic 2",
                statisticDescription: "This is a test statistic 2",
                points: 2,
                formattedPoints: "+2",
                isRare: false
            }
        ];

        const points = await store.getters["achievements/points"];
        expect(points).toBe(3);
    });

    it("should return player achievements", async () => {
        await store.dispatch("achievements/fetchAchievements", { userId: "user1", language: "fr" });

        const achievements: Achievement[] = await store.getters["achievements/achievements"];
        expect(achievements).toEqual([
            {
                key: "test_achievement_1",
                name: "Test Achievement 1",
                statisticKey: "test_statistic_1",
                statisticName: "Test Statistic 1",
                statisticDescription: "This is a test statistic 1",
                points: 1,
                formattedPoints: "+1",
                isRare: false
            }
        ]);
    });
});
