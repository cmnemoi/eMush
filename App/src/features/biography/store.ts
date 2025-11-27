import { Module } from "vuex";

export type BiographyState = {
  biography: string | null;
};

export type LoadBioPayload = {
    characterName: string;
    language: string;
};

export function createCharacterBiographyModule(
    loadCharacterBiography: (characterName: string, language: string) => Promise<string>
): Module<BiographyState, any> {
    return {
        namespaced: true,
        state: () => ({
            biography: null
        }),
        getters: {
            biography: (state) => state.biography
        },
        mutations: {
            setBiography(state, bio: string) {
                state.biography = bio;
            }
        },
        actions: {
            async loadCharacterBio({ commit }, payload: LoadBioPayload) {
                const bio = await loadCharacterBiography(payload.characterName, payload.language);
                commit("setBiography", bio);
            }
        }
    };
}
