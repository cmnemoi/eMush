import { News } from "@/entities/News";
import type { CharacterBiographyDetails } from "@/services/biography.service";
import type { GameCounters } from "@/services/game_counters.service";
import { describe, expect, it } from "vitest";
import { createStore } from "vuex";
import { createHomePageModule } from "./store";

const characterDetails: CharacterBiographyDetails = {
    fullName: "Stephen Seagull",
    description: "Cuisinier le plus dangereux de la galaxie.",
    age: "47 ans",
    employment: "Cuisinier",
    abstract: "Un parcours mouvementé.",
    song: "To Whom The Rockets Tolls"
};

const counters: GameCounters = {
    daedalusesInGame: 2,
    mushKilled: 3,
    messagesSent: 5,
    expeditionsStarted: 7
};

function createHomePageStore({ fail = false } = {}) {
    const news = new News();
    news.frenchTitle = "Actualité";
    news.frenchContent = "Contenu";

    return createStore({
        modules: {
            homePage: createHomePageModule({
                loadCharacterDetails: () => fail ? Promise.reject(new Error("Unavailable")) : Promise.resolve(characterDetails),
                loadGameCounters: () => fail ? Promise.reject(new Error("Unavailable")) : Promise.resolve(counters),
                loadPinnedNews: () => fail ? Promise.reject(new Error("Unavailable")) : Promise.resolve([news])
            })
        }
    });
}

describe("Home page store", () => {
    it("loads home page data", async () => {
        const store = createHomePageStore();

        await store.dispatch("homePage/load", { characterName: "stephen", language: "fr" });

        expect(store.getters["homePage/characterDetails"]).toEqual(characterDetails);
        expect(store.getters["homePage/counters"]).toEqual(counters);
        expect(store.getters["homePage/news"]).toMatchObject({
            hidden: false,
            englishTitle: "Actualité",
            spanishTitle: "Actualité"
        });
    });

    it("hides unavailable home page data", async () => {
        const store = createHomePageStore({ fail: true });

        await store.dispatch("homePage/load", { characterName: "stephen", language: "fr" });

        expect(store.getters["homePage/characterDetails"]).toBeNull();
        expect(store.getters["homePage/counters"]).toBeNull();
        expect(store.getters["homePage/news"]).toBeNull();
    });
});
