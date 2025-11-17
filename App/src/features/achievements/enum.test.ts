import { describe, expect, it } from "vitest";
import { StatisticRecords } from "./enum";

describe("StatisticRecords", () => {
    it("should have an icon property for every record", () => {
        const recordKeys = Object.keys(StatisticRecords);

        for (const statisticKey of recordKeys) {
            expect(StatisticRecords[statisticKey]).toBeDefined();
            expect(StatisticRecords[statisticKey].icon).toBeDefined();
        }
    });

    it("should return a valid non-empty string for all icons", () => {
        const recordKeys = Object.keys(StatisticRecords);

        for (const statisticKey of recordKeys) {
            const icon = StatisticRecords[statisticKey]?.icon;

            expect(icon).not.toBe("");
        }
    });
});
