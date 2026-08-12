import { News } from "@/entities/News";
import type { CharacterBiographyDetails } from "@/services/biography.service";
import type { GameCounters } from "@/services/game_counters.service";
import type { Module } from "vuex";

export type HomePageState = {
    characterDetails: CharacterBiographyDetails | null;
    counters: GameCounters | null;
    news: News | null;
};

type HomePageDependencies = {
    loadCharacterDetails: (characterName: string, language: string) => Promise<CharacterBiographyDetails | null>;
    loadGameCounters: () => Promise<GameCounters | null>;
    loadPinnedNews: () => Promise<News[]>;
};

type LoadHomePagePayload = {
    characterName: string;
    language: string;
};

function prepareNews(news: News): News {
    news.hidden = false;
    news.englishTitle ||= news.frenchTitle;
    news.englishContent ||= news.frenchContent;
    news.spanishTitle ||= news.frenchTitle;
    news.spanishContent ||= news.frenchContent;

    return news;
}

export function createHomePageModule({
    loadCharacterDetails,
    loadGameCounters,
    loadPinnedNews
}: HomePageDependencies): Module<HomePageState, HomePageState> {
    return {
        namespaced: true,
        state: (): HomePageState => ({
            characterDetails: null,
            counters: null,
            news: null
        }),
        getters: {
            characterDetails: (state) => state.characterDetails,
            counters: (state) => state.counters,
            news: (state) => state.news
        },
        mutations: {
            setCharacterDetails(state, details: CharacterBiographyDetails | null) {
                state.characterDetails = details;
            },
            setCounters(state, counters: GameCounters | null) {
                state.counters = counters;
            },
            setNews(state, news: News | null) {
                state.news = news;
            }
        },
        actions: {
            async load({ dispatch }, payload: LoadHomePagePayload) {
                await Promise.all([
                    dispatch("loadCharacterDetails", payload),
                    dispatch("loadCounters"),
                    dispatch("loadNews")
                ]);
            },
            async loadCharacterDetails({ commit }, payload: LoadHomePagePayload) {
                try {
                    commit("setCharacterDetails", await loadCharacterDetails(payload.characterName, payload.language));
                } catch {
                    commit("setCharacterDetails", null);
                }
            },
            async loadCounters({ commit }) {
                try {
                    commit("setCounters", await loadGameCounters());
                } catch {
                    commit("setCounters", null);
                }
            },
            async loadNews({ commit }) {
                try {
                    const news = (await loadPinnedNews()).at(-1);
                    commit("setNews", news ? prepareNews(news) : null);
                } catch {
                    commit("setNews", null);
                }
            }
        }
    };
}
